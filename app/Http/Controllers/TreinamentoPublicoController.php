<?php

namespace App\Http\Controllers;

use App\Models\TreinamentoEvento;
use App\Models\TreinamentoInscricao;
use App\Models\TreinamentoPergunta;
use App\Models\TreinamentoPerguntaResposta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TreinamentoPublicoController extends Controller
{
    public function inscricao(string $token)
    {
        $evento = TreinamentoEvento::where('link_inscricao_token', $token)->firstOrFail();

        abort_unless($evento->inscricoes_ativas, 404);

        return view('treinamentos.public.inscricao', compact('evento'));
    }

    public function salvarInscricao(Request $request, string $token)
    {
        $evento = TreinamentoEvento::where('link_inscricao_token', $token)->firstOrFail();
        abort_unless($evento->inscricoes_ativas, 404);

        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'nullable|string|max:30',
            'instituicao' => 'nullable|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        $inscricao = TreinamentoInscricao::updateOrCreate(
            [
                'treinamento_evento_id' => $evento->id,
                'email' => $data['email'],
            ],
            [
                ...$data,
                'token' => TreinamentoInscricao::where('treinamento_evento_id', $evento->id)
                    ->where('email', $data['email'])
                    ->value('token') ?? (string) Str::uuid(),
            ]
        );

        session([
            $this->participantSessionKey($evento->id) => [
                'nome' => $inscricao->nome,
                'email' => $inscricao->email,
                'telefone' => $inscricao->telefone,
                'inscricao_id' => $inscricao->id,
            ],
        ]);

        return redirect()
            ->route('treinamentos.public.inscricao', $token)
            ->with('success', 'Inscrição realizada com sucesso.');
    }

    public function pergunta(string $token)
    {
        $pergunta = TreinamentoPergunta::with(['opcoes', 'slide.apresentacao.evento'])
            ->where('token', $token)
            ->firstOrFail();

        abort_unless($pergunta->ativa, 404);

        $evento = $pergunta->slide->apresentacao->evento;
        $participante = session($this->participantSessionKey($evento->id), []);

        return view('treinamentos.public.pergunta', compact('pergunta', 'evento', 'participante'));
    }

    public function responderPergunta(Request $request, string $token)
    {
        $pergunta = TreinamentoPergunta::with(['opcoes', 'slide.apresentacao.evento'])
            ->where('token', $token)
            ->firstOrFail();

        abort_unless($pergunta->ativa, 404);

        $evento = $pergunta->slide->apresentacao->evento;
        $sessionKey = $this->participantSessionKey($evento->id);
        $participante = session($sessionKey, []);

        $rules = [
            'opcao_id' => 'required|integer|exists:treinamento_pergunta_opcoes,id',
        ];

        if (empty($participante['nome']) || empty($participante['email'])) {
            $rules['nome'] = 'required|string|max:255';
            $rules['email'] = 'required|email|max:255';
            $rules['telefone'] = 'nullable|string|max:30';
        }

        $data = $request->validate($rules);

        abort_unless($pergunta->opcoes->contains('id', (int) $data['opcao_id']), 422);

        $nome = $participante['nome'] ?? $data['nome'];
        $email = $participante['email'] ?? $data['email'];
        $telefone = $participante['telefone'] ?? ($data['telefone'] ?? null);
        $tokenSessao = session('treinamento_token_sessao') ?: (string) Str::uuid();
        session(['treinamento_token_sessao' => $tokenSessao]);

        $inscricao = null;
        if ($email) {
            $inscricao = TreinamentoInscricao::where('treinamento_evento_id', $evento->id)
                ->where('email', $email)
                ->first();
        }

        session([
            $sessionKey => [
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'inscricao_id' => $inscricao?->id,
            ],
        ]);

        $identifier = [
            'treinamento_pergunta_id' => $pergunta->id,
        ];

        if ($inscricao) {
            $identifier['treinamento_inscricao_id'] = $inscricao->id;
        } elseif ($email) {
            $identifier['participante_email'] = $email;
        } else {
            $identifier['token_sessao'] = $tokenSessao;
        }

        TreinamentoPerguntaResposta::updateOrCreate(
            $identifier,
            [
                'treinamento_pergunta_opcao_id' => $data['opcao_id'],
                'treinamento_inscricao_id' => $inscricao?->id,
                'token_sessao' => $tokenSessao,
                'participante_nome' => $nome,
                'participante_email' => $email,
                'participante_telefone' => $telefone,
            ]
        );

        return redirect()
            ->route('treinamentos.public.pergunta.obrigado', $pergunta->token)
            ->with('success', 'Resposta registrada com sucesso.');
    }

    public function obrigado(string $token)
    {
        $pergunta = TreinamentoPergunta::with('slide.apresentacao.evento')
            ->where('token', $token)
            ->firstOrFail();

        $evento = $pergunta->slide->apresentacao->evento;

        return view('treinamentos.public.obrigado', compact('pergunta', 'evento'));
    }

    private function participantSessionKey(int $eventoId): string
    {
        return 'treinamento_participante_' . $eventoId;
    }
}
