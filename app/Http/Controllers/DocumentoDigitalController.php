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
use Illuminate\Support\Facades\Cache;
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
                    
                case 'com_prazos':
                    $query->whereNotNull('data_vencimento')
                          ->orderBy('data_vencimento', 'asc');
                    
                    // Filtro adicional por tipo de documento
                    if ($request->has('tipo_documento_id') && $request->get('tipo_documento_id') != '') {
                        $query->where('tipo_documento_id', $request->get('tipo_documento_id'));
                    }
                    break;
            }
        }
        
        $documentos = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Busca todos os tipos de documento para o filtro
        $tiposDocumento = \App\Models\TipoDocumento::where('ativo', true)
            ->orderBy('nome')
            ->get();
        
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
            'com_prazos' => DocumentoDigital::where(function($q) use ($usuarioLogado) {
                $q->where('usuario_criador_id', $usuarioLogado->id)
                  ->orWhereHas('assinaturas', function($query) use ($usuarioLogado) {
                      $query->where('usuario_interno_id', $usuarioLogado->id);
                  });
            })
            ->whereNotNull('data_vencimento')
            ->count(),
        ];

        return view('documentos.index', compact('documentos', 'filtroStatus', 'stats', 'tiposDocumento'));
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
            $processo = \App\Models\Processo::with('estabelecimento.municipioRelacionado')->find($processoId);
        }

        // Determina qual logomarca usar
        $logomarca = $this->determinarLogomarca($processo, $usuarioLogado);

        return view('documentos.create', compact('tiposDocumento', 'usuariosInternos', 'processo', 'logomarca'));
    }

    /**
     * Determina qual logomarca usar no documento baseado na competência e município
     * 
     * REGRAS:
     * 1. Se estabelecimento é de COMPETÊNCIA ESTADUAL -> sempre usa logomarca estadual
     * 2. Se estabelecimento é MUNICIPAL e município tem logomarca -> usa logomarca do município
     * 3. Se estabelecimento é MUNICIPAL mas município NÃO tem logomarca -> usa logomarca estadual (fallback)
     * 4. Se não houver processo -> usa logomarca do usuário logado
     */
    private function determinarLogomarca($processo, $usuarioLogado)
    {
        // Se não houver processo, usa logomarca do usuário
        if (!$processo || !$processo->estabelecimento) {
            return $usuarioLogado->getLogomarcaDocumento();
        }

        $estabelecimento = $processo->estabelecimento;
        
        // 1. Se estabelecimento é de COMPETÊNCIA ESTADUAL -> sempre usa logomarca estadual
        if ($estabelecimento->isCompetenciaEstadual()) {
            return \App\Models\ConfiguracaoSistema::logomarcaEstadual();
        }
        
        // 2. Se estabelecimento é MUNICIPAL e tem município vinculado
        if ($estabelecimento->municipio_id) {
            $municipio = $estabelecimento->municipioRelacionado;
            
            // Se município tem logomarca cadastrada, usa ela
            if ($municipio && $municipio->logomarca) {
                return $municipio->logomarca;
            }
        }
        
        // 3. Fallback: município sem logomarca ou sem município vinculado -> usa logomarca estadual
        return \App\Models\ConfiguracaoSistema::logomarcaEstadual();
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
     * Busca informações de prazo do tipo de documento (AJAX)
     */
    public function buscarPrazoTipo($tipoId)
    {
        $tipo = TipoDocumento::findOrFail($tipoId);

        return response()->json([
            'tem_prazo' => $tipo->tem_prazo,
            'prazo_padrao_dias' => $tipo->prazo_padrao_dias,
            'tipo_prazo' => $tipo->tipo_prazo ?? 'corridos',
        ]);
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
            'prazo_dias' => 'nullable|integer|min:1',
            'tipo_prazo' => 'nullable|in:corridos,uteis',
        ]);

        try {
            DB::beginTransaction();

            // Busca o tipo de documento para pegar o nome
            $tipoDocumento = TipoDocumento::findOrFail($request->tipo_documento_id);
            
            // Busca o processo e estabelecimento se existir
            $processo = null;
            $estabelecimento = null;
            if ($request->processo_id) {
                $processo = \App\Models\Processo::with(['estabelecimento.responsaveisTecnicos', 'estabelecimento.municipioRelacionado'])->find($request->processo_id);
                $estabelecimento = $processo?->estabelecimento;
            }
            
            // Substitui as variáveis no conteúdo
            $conteudoProcessado = $this->substituirVariaveis($request->conteudo, $estabelecimento, $processo);
            
            // Calcula data de vencimento se prazo foi informado
            $dataVencimento = null;
            $tipoPrazo = $request->tipo_prazo ?? 'corridos';
            
            if ($request->prazo_dias) {
                $dataInicio = now();
                $diasPrazo = (int) $request->prazo_dias;
                
                if ($tipoPrazo === 'corridos') {
                    // Dias corridos: simplesmente adiciona os dias
                    $dataVencimento = $dataInicio->addDays($diasPrazo)->format('Y-m-d');
                } else {
                    // Dias úteis: adiciona apenas dias úteis (segunda a sexta)
                    $diasAdicionados = 0;
                    $dataAtual = $dataInicio->copy();
                    
                    while ($diasAdicionados < $diasPrazo) {
                        $dataAtual->addDay();
                        // 0 = Domingo, 6 = Sábado
                        if ($dataAtual->dayOfWeek !== 0 && $dataAtual->dayOfWeek !== 6) {
                            $diasAdicionados++;
                        }
                    }
                    
                    $dataVencimento = $dataAtual->format('Y-m-d');
                }
            }
            
            $documento = DocumentoDigital::create([
                'tipo_documento_id' => $request->tipo_documento_id,
                'processo_id' => $request->processo_id,
                'usuario_criador_id' => Auth::guard('interno')->user()->id,
                'numero_documento' => DocumentoDigital::gerarNumeroDocumento(),
                'nome' => $tipoDocumento->nome, // Nome do tipo de documento
                'conteudo' => $conteudoProcessado, // Usa conteúdo com variáveis substituídas
                'sigiloso' => $request->sigiloso ?? false,
                'status' => $request->acao === 'finalizar' ? 'aguardando_assinatura' : 'rascunho',
                'prazo_dias' => $request->prazo_dias,
                'tipo_prazo' => $tipoPrazo,
                'data_vencimento' => $dataVencimento,
                'prazo_notificacao' => $tipoDocumento->prazo_notificacao ?? false, // Herda do tipo de documento
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
                Auth::guard('interno')->user()->id,
                $conteudoProcessado, // Usa conteúdo com variáveis substituídas
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

        // Permite editar se for rascunho OU se estiver aguardando assinatura mas ninguém assinou ainda
        if (!$documento->podeEditar()) {
            \Log::warning('Tentativa de editar documento que já possui assinaturas', [
                'documento_id' => $id,
                'status' => $documento->status
            ]);
            
            // Redirect específico ao invés de back() para evitar loops
            return redirect()->route('admin.documentos.show', $documento->id)
                ->with('error', 'Este documento já possui assinaturas e não pode mais ser editado.');
        }

        // Se o documento estava finalizado (aguardando_assinatura) e ninguém assinou,
        // reverte para rascunho para permitir a edição
        if ($documento->status === 'aguardando_assinatura') {
            $documento->update(['status' => 'rascunho']);
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

        // Permite editar se for rascunho OU se estiver aguardando assinatura mas ninguém assinou ainda
        if (!$documento->podeEditar()) {
            return back()->with('error', 'Este documento já possui assinaturas e não pode mais ser editado.');
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

            // Busca o tipo de documento para pegar prazo_notificacao
            $tipoDocumento = TipoDocumento::findOrFail($request->tipo_documento_id);

            $documento->update([
                'tipo_documento_id' => $request->tipo_documento_id,
                'conteudo' => $request->conteudo,
                'sigiloso' => $request->sigiloso ?? false,
                'status' => $request->acao === 'finalizar' ? 'aguardando_assinatura' : 'rascunho',
                'prazo_notificacao' => $tipoDocumento->prazo_notificacao ?? false, // Herda do tipo de documento
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
                Auth::guard('interno')->user()->id,
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
     * Gera PDF do documento para download com cabeçalho do estabelecimento
     */
    public function gerarPdf($id)
    {
        $documento = DocumentoDigital::with([
            'tipoDocumento',
            'processo.tipoProcesso',
            'processo.estabelecimento.responsaveis',
            'processo.estabelecimento.municipioRelacionado',
        ])->findOrFail($id);

        // Se já tem arquivo salvo, baixa ele
        if ($documento->arquivo_pdf && \Storage::disk('public')->exists($documento->arquivo_pdf)) {
            return \Storage::disk('public')->download($documento->arquivo_pdf, $documento->numero_documento . '.pdf');
        }

        // Gera PDF com cabeçalho usando o template pdf-preview
        $processo = $documento->processo;
        $estabelecimento = $processo ? $processo->estabelecimento : null;
        $usuarioLogado = \Auth::guard('interno')->user();
        $logomarca = $this->determinarLogomarca($processo, $usuarioLogado);

        $pdf = Pdf::loadView('documentos.pdf-preview', [
            'documento' => $documento,
            'processo' => $processo,
            'estabelecimento' => $estabelecimento,
            'logomarca' => $logomarca,
        ])
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
        
        return $pdf->download($documento->numero_documento . '.pdf');
    }

    /**
     * Gera PDF do documento para visualização (stream) com cabeçalho do estabelecimento
     */
    public function visualizarPdf($id)
    {
        $documento = DocumentoDigital::with([
            'tipoDocumento',
            'processo.tipoProcesso',
            'processo.estabelecimento.responsaveis',
            'processo.estabelecimento.municipioRelacionado',
        ])->findOrFail($id);

        // Se já tem arquivo PDF final salvo (documento assinado), exibe ele
        if ($documento->arquivo_pdf && \Storage::disk('public')->exists($documento->arquivo_pdf)) {
            return response()->file(\Storage::disk('public')->path($documento->arquivo_pdf), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $documento->numero_documento . '.pdf"'
            ]);
        }

        // Gera preview com cabeçalho usando o template pdf-preview
        $processo = $documento->processo;
        $estabelecimento = $processo ? $processo->estabelecimento : null;
        $usuarioLogado = \Auth::guard('interno')->user();
        $logomarca = $this->determinarLogomarca($processo, $usuarioLogado);

        $pdf = Pdf::loadView('documentos.pdf-preview', [
            'documento' => $documento,
            'processo' => $processo,
            'estabelecimento' => $estabelecimento,
            'logomarca' => $logomarca,
        ])
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
        
        return $pdf->stream($documento->numero_documento . '.pdf');
    }

    /**
     * Exclui documento digital (requer senha de assinatura)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);
            $usuario = Auth::guard('interno')->user();
            
            // Valida senha de assinatura
            if (!$usuario->temSenhaAssinatura()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Você precisa configurar sua senha de assinatura primeiro.'
                ], 400);
            }

            $senhaAssinatura = $request->input('senha_assinatura');
            
            if (!$senhaAssinatura || !\Hash::check($senhaAssinatura, $usuario->senha_assinatura_digital)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Senha de assinatura incorreta.'
                ], 400);
            }
            
            // ✅ REGISTRAR EVENTO NO HISTÓRICO ANTES DE EXCLUIR
            if ($documento->processo_id) {
                $processo = \App\Models\Processo::find($documento->processo_id);
                if ($processo) {
                    \App\Models\ProcessoEvento::create([
                        'processo_id' => $processo->id,
                        'usuario_interno_id' => $usuario->id,
                        'tipo_evento' => 'documento_digital_excluido',
                        'titulo' => 'Documento Digital Excluído',
                        'descricao' => 'Documento digital excluído: ' . ($documento->nome ?? $documento->tipoDocumento->nome ?? 'N/D'),
                        'dados_adicionais' => [
                            'nome_arquivo' => $documento->numero_documento,
                            'tipo_documento' => $documento->tipoDocumento->nome ?? 'N/D',
                            'excluido_por' => $usuario->nome,
                        ]
                    ]);
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
        $usuarioId = Auth::guard('interno')->user()->id;

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
            
            // Permite restaurar versão se for rascunho OU aguardando assinatura sem assinaturas realizadas
            if (!$documento->podeEditar()) {
                return back()->with('error', 'Este documento já possui assinaturas e não pode ter versões restauradas.');
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
                    'usuario_id' => Auth::guard('interno')->user()->id,
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
     * Registra que o usuário está editando o documento
     * Usado para evitar conflitos de edição simultânea
     */
    public function registrarEdicao($id)
    {
        $usuario = auth('interno')->user();
        $cacheKey = "documento_edicao_{$id}";
        
        // Verifica se outro usuário está editando
        $edicaoAtual = Cache::get($cacheKey);
        
        if ($edicaoAtual && $edicaoAtual['usuario_id'] !== $usuario->id) {
            // Outro usuário está editando - verifica se ainda está ativo (menos de 30 segundos)
            $ultimaAtividade = \Carbon\Carbon::parse($edicaoAtual['ultima_atividade']);
            if ($ultimaAtividade->diffInSeconds(now()) < 30) {
                return response()->json([
                    'success' => false,
                    'editando' => true,
                    'usuario_nome' => $edicaoAtual['usuario_nome'],
                    'message' => 'Outro usuário está editando este documento.'
                ]);
            }
        }
        
        // Registra a edição do usuário atual com TTL de 35 segundos
        Cache::put($cacheKey, [
            'usuario_id' => $usuario->id,
            'usuario_nome' => $usuario->nome,
            'ultima_atividade' => now()->toISOString(),
        ], 35);
        
        return response()->json([
            'success' => true,
            'editando' => false,
            'message' => 'Edição registrada.'
        ]);
    }

    /**
     * Verifica se outro usuário está editando o documento
     */
    public function verificarEdicao($id)
    {
        $usuario = auth('interno')->user();
        $cacheKey = "documento_edicao_{$id}";
        
        $edicaoAtual = Cache::get($cacheKey);
        
        if (!$edicaoAtual) {
            return response()->json([
                'editando' => false,
                'usuario_nome' => null
            ]);
        }
        
        // Se é o próprio usuário, não está bloqueado
        if ($edicaoAtual['usuario_id'] === $usuario->id) {
            return response()->json([
                'editando' => false,
                'usuario_nome' => null
            ]);
        }
        
        // Verifica se a edição ainda está ativa (menos de 30 segundos)
        $ultimaAtividade = \Carbon\Carbon::parse($edicaoAtual['ultima_atividade']);
        if ($ultimaAtividade->diffInSeconds(now()) >= 30) {
            // Edição expirou
            return response()->json([
                'editando' => false,
                'usuario_nome' => null
            ]);
        }
        
        return response()->json([
            'editando' => true,
            'usuario_nome' => $edicaoAtual['usuario_nome'],
            'ultima_atividade' => $edicaoAtual['ultima_atividade']
        ]);
    }

    /**
     * Libera a edição do documento (quando o usuário sai ou salva)
     */
    public function liberarEdicao($id)
    {
        $usuario = auth('interno')->user();
        $cacheKey = "documento_edicao_{$id}";
        
        $edicaoAtual = Cache::get($cacheKey);
        
        // Só libera se for o próprio usuário
        if ($edicaoAtual && $edicaoAtual['usuario_id'] === $usuario->id) {
            Cache::forget($cacheKey);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Edição liberada.'
        ]);
    }

    /**
     * Inicia edição colaborativa do documento
     */
    public function iniciarEdicao($id)
    {
        $usuario = auth('interno')->user();
        $cacheKey = "documento_editores_{$id}";
        
        // Busca editores atuais
        $editores = Cache::get($cacheKey, []);
        
        // Remove editores inativos (mais de 30 segundos)
        $editores = array_filter($editores, function($editor) {
            $ultimaAtividade = \Carbon\Carbon::parse($editor['ultima_atividade']);
            return $ultimaAtividade->diffInSeconds(now()) < 30;
        });
        
        // Gera ID único para esta sessão de edição
        $edicaoId = uniqid('edicao_');
        
        // Adiciona ou atualiza o editor atual
        $editores[$usuario->id] = [
            'usuario_id' => $usuario->id,
            'nome' => $usuario->nome,
            'edicao_id' => $edicaoId,
            'iniciado_em' => now()->format('H:i'),
            'ultima_atividade' => now()->toISOString(),
        ];
        
        // Salva no cache com TTL de 60 segundos
        Cache::put($cacheKey, $editores, 60);
        
        // Busca outros editores (excluindo o atual)
        $outrosEditores = array_filter($editores, function($editor) use ($usuario) {
            return $editor['usuario_id'] !== $usuario->id;
        });
        
        return response()->json([
            'success' => true,
            'edicao_id' => $edicaoId,
            'outros_editores' => array_values($outrosEditores)
        ]);
    }

    /**
     * Salva automaticamente o conteúdo do documento
     */
    public function salvarAuto(Request $request, $id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);
            $usuario = auth('interno')->user();
            
            // Permite salvar se for rascunho OU aguardando assinatura sem assinaturas realizadas
            if (!$documento->podeEditar()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este documento já possui assinaturas e não pode mais ser editado.'
                ], 400);
            }
            
            $conteudo = $request->input('conteudo');
            
            // Atualiza o documento
            $documento->update([
                'conteudo' => $conteudo,
                'ultimo_editor_id' => $usuario->id,
                'ultima_edicao_em' => now(),
            ]);
            
            // Incrementa versão interna (para controle de conflitos)
            $versao = Cache::increment("documento_versao_{$id}", 1) ?: 1;
            
            // Atualiza atividade do editor
            $cacheKey = "documento_editores_{$id}";
            $editores = Cache::get($cacheKey, []);
            
            if (isset($editores[$usuario->id])) {
                $editores[$usuario->id]['ultima_atividade'] = now()->toISOString();
                Cache::put($cacheKey, $editores, 60);
            }
            
            // Busca editores ativos
            $editoresAtivos = array_filter($editores, function($editor) use ($usuario) {
                $ultimaAtividade = \Carbon\Carbon::parse($editor['ultima_atividade']);
                return $ultimaAtividade->diffInSeconds(now()) < 30 && $editor['usuario_id'] !== $usuario->id;
            });
            
            return response()->json([
                'success' => true,
                'versao' => $versao,
                'editores_ativos' => array_values($editoresAtivos)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao salvar documento automaticamente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna lista de editores ativos do documento
     */
    public function editoresAtivos($id)
    {
        $usuario = auth('interno')->user();
        $cacheKey = "documento_editores_{$id}";
        
        $editores = Cache::get($cacheKey, []);
        
        // Remove editores inativos
        $editores = array_filter($editores, function($editor) {
            $ultimaAtividade = \Carbon\Carbon::parse($editor['ultima_atividade']);
            return $ultimaAtividade->diffInSeconds(now()) < 30;
        });
        
        // Atualiza o cache
        Cache::put($cacheKey, $editores, 60);
        
        return response()->json([
            'success' => true,
            'editores' => array_values($editores)
        ]);
    }

    /**
     * Obtém conteúdo atual do documento
     */
    public function obterConteudo($id)
    {
        try {
            $documento = DocumentoDigital::findOrFail($id);
            $versao = Cache::get("documento_versao_{$id}", 1);
            
            return response()->json([
                'success' => true,
                'conteudo' => $documento->conteudo,
                'versao' => $versao
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Documento não encontrado.'
            ], 404);
        }
    }

    /**
     * Finaliza a edição do documento (remove editor da lista)
     */
    public function finalizarEdicao($id)
    {
        $usuario = auth('interno')->user();
        $cacheKey = "documento_editores_{$id}";
        
        $editores = Cache::get($cacheKey, []);
        
        // Remove o usuário atual da lista de editores
        if (isset($editores[$usuario->id])) {
            unset($editores[$usuario->id]);
            Cache::put($cacheKey, $editores, 60);
        }
        
        // Também limpa o registro de edição simples
        $cacheKeySimples = "documento_edicao_{$id}";
        $edicaoAtual = Cache::get($cacheKeySimples);
        if ($edicaoAtual && $edicaoAtual['usuario_id'] === $usuario->id) {
            Cache::forget($cacheKeySimples);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Edição finalizada.'
        ]);
    }

    /**
     * Substitui variáveis no conteúdo do documento
     * 
     * @param string $conteudo O conteúdo do documento com variáveis
     * @param mixed $estabelecimento O estabelecimento relacionado (pode ser null)
     * @param mixed $processo O processo relacionado (pode ser null)
     * @return string O conteúdo com as variáveis substituídas
     */
    private function substituirVariaveis($conteudo, $estabelecimento = null, $processo = null)
    {
        // Se não houver conteúdo, retorna vazio
        if (empty($conteudo)) {
            return $conteudo;
        }

        $variaveis = [
            // Data
            '{data_atual}' => now()->format('d/m/Y'),
            '{data_extenso}' => now()->translatedFormat('d \d\e F \d\e Y'),
            '{data_extenso_maiusculo}' => strtoupper(now()->translatedFormat('d \d\e F \d\e Y')),
            '{data_atual_extenso}' => now()->translatedFormat('d \d\e F \d\e Y'),
            '{ano_atual}' => now()->format('Y'),
        ];

        // Variáveis do estabelecimento
        if ($estabelecimento) {
            $variaveis['{estabelecimento_nome}'] = $estabelecimento->nome_fantasia ?? $estabelecimento->razao_social ?? '';
            $variaveis['{estabelecimento_razao_social}'] = $estabelecimento->razao_social ?? '';
            $variaveis['{estabelecimento_cnpj}'] = $estabelecimento->cnpj_formatado ?? $estabelecimento->cnpj ?? '';
            $variaveis['{estabelecimento_cpf}'] = $estabelecimento->cpf_formatado ?? $estabelecimento->cpf ?? '';
            $variaveis['{estabelecimento_endereco}'] = trim(($estabelecimento->endereco ?? '') . ', ' . ($estabelecimento->numero ?? ''));
            $variaveis['{estabelecimento_bairro}'] = $estabelecimento->bairro ?? '';
            $variaveis['{estabelecimento_cidade}'] = $estabelecimento->cidade ?? '';
            $variaveis['{estabelecimento_estado}'] = $estabelecimento->estado ?? '';
            $variaveis['{estabelecimento_cep}'] = $estabelecimento->cep ?? '';
            $variaveis['{estabelecimento_telefone}'] = $estabelecimento->telefone_formatado ?? $estabelecimento->telefone ?? '';
            $variaveis['{estabelecimento_email}'] = $estabelecimento->email ?? '';
            $variaveis['{municipio}'] = $estabelecimento->cidade ?? $estabelecimento->municipioRelacionado?->nome ?? '';
            
            // Responsável técnico (pega o primeiro da lista de responsáveis técnicos)
            $responsavel = $estabelecimento->responsaveisTecnicos?->first() ?? null;
            $variaveis['{responsavel_nome}'] = $responsavel?->nome ?? '';
            $variaveis['{responsavel_cpf}'] = $responsavel?->cpf_formatado ?? $responsavel?->cpf ?? '';
            $variaveis['{responsavel_email}'] = $responsavel?->email ?? '';
            $variaveis['{responsavel_telefone}'] = $responsavel?->telefone ?? '';
            $variaveis['{responsavel_conselho}'] = $responsavel?->numero_conselho ?? '';
            
            // Atividades do estabelecimento - busca todas as atividades disponíveis
            $atividadesTexto = $this->formatarAtividadesEstabelecimento($estabelecimento);
            $variaveis['{atividades}'] = $atividadesTexto;
        } else {
            // Valores padrão quando não há estabelecimento
            $variaveis['{estabelecimento_nome}'] = '';
            $variaveis['{estabelecimento_razao_social}'] = '';
            $variaveis['{estabelecimento_cnpj}'] = '';
            $variaveis['{estabelecimento_cpf}'] = '';
            $variaveis['{estabelecimento_endereco}'] = '';
            $variaveis['{estabelecimento_bairro}'] = '';
            $variaveis['{estabelecimento_cidade}'] = '';
            $variaveis['{estabelecimento_estado}'] = '';
            $variaveis['{estabelecimento_cep}'] = '';
            $variaveis['{estabelecimento_telefone}'] = '';
            $variaveis['{estabelecimento_email}'] = '';
            $variaveis['{municipio}'] = '';
            $variaveis['{responsavel_nome}'] = '';
            $variaveis['{responsavel_cpf}'] = '';
            $variaveis['{responsavel_email}'] = '';
            $variaveis['{responsavel_telefone}'] = '';
            $variaveis['{responsavel_conselho}'] = '';
            $variaveis['{atividades}'] = '';
        }

        // Variáveis do processo
        if ($processo) {
            $variaveis['{processo_numero}'] = $processo->numero_processo ?? '';
            $variaveis['{processo_tipo}'] = $processo->tipo ?? '';
            $variaveis['{processo_status}'] = $processo->status_formatado ?? $processo->status ?? '';
            $variaveis['{processo_data_criacao}'] = $processo->created_at?->format('d/m/Y') ?? '';
            $variaveis['{processo_data_criacao_extenso}'] = $processo->created_at?->translatedFormat('d \d\e F \d\e Y') ?? '';
        } else {
            $variaveis['{processo_numero}'] = '';
            $variaveis['{processo_tipo}'] = '';
            $variaveis['{processo_status}'] = '';
            $variaveis['{processo_data_criacao}'] = '';
            $variaveis['{processo_data_criacao_extenso}'] = '';
        }

        // Substitui todas as variáveis
        return str_replace(array_keys($variaveis), array_values($variaveis), $conteudo);
    }

    /**
     * Formata as atividades do estabelecimento para exibição no documento
     * Busca atividades exercidas ou, se não houver, usa CNAE principal e secundários
     * 
     * @param mixed $estabelecimento
     * @return string
     */
    private function formatarAtividadesEstabelecimento($estabelecimento)
    {
        if (!$estabelecimento) {
            return '';
        }

        $listaAtividades = [];

        // 1. Primeiro tenta usar atividades_exercidas (atividades selecionadas pelo usuário)
        // Filtra apenas atividades com código CNAE válido (numérico, ex: 86.40-2-02 ou 8640202)
        if ($estabelecimento->atividades_exercidas && is_array($estabelecimento->atividades_exercidas) && count($estabelecimento->atividades_exercidas) > 0) {
            foreach ($estabelecimento->atividades_exercidas as $atividade) {
                if (is_array($atividade)) {
                    $codigo = $atividade['codigo'] ?? '';
                    $descricao = $atividade['descricao'] ?? $atividade['nome'] ?? '';
                    $principal = isset($atividade['principal']) && $atividade['principal'];
                    
                    // Verifica se o código é um CNAE válido (deve conter apenas números e formatação)
                    // Ignora códigos como "PROJ_ARQ", "DOC_123", etc.
                    $codigoLimpo = preg_replace('/[^0-9]/', '', $codigo);
                    $isCodigoCnaeValido = !empty($codigoLimpo) && strlen($codigoLimpo) >= 5 && strlen($codigoLimpo) <= 7;
                    
                    // Só inclui se tiver código CNAE válido
                    if ($isCodigoCnaeValido && ($descricao || $codigo)) {
                        $texto = '<div style="margin-bottom: 10px; display: flex; align-items: baseline;">';
                        if ($codigo) {
                            // Formata o código CNAE (ex: 4711301 -> 47.11-3-01)
                            $codigoFormatado = $this->formatarCodigoCnae($codigo);
                            $texto .= "<span style=\"font-weight: bold; margin-right: 15px; min-width: 90px; display: inline-block;\">{$codigoFormatado}</span>";
                        }
                        $texto .= "<span>{$descricao}";
                        if ($principal) {
                            $texto .= ' - Principal';
                        }
                        $texto .= '</span></div>';
                        $listaAtividades[] = $texto;
                    }
                } elseif (is_string($atividade) && !empty($atividade)) {
                    // Verifica se é um código CNAE válido
                    $codigoLimpo = preg_replace('/[^0-9]/', '', $atividade);
                    if (!empty($codigoLimpo) && strlen($codigoLimpo) >= 5) {
                        $listaAtividades[] = '<div style="margin-bottom: 10px;">' . $atividade . '</div>';
                    }
                }
            }
        }

        // 2. Se não tem atividades exercidas válidas, usa CNAE principal e secundários
        if (empty($listaAtividades)) {
            // CNAE Principal
            if ($estabelecimento->cnae_fiscal) {
                $codigoFormatado = $this->formatarCodigoCnae($estabelecimento->cnae_fiscal);
                $descricao = $estabelecimento->cnae_fiscal_descricao ?? '';
                $texto = "<div style=\"margin-bottom: 10px; display: flex; align-items: baseline;\">";
                $texto .= "<span style=\"font-weight: bold; margin-right: 15px; min-width: 90px; display: inline-block;\">{$codigoFormatado}</span>";
                $texto .= "<span>{$descricao} - Principal</span></div>";
                $listaAtividades[] = $texto;
            }

            // CNAEs Secundários
            if ($estabelecimento->cnaes_secundarios && is_array($estabelecimento->cnaes_secundarios)) {
                foreach ($estabelecimento->cnaes_secundarios as $cnae) {
                    if (is_array($cnae)) {
                        $codigo = $cnae['codigo'] ?? '';
                        $descricao = $cnae['descricao'] ?? $cnae['texto'] ?? '';
                        
                        if ($codigo || $descricao) {
                            $texto = '<div style="margin-bottom: 10px; display: flex; align-items: baseline;">';
                            if ($codigo) {
                                $codigoFormatado = $this->formatarCodigoCnae($codigo);
                                $texto .= "<span style=\"font-weight: bold; margin-right: 15px; min-width: 90px; display: inline-block;\">{$codigoFormatado}</span>";
                            }
                            $texto .= "<span>{$descricao}</span></div>";
                            $listaAtividades[] = $texto;
                        }
                    } elseif (is_string($cnae) && !empty($cnae)) {
                        $codigoFormatado = $this->formatarCodigoCnae($cnae);
                        $listaAtividades[] = "<div style=\"margin-bottom: 10px;\"><span style=\"font-weight: bold;\">{$codigoFormatado}</span></div>";
                    }
                }
            }
        }

        return implode("", $listaAtividades);
    }

    /**
     * Formata código CNAE no padrão XX.XX-X-XX
     * 
     * @param string $codigo
     * @return string
     */
    private function formatarCodigoCnae($codigo)
    {
        // Remove caracteres não numéricos
        $codigo = preg_replace('/[^0-9]/', '', $codigo);
        
        // Se já tem 7 dígitos, formata no padrão XX.XX-X-XX
        if (strlen($codigo) === 7) {
            return substr($codigo, 0, 2) . '.' . 
                   substr($codigo, 2, 2) . '-' . 
                   substr($codigo, 4, 1) . '-' . 
                   substr($codigo, 5, 2);
        }
        
        // Retorna como está se não tiver 7 dígitos
        return $codigo;
    }

}
