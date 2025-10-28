<?php

namespace App\Http\Controllers;

use App\Models\DocumentoDigital;
use App\Models\DocumentoAssinatura;
use App\Models\TipoDocumento;
use App\Models\ModeloDocumento;
use App\Models\UsuarioInterno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentoDigitalController extends Controller
{
    /**
     * Lista documentos digitais do usuário logado com filtros
     */
    public function index(Request $request)
    {
        $usuarioLogado = auth('interno')->user();
        $filtroStatus = $request->get('status', 'todos');
        
        // Query base: documentos relacionados ao usuário
        $query = DocumentoDigital::with(['tipoDocumento', 'usuarioCriador', 'processo', 'assinaturas.usuarioInterno'])
            ->where(function($q) use ($usuarioLogado) {
                // Documentos criados pelo usuário
                $q->where('usuario_criador_id', $usuarioLogado->id)
                  // OU documentos onde o usuário é assinante
                  ->orWhereHas('assinaturas', function($query) use ($usuarioLogado) {
                      $query->where('usuario_interno_id', $usuarioLogado->id);
                  });
            });
        
        // Aplicar filtro de status
        if ($filtroStatus !== 'todos') {
            switch ($filtroStatus) {
                case 'rascunho':
                    $query->where('status', 'rascunho')
                          ->where('usuario_criador_id', $usuarioLogado->id);
                    break;
                    
                case 'aguardando_minha_assinatura':
                    $query->where('status', 'aguardando_assinatura')
                          ->whereHas('assinaturas', function($q) use ($usuarioLogado) {
                              $q->where('usuario_interno_id', $usuarioLogado->id)
                                ->where('status', 'pendente');
                          });
                    break;
                    
                case 'assinados_por_mim':
                    $query->whereHas('assinaturas', function($q) use ($usuarioLogado) {
                        $q->where('usuario_interno_id', $usuarioLogado->id)
                          ->where('status', 'assinado');
                    });
                    break;
                    
                case 'aguardando_assinatura':
                    $query->where('status', 'aguardando_assinatura');
                    break;
                    
                case 'assinado':
                    $query->where('status', 'assinado');
                    break;
            }
        }
        
        $documentos = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Estatísticas para badges
        $stats = [
            'rascunhos' => DocumentoDigital::where('usuario_criador_id', $usuarioLogado->id)
                ->where('status', 'rascunho')
                ->count(),
            'aguardando_minha_assinatura' => DocumentoDigital::where('status', 'aguardando_assinatura')
                ->whereHas('assinaturas', function($q) use ($usuarioLogado) {
                    $q->where('usuario_interno_id', $usuarioLogado->id)
                      ->where('status', 'pendente');
                })
                ->count(),
            'assinados_por_mim' => DocumentoDigital::whereHas('assinaturas', function($q) use ($usuarioLogado) {
                $q->where('usuario_interno_id', $usuarioLogado->id)
                  ->where('status', 'assinado');
            })
            ->count(),
        ];

        return view('documentos.index', compact('documentos', 'filtroStatus', 'stats'));
    }

    /**
     * Exibe formulário para criar novo documento
     */
    public function create(Request $request)
    {
        $tiposDocumento = TipoDocumento::where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        // Busca usuários internos do mesmo município do usuário logado
        $usuarioLogado = auth('interno')->user();
        $usuariosInternosQuery = UsuarioInterno::where('ativo', true);
        
        // Filtra por município (tanto para gestores/técnicos municipais quanto estaduais)
        if ($usuarioLogado->municipio_id) {
            $usuariosInternosQuery->where('municipio_id', $usuarioLogado->municipio_id);
        }
        
        $usuariosInternos = $usuariosInternosQuery->orderBy('nome')->get();

        $processoId = $request->get('processo_id');
        $processo = null;

        if ($processoId) {
            $processo = \App\Models\Processo::with('estabelecimento')->find($processoId);
        }

        return view('documentos.create', compact('tiposDocumento', 'usuariosInternos', 'processo'));
    }

    /**
     * Busca modelos por tipo de documento (AJAX)
     */
    public function buscarModelos($tipoId)
    {
        $modelos = ModeloDocumento::where('tipo_documento_id', $tipoId)
            ->where('ativo', true)
            ->orderBy('ordem')
            ->get(['id', 'descricao', 'conteudo']);

        return response()->json($modelos);
    }

    /**
     * Salva novo documento
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'conteudo' => 'required',
            'sigiloso' => 'boolean',
            'assinaturas' => 'required|array|min:1',
            'assinaturas.*' => 'exists:usuarios_internos,id',
        ]);

        try {
            DB::beginTransaction();

            // Busca o tipo de documento para pegar o nome
            $tipoDocumento = TipoDocumento::findOrFail($request->tipo_documento_id);
            
            $documento = DocumentoDigital::create([
                'tipo_documento_id' => $request->tipo_documento_id,
                'processo_id' => $request->processo_id,
                'usuario_criador_id' => Auth::guard('interno')->id(),
                'numero_documento' => DocumentoDigital::gerarNumeroDocumento(),
                'nome' => $tipoDocumento->nome, // Nome do tipo de documento
                'conteudo' => $request->conteudo,
                'sigiloso' => $request->sigiloso ?? false,
                'status' => $request->acao === 'finalizar' ? 'aguardando_assinatura' : 'rascunho',
            ]);

            // Criar assinaturas
            foreach ($request->assinaturas as $index => $usuarioId) {
                DocumentoAssinatura::create([
                    'documento_digital_id' => $documento->id,
                    'usuario_interno_id' => $usuarioId,
                    'ordem' => $index + 1,
                    'obrigatoria' => true,
                    'status' => 'pendente',
                ]);
            }

            // Salva a primeira versão do documento
            $documento->salvarVersao(
                Auth::guard('interno')->id(),
                $request->conteudo,
                null
            );

            // Se finalizar, gera PDF e salva no processo
            if ($request->acao === 'finalizar' && $request->processo_id) {
                $this->gerarESalvarPDF($documento, $request->processo_id);
            }

            // ✅ REGISTRAR EVENTO NO HISTÓRICO
            if ($request->processo_id) {
                $processo = \App\Models\Processo::find($request->processo_id);
                if ($processo) {
                    \App\Models\ProcessoEvento::registrarDocumentoDigitalCriado($processo, $documento);
                }
            }

            DB::commit();

            // Se veio de um processo, redireciona de volta para o processo
            if ($request->processo_id) {
                $processo = \App\Models\Processo::find($request->processo_id);
                return redirect()->route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id])
                    ->with('success', 'Documento criado com sucesso!' . ($request->acao === 'finalizar' ? ' PDF gerado e anexado ao processo.' : ''));
            }

            return redirect()->route('admin.documentos.show', $documento->id)
                ->with('success', 'Documento criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao criar documento: ' . $e->getMessage());
        }
    }

    /**
     * Exibe documento
     */
    public function show($id)
    {
        $documento = DocumentoDigital::with(['tipoDocumento', 'usuarioCriador', 'processo', 'assinaturas.usuarioInterno'])
            ->findOrFail($id);

        return view('documentos.show', compact('documento'));
    }

    /**
     * Exibe formulário de edição (apenas para rascunhos)
     */
    public function edit($id)
    {
        // Log para debug de redirecionamento
        \Log::info('DocumentoDigitalController@edit chamado', [
            'documento_id' => $id,
            'usuario_autenticado' => auth('interno')->check(),
            'usuario_id' => auth('interno')->id(),
            'url_atual' => request()->url(),
        ]);
        
        $documento = DocumentoDigital::with(['tipoDocumento', 'processo', 'assinaturas', 'versoes.usuarioInterno'])
            ->findOrFail($id);

        // Apenas rascunhos podem ser editados
        if ($documento->status !== 'rascunho') {
            \Log::warning('Tentativa de editar documento não-rascunho', [
                'documento_id' => $id,
                'status' => $documento->status
            ]);
            
            // Redirect específico ao invés de back() para evitar loops
            return redirect()->route('admin.documentos.show', $documento->id)
                ->with('error', 'Apenas documentos em rascunho podem ser editados.');
        }

        $tiposDocumento = TipoDocumento::ativo()->ordenado()->get();
        
        // Busca usuários internos do mesmo município do usuário logado
        $usuarioLogado = auth('interno')->user();
        $usuariosInternosQuery = UsuarioInterno::ativo();
        
        // Filtra por município (tanto para gestores/técnicos municipais quanto estaduais)
        if ($usuarioLogado->municipio_id) {
            $usuariosInternosQuery->where('municipio_id', $usuarioLogado->municipio_id);
        }
        
        $usuariosInternos = $usuariosInternosQuery->ordenado()->get();
        $processo = $documento->processo;

        return view('documentos.edit', compact('documento', 'tiposDocumento', 'usuariosInternos', 'processo'));
    }

    /**
     * Atualiza documento (apenas rascunhos)
     */
    public function update(Request $request, $id)
    {
        $documento = DocumentoDigital::findOrFail($id);

        // Apenas rascunhos podem ser editados
        if ($documento->status !== 'rascunho') {
            return back()->with('error', 'Apenas documentos em rascunho podem ser editados.');
        }

        $request->validate([
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
            'conteudo' => 'required',
            'sigiloso' => 'boolean',
            'assinaturas' => 'required|array|min:1',
            'assinaturas.*' => 'exists:usuarios_internos,id',
        ]);

        try {
            DB::beginTransaction();

            $documento->update([
                'tipo_documento_id' => $request->tipo_documento_id,
                'conteudo' => $request->conteudo,
                'sigiloso' => $request->sigiloso ?? false,
                'status' => $request->acao === 'finalizar' ? 'aguardando_assinatura' : 'rascunho',
            ]);

            // Atualiza assinaturas
            $documento->assinaturas()->delete();
            foreach ($request->assinaturas as $index => $usuarioId) {
                DocumentoAssinatura::create([
                    'documento_digital_id' => $documento->id,
                    'usuario_interno_id' => $usuarioId,
                    'ordem' => $index + 1,
                    'obrigatoria' => true,
                    'status' => 'pendente',
                ]);
            }

            // SEMPRE salva nova versão quando salvar como rascunho
            // Isso garante que cada salvamento seja registrado no histórico
            $documento->salvarVersao(
                Auth::guard('interno')->id(),
                $request->conteudo,
                null
            );

            // Se finalizar, gera PDF e salva no processo
            if ($request->acao === 'finalizar' && $documento->processo_id) {
                $this->gerarESalvarPDF($documento, $documento->processo_id);
            }

            DB::commit();

            // Redireciona de volta para o processo
            if ($documento->processo_id) {
                $processo = \App\Models\Processo::find($documento->processo_id);
                return redirect()->route('admin.estabelecimentos.processos.show', [$processo->estabelecimento_id, $processo->id])
                    ->with('success', 'Documento atualizado com sucesso!' . ($request->acao === 'finalizar' ? ' PDF gerado e anexado ao processo.' : ''));
            }

            return redirect()->route('admin.documentos.show', $documento->id)
                ->with('success', 'Documento atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar documento: ' . $e->getMessage());
        }
    }

    /**
     * Gera PDF do documento
     */
    public function gerarPdf($id)
    {
        $documento = DocumentoDigital::findOrFail($id);

        $pdf = Pdf::loadHTML($documento->conteudo);
        
        return $pdf->download($documento->numero_documento . '.pdf');
    }

    /**
     * Exclui documento digital
     */
    public function destroy($id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);
            
            // ✅ REGISTRAR EVENTO NO HISTÓRICO ANTES DE EXCLUIR
            if ($documento->processo_id) {
                $processo = \App\Models\Processo::find($documento->processo_id);
                if ($processo) {
                    \App\Models\ProcessoEvento::registrarDocumentoDigitalExcluido($processo, $documento);
                }
            }
            
            // Remove assinaturas
            $documento->assinaturas()->delete();
            
            // Remove documento
            $documento->delete();
            
            return response()->json(['success' => true, 'message' => 'Documento excluído com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao excluir documento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Move documento para pasta
     */
    public function moverPasta(Request $request, $id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);
            $documento->update(['pasta_id' => $request->pasta_id]);
            
            return response()->json(['success' => true, 'message' => 'Documento movido com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao mover documento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Renomeia documento
     */
    public function renomear(Request $request, $id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);
            $documento->update(['nome' => $request->nome]);
            
            return response()->json(['success' => true, 'message' => 'Documento renomeado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao renomear documento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Assina documento
     */
    public function assinar(Request $request, $id)
    {
        $documento = DocumentoDigital::findOrFail($id);
        $usuarioId = Auth::guard('interno')->id();

        $assinatura = DocumentoAssinatura::where('documento_digital_id', $id)
            ->where('usuario_interno_id', $usuarioId)
            ->where('status', 'pendente')
            ->firstOrFail();

        $assinatura->update([
            'status' => 'assinado',
            'assinado_em' => now(),
            'hash_assinatura' => hash('sha256', $documento->id . $usuarioId . now()),
        ]);

        // Verifica se todas assinaturas foram feitas
        if ($documento->todasAssinaturasCompletas()) {
            $documento->update([
                'status' => 'assinado',
                'finalizado_em' => now(),
            ]);
        }

        return back()->with('success', 'Documento assinado com sucesso!');
    }

    /**
     * Restaura uma versão anterior do documento
     */
    public function restaurarVersao($documentoId, $versaoId)
    {
        try {
            $documento = DocumentoDigital::findOrFail($documentoId);
            
            // Apenas rascunhos podem ter versões restauradas
            if ($documento->status !== 'rascunho') {
                return back()->with('error', 'Apenas documentos em rascunho podem ter versões restauradas.');
            }
            
            $versao = \App\Models\DocumentoDigitalVersao::where('documento_digital_id', $documentoId)
                ->findOrFail($versaoId);
            
            // Apenas restaura o conteúdo, SEM criar nova versão
            // A versão será criada quando o usuário salvar como rascunho
            $documento->update([
                'conteudo' => $versao->conteudo
            ]);
            
            return back()->with('success', 'Versão ' . $versao->versao . ' restaurada com sucesso! Salve como rascunho para registrar a alteração.');
            
        } catch (\Exception $e) {
            \Log::error('Erro ao restaurar versão: ' . $e->getMessage());
            return back()->with('error', 'Erro ao restaurar versão.');
        }
    }

    /**
     * Gera PDF e salva como arquivo no processo
     */
    private function gerarESalvarPDF($documento, $processoId)
    {
        try {
            // Verifica se já existe um PDF gerado para este documento
            if ($documento->arquivo_pdf) {
                \Log::info('PDF já existe para o documento: ' . $documento->numero_documento);
                return;
            }
            
            // Gera o PDF
            $pdf = Pdf::loadHTML($documento->conteudo)
                ->setPaper('a4')
                ->setOption('margin-top', 20)
                ->setOption('margin-bottom', 20)
                ->setOption('margin-left', 20)
                ->setOption('margin-right', 20);
            
            // Nome do arquivo
            $nomeArquivo = $documento->numero_documento . '.pdf';
            $nomeArquivoSalvo = time() . '_' . $nomeArquivo;
            
            // Salva o PDF no storage
            $caminho = 'processos/' . $processoId . '/' . $nomeArquivoSalvo;
            \Storage::disk('public')->put($caminho, $pdf->output());
            
            // Verifica se já existe um registro de ProcessoDocumento para este documento digital
            $documentoExistente = \App\Models\ProcessoDocumento::where('processo_id', $processoId)
                ->where('observacoes', 'Documento Digital: ' . $documento->numero_documento)
                ->first();
            
            if (!$documentoExistente) {
                // Cria registro no banco
                \App\Models\ProcessoDocumento::create([
                    'processo_id' => $processoId,
                    'usuario_id' => Auth::guard('interno')->id(),
                    'tipo_usuario' => 'interno',
                    'nome_arquivo' => $nomeArquivoSalvo,
                    'nome_original' => $nomeArquivo,
                    'caminho' => $caminho,
                    'extensao' => 'pdf',
                    'tamanho' => strlen($pdf->output()),
                    'tipo_documento' => 'documento_digital', // Marca como documento digital
                    'observacoes' => 'Documento Digital: ' . $documento->numero_documento,
                ]);
            }
            
            // Atualiza o documento digital com o caminho do PDF
            $documento->update(['arquivo_pdf' => $caminho]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Gerencia assinantes do documento (adicionar/remover)
     */
    public function gerenciarAssinantes(Request $request, $id)
    {
        try {
            $documento = DocumentoDigital::with('assinaturas')->findOrFail($id);
            $usuarioLogado = auth('interno')->user();
            $isAdmin = $usuarioLogado->isAdmin();

            // Verifica se alguma assinatura já foi feita
            $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
            
            // Apenas administradores podem alterar após assinaturas feitas
            if ($temAssinaturaFeita && !$isAdmin) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível alterar assinantes após uma assinatura ter sido feita. Apenas administradores podem fazer isso.');
            }

            // Verifica se o documento já foi assinado completamente
            if ($documento->status === 'assinado') {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível alterar assinantes de um documento já assinado completamente.');
            }

            $assinantesNovos = $request->input('assinantes', []);
            $assinantesAtuais = $documento->assinaturas->pluck('usuario_interno_id')->toArray();

            // Remove assinantes que não estão mais na lista
            $assinantesRemover = array_diff($assinantesAtuais, $assinantesNovos);
            if (!empty($assinantesRemover)) {
                DocumentoAssinatura::where('documento_digital_id', $id)
                    ->whereIn('usuario_interno_id', $assinantesRemover)
                    ->delete();
            }

            // Adiciona novos assinantes
            $assinantesAdicionar = array_diff($assinantesNovos, $assinantesAtuais);
            $ordem = $documento->assinaturas()->max('ordem') ?? 0;
            
            foreach ($assinantesAdicionar as $usuarioId) {
                $ordem++;
                DocumentoAssinatura::create([
                    'documento_digital_id' => $id,
                    'usuario_interno_id' => $usuarioId,
                    'ordem' => $ordem,
                    'obrigatoria' => true,
                    'status' => 'pendente',
                ]);
            }

            // Reordena as assinaturas
            $assinaturas = DocumentoAssinatura::where('documento_digital_id', $id)
                ->orderBy('ordem')
                ->get();
            
            $novaOrdem = 1;
            foreach ($assinaturas as $assinatura) {
                $assinatura->ordem = $novaOrdem++;
                $assinatura->save();
            }

            return redirect()
                ->back()
                ->with('success', 'Assinantes atualizados com sucesso!');

        } catch (\Exception $e) {
            \Log::error('Erro ao gerenciar assinantes: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Erro ao gerenciar assinantes: ' . $e->getMessage());
        }
    }

    /**
     * Remove um assinante específico
     */
    public function removerAssinante($id)
    {
        try {
            $assinatura = DocumentoAssinatura::with('documentoDigital.assinaturas')->findOrFail($id);
            $documento = $assinatura->documentoDigital;
            $usuarioLogado = auth('interno')->user();
            $isAdmin = $usuarioLogado->isAdmin();

            // Verifica se alguma assinatura já foi feita
            $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
            
            // Apenas administradores podem remover após assinaturas feitas
            if ($temAssinaturaFeita && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível remover assinantes após uma assinatura ter sido feita. Apenas administradores podem fazer isso.'
                ], 400);
            }

            // Verifica se o documento já foi assinado completamente
            if ($documento->status === 'assinado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível remover assinantes de um documento já assinado completamente.'
                ], 400);
            }

            // Verifica se a assinatura já foi feita
            if ($assinatura->status === 'assinado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível remover um assinante que já assinou o documento.'
                ], 400);
            }

            $assinatura->delete();

            // Reordena as assinaturas restantes
            $assinaturas = DocumentoAssinatura::where('documento_digital_id', $documento->id)
                ->orderBy('ordem')
                ->get();
            
            $novaOrdem = 1;
            foreach ($assinaturas as $ass) {
                $ass->ordem = $novaOrdem++;
                $ass->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Assinante removido com sucesso!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao remover assinante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover assinante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Salva automaticamente o conteúdo do documento (rascunho)
     */
    public function salvarAutomaticamente(Request $request, $id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);
            
            // Apenas rascunhos podem ter salvamento automático
            if ($documento->status !== 'rascunho') {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas rascunhos podem ser salvos automaticamente.'
                ], 403);
            }

            $usuarioId = Auth::guard('interno')->id();
            $conteudoNovo = $request->input('conteudo');
            $conteudoAntigo = $documento->conteudo;

            // Calcula diferença
            $diff = \App\Models\DocumentoEdicao::calcularDiff($conteudoAntigo, $conteudoNovo);

            // Atualiza documento
            $documento->update([
                'conteudo' => $conteudoNovo,
                'ultimo_editor_id' => $usuarioId,
                'ultima_edicao_em' => now(),
                'versao_atual' => $documento->versao_atual + 1,
            ]);

            // Registra edição
            \App\Models\DocumentoEdicao::create([
                'documento_digital_id' => $documento->id,
                'usuario_interno_id' => $usuarioId,
                'conteudo' => $conteudoNovo,
                'diff' => $diff['diff'],
                'caracteres_adicionados' => $diff['adicionados'],
                'caracteres_removidos' => $diff['removidos'],
                'iniciado_em' => now(),
                'ativo' => true,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Busca editores ativos
            $editores = $documento->editoresAtivos();

            return response()->json([
                'success' => true,
                'versao' => $documento->versao_atual,
                'ultima_edicao' => $documento->ultima_edicao_em->toIso8601String(),
                'editores_ativos' => $editores->map(function($edicao) {
                    return [
                        'id' => $edicao->usuarioInterno->id,
                        'nome' => $edicao->usuarioInterno->nome,
                        'iniciado_em' => $edicao->iniciado_em->diffForHumans(),
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao salvar automaticamente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna editores atualmente ativos
     */
    public function editoresAtivos($id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);
            $editores = $documento->editoresAtivos();

            return response()->json([
                'success' => true,
                'editores' => $editores->map(function($edicao) {
                    return [
                        'id' => $edicao->usuarioInterno->id,
                        'nome' => $edicao->usuarioInterno->nome,
                        'iniciado_em' => $edicao->iniciado_em->diffForHumans(),
                        'caracteres_adicionados' => $edicao->caracteres_adicionados,
                        'caracteres_removidos' => $edicao->caracteres_removidos,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar editores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna conteúdo atual do documento para sincronização
     */
    public function obterConteudo($id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);

            return response()->json([
                'success' => true,
                'conteudo' => $documento->conteudo,
                'versao' => $documento->versao_atual,
                'ultima_edicao' => $documento->ultima_edicao_em?->toIso8601String(),
                'ultimo_editor' => $documento->ultimoEditor ? [
                    'id' => $documento->ultimoEditor->id,
                    'nome' => $documento->ultimoEditor->nome,
                ] : null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter conteúdo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marca início de edição
     */
    public function iniciarEdicao(Request $request, $id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);
            $usuarioId = Auth::guard('interno')->id();

            // Desativa edições antigas do mesmo usuário
            \App\Models\DocumentoEdicao::where('documento_digital_id', $documento->id)
                ->where('usuario_interno_id', $usuarioId)
                ->where('ativo', true)
                ->update(['ativo' => false, 'finalizado_em' => now()]);

            // Cria nova edição ativa
            $edicao = \App\Models\DocumentoEdicao::create([
                'documento_digital_id' => $documento->id,
                'usuario_interno_id' => $usuarioId,
                'conteudo' => $documento->conteudo,
                'iniciado_em' => now(),
                'ativo' => true,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Busca outros editores ativos
            $outrosEditores = $documento->editoresAtivos()
                ->where('usuario_interno_id', '!=', $usuarioId);

            return response()->json([
                'success' => true,
                'edicao_id' => $edicao->id,
                'outros_editores' => $outrosEditores->map(function($e) {
                    return [
                        'nome' => $e->usuarioInterno->nome,
                        'iniciado_em' => $e->iniciado_em->diffForHumans(),
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar edição: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finaliza edição
     */
    public function finalizarEdicao(Request $request, $id)
    {
        try {
            $usuarioId = Auth::guard('interno')->id();

            \App\Models\DocumentoEdicao::where('documento_digital_id', $id)
                ->where('usuario_interno_id', $usuarioId)
                ->where('ativo', true)
                ->update([
                    'ativo' => false,
                    'finalizado_em' => now(),
                ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar edição: ' . $e->getMessage()
            ], 500);
        }
    }
}
