<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    /**
     * Exibe a página de perfil do usuário
     */
    public function index()
    {
        $usuario = auth('interno')->user();
        
        return view('admin.perfil.index', compact('usuario'));
    }

    /**
     * Atualiza os dados pessoais do usuário
     */
    public function updateDados(Request $request)
    {
        $usuario = auth('interno')->user();

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:usuarios_internos,email,' . $usuario->id,
        ], [
            'nome.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso por outro usuário.',
        ]);

        // Limpa formatação do telefone
        if (!empty($validated['telefone'])) {
            $validated['telefone'] = preg_replace('/\D/', '', $validated['telefone']);
        }

        $usuario->update($validated);

        return redirect()->route('admin.perfil.index')
            ->with('success', 'Dados atualizados com sucesso!');
    }

    /**
     * Atualiza a senha de acesso do usuário
     */
    public function updateSenha(Request $request)
    {
        $usuario = auth('interno')->user();

        $validated = $request->validate([
            'senha_atual' => 'required|string',
            'nova_senha' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ], [
            'senha_atual.required' => 'Informe sua senha atual.',
            'nova_senha.required' => 'Informe a nova senha.',
            'nova_senha.min' => 'A nova senha deve ter no mínimo 8 caracteres.',
            'nova_senha.confirmed' => 'A confirmação da nova senha não confere.',
        ]);

        // Verifica se a senha atual está correta
        if (!Hash::check($validated['senha_atual'], $usuario->password)) {
            return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
        }

        // Verifica se a nova senha é diferente da atual
        if (Hash::check($validated['nova_senha'], $usuario->password)) {
            return back()->withErrors(['nova_senha' => 'A nova senha deve ser diferente da senha atual.']);
        }

        $usuario->update([
            'password' => Hash::make($validated['nova_senha']),
        ]);

        return redirect()->route('admin.perfil.index')
            ->with('success', 'Senha alterada com sucesso!');
    }
}
