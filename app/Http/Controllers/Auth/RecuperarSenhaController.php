<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UsuarioExterno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RecuperarSenhaController extends Controller
{
    /**
     * Mostra formulário para solicitar recuperação (digitar CPF)
     */
    public function showForm()
    {
        return view('auth.recuperar-senha');
    }

    /**
     * Envia email com link de recuperação
     */
    public function enviarLink(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string|min:11|max:14',
        ]);

        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);

        $usuario = UsuarioExterno::where('cpf', $cpf)->first();

        if (!$usuario || !$usuario->email) {
            return back()->with('error', 'CPF não encontrado ou sem e-mail cadastrado. Entre em contato com a Vigilância Sanitária.');
        }

        // Rate limiting: máximo 1 solicitação por hora
        $ultimaSolicitacao = DB::table('password_reset_tokens_externos')
            ->where('email', $usuario->email)
            ->first();

        if ($ultimaSolicitacao && Carbon::parse($ultimaSolicitacao->created_at)->diffInMinutes(now()) < 60) {
            $minutosRestantes = 60 - (int) Carbon::parse($ultimaSolicitacao->created_at)->diffInMinutes(now());
            return back()->with('error', "Você já solicitou a recuperação recentemente. Aguarde {$minutosRestantes} minuto(s) para solicitar novamente.");
        }

        // Rate limiting: máximo 3 solicitações por dia (por IP)
        $cacheKey = 'recuperar_senha_' . $request->ip() . '_' . now()->format('Y-m-d');
        $tentativasHoje = (int) \Cache::get($cacheKey, 0);

        if ($tentativasHoje >= 3) {
            return back()->with('error', 'Limite de 3 solicitações por dia atingido. Tente novamente amanhã.');
        }

        \Cache::put($cacheKey, $tentativasHoje + 1, now()->endOfDay());

        // Gera token
        $token = Str::random(64);

        // Remove tokens antigos
        DB::table('password_reset_tokens_externos')->where('email', $usuario->email)->delete();

        // Salva novo token
        DB::table('password_reset_tokens_externos')->insert([
            'email' => $usuario->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        // Envia email em background (defer) para não travar o request
        $link = url('/recuperar-senha/redefinir?token=' . $token . '&email=' . urlencode($usuario->email));
        $nomeUsuario = $usuario->nome;
        $emailDestinatario = $usuario->email;
        $emailMascarado = $this->mascararEmail($usuario->email);

        defer(function () use ($emailDestinatario, $nomeUsuario, $link) {
            try {
                Mail::send('emails.recuperar-senha', [
                    'nome' => $nomeUsuario,
                    'link' => $link,
                ], function ($message) use ($emailDestinatario, $nomeUsuario) {
                    $message->to($emailDestinatario, $nomeUsuario)
                            ->subject('Recuperação de Senha - InfoVISA');
                });
            } catch (\Exception $e) {
                \Log::error('Erro ao enviar email de recuperação: ' . $e->getMessage());
            }
        });

        return back()->with('success', "Link de recuperação enviado para {$emailMascarado}. Verifique sua caixa de entrada e spam.");
    }

    /**
     * Mostra formulário de redefinição de senha
     */
    public function showRedefinir(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect()->route('recuperar-senha.form')->with('error', 'Link inválido.');
        }

        return view('auth.redefinir-senha', compact('token', 'email'));
    }

    /**
     * Processa a redefinição de senha
     */
    public function redefinir(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $registro = DB::table('password_reset_tokens_externos')
            ->where('email', $request->email)
            ->first();

        if (!$registro) {
            return back()->with('error', 'Token inválido ou expirado.');
        }

        // Verifica se o token é válido
        if (!Hash::check($request->token, $registro->token)) {
            return back()->with('error', 'Token inválido.');
        }

        // Verifica se não expirou (60 minutos)
        if (Carbon::parse($registro->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens_externos')->where('email', $request->email)->delete();
            return redirect()->route('recuperar-senha.form')->with('error', 'Link expirado. Solicite um novo.');
        }

        // Atualiza a senha
        $usuario = UsuarioExterno::where('email', $request->email)->first();
        if (!$usuario) {
            return back()->with('error', 'Usuário não encontrado.');
        }

        $usuario->update(['password' => Hash::make($request->password)]);

        // Remove o token usado
        DB::table('password_reset_tokens_externos')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Senha redefinida com sucesso! Faça login com sua nova senha.');
    }

    private function mascararEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1];
        $masked = substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 2, 3));
        return $masked . '@' . $domain;
    }
}
