<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PesquisaSatisfacao;
use App\Models\PesquisaSatisfacaoPergunta;
use App\Models\PesquisaSatisfacaoOpcao;
use App\Models\TipoSetor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PesquisaSatisfacaoController extends Controller
{
    public function index()
    {
        $pesquisas = PesquisaSatisfacao::withCount('perguntas')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.pesquisas-satisfacao.index', compact('pesquisas'));
    }

    public function create()
    {
        $setores = TipoSetor::where('ativo', true)->orderBy('nome')->get();
        return view('admin.pesquisas-satisfacao.form', compact('setores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo'           => 'required|string|max:255',
            'descricao'        => 'nullable|string',
            'tipo_publico'     => 'required|in:interno,externo',
            'tipo_setores_ids' => 'nullable|array',
            'tipo_setores_ids.*' => 'integer',
            'ativo'            => 'nullable|boolean',
            'perguntas'        => 'nullable|array',
            'perguntas.*.texto'      => 'required|string|max:500',
            'perguntas.*.tipo'       => 'required|in:escala_1_5,multipla_escolha,texto_livre',
            'perguntas.*.obrigatoria' => 'nullable|boolean',
            'perguntas.*.opcoes'     => 'nullable|array',
            'perguntas.*.opcoes.*.texto' => 'required|string|max:255',
        ]);

        $pesquisa = PesquisaSatisfacao::create([
            'titulo'           => $data['titulo'],
            'descricao'        => $data['descricao'] ?? null,
            'tipo_publico'     => $data['tipo_publico'],
            'tipo_setores_ids' => $data['tipo_setores_ids'] ?? null,
            'ativo'            => $request->boolean('ativo', true),
            'slug'             => Str::slug($data['titulo']) . '-' . Str::random(8),
        ]);

        $this->salvarPerguntas($pesquisa, $data['perguntas'] ?? []);

        return redirect()
            ->route('admin.configuracoes.pesquisas-satisfacao.index')
            ->with('success', 'Pesquisa de satisfação criada com sucesso!');
    }

    public function edit(PesquisaSatisfacao $pesquisasSatisfacao)
    {
        $pesquisa = $pesquisasSatisfacao->load('perguntas.opcoes');
        $setores  = TipoSetor::where('ativo', true)->orderBy('nome')->get();
        return view('admin.pesquisas-satisfacao.form', compact('pesquisa', 'setores'));
    }

    public function update(Request $request, PesquisaSatisfacao $pesquisasSatisfacao)
    {
        $pesquisa = $pesquisasSatisfacao;

        $data = $request->validate([
            'titulo'           => 'required|string|max:255',
            'descricao'        => 'nullable|string',
            'tipo_publico'     => 'required|in:interno,externo',
            'tipo_setores_ids' => 'nullable|array',
            'tipo_setores_ids.*' => 'integer',
            'ativo'            => 'nullable|boolean',
            'perguntas'        => 'nullable|array',
            'perguntas.*.texto'      => 'required|string|max:500',
            'perguntas.*.tipo'       => 'required|in:escala_1_5,multipla_escolha,texto_livre',
            'perguntas.*.obrigatoria' => 'nullable|boolean',
            'perguntas.*.opcoes'     => 'nullable|array',
            'perguntas.*.opcoes.*.texto' => 'required|string|max:255',
        ]);

        $pesquisa->update([
            'titulo'           => $data['titulo'],
            'descricao'        => $data['descricao'] ?? null,
            'tipo_publico'     => $data['tipo_publico'],
            'tipo_setores_ids' => $data['tipo_setores_ids'] ?? null,
            'ativo'            => $request->boolean('ativo', true),
        ]);

        // Remove antigas e recria (abordagem simples e segura)
        $pesquisa->perguntas()->delete();
        $this->salvarPerguntas($pesquisa, $data['perguntas'] ?? []);

        return redirect()
            ->route('admin.configuracoes.pesquisas-satisfacao.index')
            ->with('success', 'Pesquisa atualizada com sucesso!');
    }

    public function destroy(PesquisaSatisfacao $pesquisasSatisfacao)
    {
        $pesquisasSatisfacao->delete();

        return redirect()
            ->route('admin.configuracoes.pesquisas-satisfacao.index')
            ->with('success', 'Pesquisa excluída com sucesso!');
    }

    public function toggleAtivo(PesquisaSatisfacao $pesquisasSatisfacao)
    {
        $pesquisasSatisfacao->update(['ativo' => !$pesquisasSatisfacao->ativo]);

        return back()->with('success', 'Status da pesquisa atualizado!');
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function salvarPerguntas(PesquisaSatisfacao $pesquisa, array $perguntas): void
    {
        foreach ($perguntas as $ordem => $perguntaData) {
            $pergunta = PesquisaSatisfacaoPergunta::create([
                'pesquisa_id' => $pesquisa->id,
                'texto'       => $perguntaData['texto'],
                'tipo'        => $perguntaData['tipo'],
                'obrigatoria' => isset($perguntaData['obrigatoria']) ? (bool) $perguntaData['obrigatoria'] : true,
                'ordem'       => $ordem,
            ]);

            if ($perguntaData['tipo'] === 'multipla_escolha' && !empty($perguntaData['opcoes'])) {
                foreach ($perguntaData['opcoes'] as $opcaoOrdem => $opcaoData) {
                    PesquisaSatisfacaoOpcao::create([
                        'pergunta_id' => $pergunta->id,
                        'texto'       => $opcaoData['texto'],
                        'ordem'       => $opcaoOrdem,
                    ]);
                }
            }
        }
    }
}
