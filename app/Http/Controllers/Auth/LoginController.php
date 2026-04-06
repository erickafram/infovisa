<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Corrige a URL intended adicionando o prefixo do APP_URL se necessário.
     * O proxy reverso faz strip do /infovisacore, então o Laravel salva sem prefixo.
     */
    private function corrigirUrlIntended(Request $request): void
    {
        $intended = $request->session()->get('url.intended');
        if (!$intended) {
            return;
        }

        $appUrl = config('app.url');
        $parsedApp = parse_url($appUrl);
        $prefix = rtrim($parsedApp['path'] ?? '', '/');

        if (!$prefix) {
            return;
        }

        $parsedIntended = parse_url($intended);
        $intendedPath = $parsedIntended['path'] ?? '';

        if (!str_starts_with($intendedPath, $prefix)) {
            $host = ($parsedIntended['scheme'] ?? 'https') . '://' . ($parsedIntended['host'] ?? $parsedApp['host']);
            $corrected = $host . $prefix . $intendedPath
                . (isset($parsedIntended['query']) ? '?' . $parsedIntended['query'] : '');
            $request->session()->put('url.intended', $corrected);
        }
    }

    /**
     * Exibe o formulário de login unificado
     */
    public function showLoginForm()
    {
        return view('auth.login-unificado');
    }

    /**
     * Processa o login (tenta em ambos os guards)
     */
    public function login(Request $request)
    {
        $request->validate([
            'cpf' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'cpf.required' => 'O campo CPF é obrigatório.',
            'password.required' => 'O campo senha é obrigatório.',
        ]);

        // Remove formatação do CPF (pontos e traços)
        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
        
        $credentials = [
            'cpf' => $cpf,
            'password' => $request->password,
        ];

        $remember = $request->boolean('remember');

        // Tenta autenticar como usuário INTERNO primeiro
        if (Auth::guard('interno')->attempt(array_merge($credentials, ['ativo' => true]), $remember)) {
            $request->session()->regenerate();
            
            // Limpa URL intended se for uma rota AJAX (chat, api, etc)
            $intended = $request->session()->get('url.intended');
            if ($intended && (
                str_contains($intended, '/chat/') ||
                str_contains($intended, '/api/') ||
                str_contains($intended, 'verificar-novas') ||
                str_contains($intended, 'heartbeat')
            )) {
                $request->session()->forget('url.intended');
            }
            
            $this->corrigirUrlIntended($request);
            
            $fallback = route('admin.dashboard');
            return redirect()->intended($fallback);
        }

        // Se falhar, tenta autenticar como usuário EXTERNO
        if (Auth::guard('externo')->attempt(array_merge($credentials, ['ativo' => true]), $remember)) {
            $request->session()->regenerate();
            
            // Limpa URL intended se for uma rota AJAX (chat, api, etc)
            $intended = $request->session()->get('url.intended');
            if ($intended && (
                str_contains($intended, '/chat/') ||
                str_contains($intended, '/api/') ||
                str_contains($intended, 'verificar-novas') ||
                str_contains($intended, 'heartbeat')
            )) {
                $request->session()->forget('url.intended');
            }
            
            $this->corrigirUrlIntended($request);
            
            $fallback = route('company.dashboard');
            return redirect()->intended($fallback);
        }

        // Se ambos falharem, retorna erro
        throw ValidationException::withMessages([
            'cpf' => 'As credenciais fornecidas não correspondem aos nossos registros ou o usuário está inativo.',
        ]);
    }

    /**
     * Realiza o logout (detecta qual guard está ativo)
     */
    public function logout(Request $request)
    {
        if (Auth::guard('interno')->check()) {
            Auth::guard('interno')->logout();
        } elseif (Auth::guard('externo')->check()) {
            Auth::guard('externo')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

