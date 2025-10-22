<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'password.required' => 'O campo senha é obrigatório.',
        ]);

        $remember = $request->boolean('remember');

        // Tenta autenticar como usuário INTERNO primeiro
        if (Auth::guard('interno')->attempt(array_merge($credentials, ['ativo' => true]), $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        // Se falhar, tenta autenticar como usuário EXTERNO
        if (Auth::guard('externo')->attempt(array_merge($credentials, ['ativo' => true]), $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('company.dashboard'));
        }

        // Se ambos falharem, retorna erro
        throw ValidationException::withMessages([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registros ou o usuário está inativo.',
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

