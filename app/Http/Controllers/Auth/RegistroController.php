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
    public function showRegistroForm(Request $request)
    {
        // CPF autorizado para testes
        $cpfAutorizado = '01758848111'; // 017.588.481-11 sem formatação
        
        // Verifica se o CPF foi fornecido via query string
        $cpfFornecido = $request->query('cpf');
        
        if ($cpfFornecido) {
            // Remove formatação do CPF fornecido
            $cpfLimpo = preg_replace('/\D/', '', $cpfFornecido);
            
            // Se o CPF não for o autorizado, bloqueia
            if ($cpfLimpo !== $cpfAutorizado) {
                abort(403, 'Cadastro temporariamente desabilitado. Apenas usuários autorizados podem se cadastrar no momento.');
            }
        } else {
            // Se não forneceu CPF, bloqueia
            abort(403, 'Cadastro temporariamente desabilitado. Apenas usuários autorizados podem se cadastrar no momento.');
        }
        
        $vinculos = VinculoEstabelecimento::toArray();
        
        return view('auth.registro', compact('vinculos', 'cpfFornecido'));
    }

    /**
     * Processa o registro do usuário externo
     */
    public function registro(RegistroUsuarioExternoRequest $request)
    {
        // CPF autorizado para testes
        $cpfAutorizado = '01758848111'; // 017.588.481-11 sem formatação
        
        // Remove formatação do CPF fornecido
        $cpfFornecido = preg_replace('/\D/', '', $request->input('cpf'));
        
        // Valida se é o CPF autorizado
        if ($cpfFornecido !== $cpfAutorizado) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cadastro temporariamente desabilitado. Apenas usuários autorizados podem se cadastrar no momento.');
        }
        
        try {
            // Remove máscaras para salvar no banco
            $dados = $request->validated();
            $dados['cpf'] = preg_replace('/\D/', '', $dados['cpf']);
            $dados['telefone'] = preg_replace('/\D/', '', $dados['telefone']);
            
            // Converte nome para maiúsculas
            $dados['nome'] = mb_strtoupper($dados['nome'], 'UTF-8');
            
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
