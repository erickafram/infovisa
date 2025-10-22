<?php

namespace App\Http\Controllers\Auth;

use App\Enums\VinculoEstabelecimento;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistroUsuarioExternoRequest;
use App\Models\UsuarioExterno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistroController extends Controller
{
    /**
     * Exibe o formulário de registro
     */
    public function showRegistroForm()
    {
        $vinculos = VinculoEstabelecimento::toArray();
        
        return view('auth.registro', compact('vinculos'));
    }

    /**
     * Processa o registro do usuário externo
     */
    public function registro(RegistroUsuarioExternoRequest $request)
    {
        try {
            // Remove máscaras para salvar no banco
            $dados = $request->validated();
            $dados['cpf'] = preg_replace('/\D/', '', $dados['cpf']);
            $dados['telefone'] = preg_replace('/\D/', '', $dados['telefone']);
            
            // Registra o aceite dos termos
            $dados['aceite_termos_em'] = now();
            $dados['ip_aceite_termos'] = $request->ip();
            
            // Cria o usuário
            $usuario = UsuarioExterno::create($dados);

            // Faz login automático
            Auth::guard('externo')->login($usuario);

            return redirect()->route('company.dashboard')
                ->with('success', 'Cadastro realizado com sucesso! Bem-vindo ao InfoVISA.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao realizar cadastro. Tente novamente.');
        }
    }

}
