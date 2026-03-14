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
        return redirect()
            ->route('admin.relatorios.pesquisa-satisfacao', array_merge(
                $request->query(),
                ['aba' => 'pesquisas']
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
            ->route('admin.relatorios.pesquisa-satisfacao', ['aba' => 'pesquisas'])
            ->with('success', 'Resposta excluída com sucesso!');
    }
}
