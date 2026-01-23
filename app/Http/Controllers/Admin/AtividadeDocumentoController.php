<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Atividade;
use App\Models\TipoDocumentoObrigatorio;
use App\Models\TipoServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AtividadeDocumentoController extends Controller
{
    /**
     * Exibe a página de configuração de documentos por atividade
     */
    public function index(Request $request)
    {
        // Tipos de Serviço com atividades e contagem de documentos
        $tiposServico = TipoServico::ativos()
            ->with(['atividadesAtivas' => function($query) {
                $query->withCount('documentosObrigatorios');
            }])
            ->ordenado()
            ->get();

        // Documentos comuns (aplicados automaticamente)
        $documentosComuns = TipoDocumentoObrigatorio::ativos()
            ->documentosComuns()
            ->ordenado()
            ->get();

        // Documentos específicos (para vincular às atividades)
        $documentosEspecificos = TipoDocumentoObrigatorio::ativos()
            ->documentosEspecificos()
            ->ordenado()
            ->get();

        // Todos os documentos para o modal de criação
        $todosDocumentos = TipoDocumentoObrigatorio::ativos()
            ->ordenado()
            ->get();

        // Filtros
        $tipoServicoFiltro = $request->input('tipo_servico_id');
        $buscaAtividade = $request->input('busca');

        // Atividades filtradas
        $queryAtividades = Atividade::with(['tipoServico', 'documentosObrigatorios'])
            ->withCount('documentosObrigatorios')
            ->where('ativo', true);

        if ($tipoServicoFiltro) {
            $queryAtividades->where('tipo_servico_id', $tipoServicoFiltro);
        }

        if ($buscaAtividade) {
            $queryAtividades->where(function($q) use ($buscaAtividade) {
                $q->where('nome', 'ilike', "%{$buscaAtividade}%")
                  ->orWhere('codigo_cnae', 'ilike', "%{$buscaAtividade}%");
            });
        }

        $atividades = $queryAtividades->ordenado()->paginate(20)->withQueryString();

        return view('configuracoes.atividade-documento.index', compact(
            'tiposServico',
            'documentosComuns',
            'documentosEspecificos',
            'todosDocumentos',
            'atividades'
        ));
    }

    /**
     * Exibe os documentos de uma atividade específica
     */
    public function show(Atividade $atividade)
    {
        $atividade->load(['tipoServico', 'documentosObrigatorios']);

        $documentosDisponiveis = TipoDocumentoObrigatorio::ativos()
            ->documentosEspecificos()
            ->whereNotIn('id', $atividade->documentosObrigatorios->pluck('id'))
            ->ordenado()
            ->get();

        return view('configuracoes.atividade-documento.show', compact('atividade', 'documentosDisponiveis'));
    }

    /**
     * Atualiza os documentos de uma atividade
     */
    public function update(Request $request, Atividade $atividade)
    {
        $validated = $request->validate([
            'documentos' => 'nullable|array',
            'documentos.*.id' => 'required|exists:tipos_documento_obrigatorio,id',
            'documentos.*.obrigatorio' => 'boolean',
            'documentos.*.observacao' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Sincroniza documentos
            $documentosData = [];
            
            if (!empty($validated['documentos'])) {
                foreach ($validated['documentos'] as $index => $doc) {
                    $documentosData[$doc['id']] = [
                        'obrigatorio' => $doc['obrigatorio'] ?? true,
                        'observacao' => $doc['observacao'] ?? null,
                        'ordem' => $index,
                    ];
                }
            }

            $atividade->documentosObrigatorios()->sync($documentosData);

            DB::commit();

            return redirect()
                ->route('admin.configuracoes.atividade-documento.show', $atividade)
                ->with('success', 'Documentos da atividade atualizados com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao atualizar documentos: ' . $e->getMessage());
        }
    }

    /**
     * Adiciona um documento a uma atividade
     */
    public function adicionarDocumento(Request $request, Atividade $atividade)
    {
        $validated = $request->validate([
            'tipo_documento_obrigatorio_id' => 'required|exists:tipos_documento_obrigatorio,id',
            'obrigatorio' => 'boolean',
            'observacao' => 'nullable|string|max:500',
        ]);

        // Verifica se já existe
        if ($atividade->documentosObrigatorios()->where('tipos_documento_obrigatorio.id', $validated['tipo_documento_obrigatorio_id'])->exists()) {
            return back()->with('error', 'Este documento já está vinculado a esta atividade.');
        }

        // Pega a maior ordem atual
        $maxOrdem = $atividade->documentosObrigatorios()->max('atividade_documento.ordem') ?? -1;

        $atividade->documentosObrigatorios()->attach($validated['tipo_documento_obrigatorio_id'], [
            'obrigatorio' => $validated['obrigatorio'] ?? true,
            'observacao' => $validated['observacao'] ?? null,
            'ordem' => $maxOrdem + 1,
        ]);

        return back()->with('success', 'Documento adicionado com sucesso!');
    }

    /**
     * Remove um documento de uma atividade
     */
    public function removerDocumento(Atividade $atividade, TipoDocumentoObrigatorio $documento)
    {
        $atividade->documentosObrigatorios()->detach($documento->id);

        return back()->with('success', 'Documento removido com sucesso!');
    }

    /**
     * Copia documentos de uma atividade para outra
     */
    public function copiarDocumentos(Request $request, Atividade $atividade)
    {
        $validated = $request->validate([
            'atividade_origem_id' => 'required|exists:atividades,id',
            'substituir' => 'boolean',
        ]);

        $atividadeOrigem = Atividade::with('documentosObrigatorios')->findOrFail($validated['atividade_origem_id']);

        DB::beginTransaction();
        try {
            if ($validated['substituir'] ?? false) {
                // Remove documentos existentes
                $atividade->documentosObrigatorios()->detach();
            }

            // Copia documentos da origem
            foreach ($atividadeOrigem->documentosObrigatorios as $doc) {
                // Verifica se já existe (se não for substituir)
                if (!($validated['substituir'] ?? false) && 
                    $atividade->documentosObrigatorios()->where('tipos_documento_obrigatorio.id', $doc->id)->exists()) {
                    continue;
                }

                $atividade->documentosObrigatorios()->attach($doc->id, [
                    'obrigatorio' => $doc->pivot->obrigatorio,
                    'observacao' => $doc->pivot->observacao,
                    'ordem' => $doc->pivot->ordem,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Documentos copiados com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao copiar documentos: ' . $e->getMessage());
        }
    }

    /**
     * Aplica documentos em lote para múltiplas atividades
     */
    public function aplicarEmLote(Request $request)
    {
        $validated = $request->validate([
            'atividades' => 'required|array|min:1',
            'atividades.*' => 'exists:atividades,id',
            'documentos' => 'required|array|min:1',
            'documentos.*' => 'exists:tipos_documento_obrigatorio,id',
            'obrigatorio' => 'boolean',
            'substituir' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['atividades'] as $atividadeId) {
                $atividade = Atividade::find($atividadeId);

                if ($validated['substituir'] ?? false) {
                    $atividade->documentosObrigatorios()->detach();
                }

                foreach ($validated['documentos'] as $index => $docId) {
                    // Verifica se já existe
                    if (!($validated['substituir'] ?? false) && 
                        $atividade->documentosObrigatorios()->where('tipos_documento_obrigatorio.id', $docId)->exists()) {
                        continue;
                    }

                    $atividade->documentosObrigatorios()->attach($docId, [
                        'obrigatorio' => $validated['obrigatorio'] ?? true,
                        'ordem' => $index,
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Documentos aplicados em lote com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao aplicar documentos em lote: ' . $e->getMessage());
        }
    }

    /**
     * API: Retorna documentos de uma atividade (para AJAX)
     */
    public function getDocumentos(Atividade $atividade)
    {
        $atividade->load('documentosObrigatorios');

        return response()->json([
            'atividade' => [
                'id' => $atividade->id,
                'nome' => $atividade->nome,
                'codigo_cnae' => $atividade->codigo_cnae,
            ],
            'documentos' => $atividade->documentosObrigatorios->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'nome' => $doc->nome,
                    'nomenclatura' => $doc->nomenclatura,
                    'obrigatorio' => $doc->pivot->obrigatorio,
                    'observacao' => $doc->pivot->observacao,
                    'ordem' => $doc->pivot->ordem,
                ];
            }),
        ]);
    }
}
