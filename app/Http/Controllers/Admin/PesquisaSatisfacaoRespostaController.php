<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PesquisaSatisfacao;
use App\Models\PesquisaSatisfacaoResposta;
use Illuminate\Http\Request;

class PesquisaSatisfacaoRespostaController extends Controller
{
    /**
     * Lista todas as respostas com filtros.
     */
    public function index(Request $request)
    {
        $pesquisas = PesquisaSatisfacao::orderBy('titulo')->get();

        $query = PesquisaSatisfacaoResposta::with([
            'pesquisa', 'ordemServico', 'estabelecimento',
            'usuarioInterno', 'usuarioExterno',
        ])->orderByDesc('created_at');

        // Filtro por pesquisa
        if ($request->filled('pesquisa_id')) {
            $query->where('pesquisa_id', $request->pesquisa_id);
        }

        // Filtro por tipo de respondente
        if ($request->filled('tipo_respondente')) {
            $query->where('tipo_respondente', $request->tipo_respondente);
        }

        // Filtro por OS
        if ($request->filled('ordem_servico_id')) {
            $query->where('ordem_servico_id', $request->ordem_servico_id);
        }

        // Filtro por data
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        // Busca por nome do respondente
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('respondente_nome', 'ilike', "%{$busca}%")
                  ->orWhere('respondente_email', 'ilike', "%{$busca}%");
            });
        }

        $respostas = $query->paginate(20)->withQueryString();

        // Estatísticas
        $totalRespostas   = PesquisaSatisfacaoResposta::count();
        $respostasInterno = PesquisaSatisfacaoResposta::where('tipo_respondente', 'interno')->count();
        $respostasExterno = PesquisaSatisfacaoResposta::where('tipo_respondente', 'externo')->count();

        return view('admin.pesquisas-satisfacao.respostas.index', compact(
            'respostas', 'pesquisas', 'totalRespostas',
            'respostasInterno', 'respostasExterno'
        ));
    }

    /**
     * Exibe os detalhes de uma resposta individual.
     */
    public function show($id)
    {
        $resposta = PesquisaSatisfacaoResposta::findOrFail($id);
        $resposta->load([
            'pesquisa.perguntas.opcoes', 'ordemServico',
            'estabelecimento', 'usuarioInterno', 'usuarioExterno',
        ]);

        return view('admin.pesquisas-satisfacao.respostas.show', compact('resposta'));
    }

    /**
     * Exclui uma resposta.
     */
    public function destroy($id)
    {
        $resposta = PesquisaSatisfacaoResposta::findOrFail($id);
        $resposta->delete();

        return redirect()
            ->route('admin.pesquisas-satisfacao.respostas.index')
            ->with('success', 'Resposta excluída com sucesso!');
    }
}
