<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistroUsuarioExternoRequest;
use App\Enums\VinculoEstabelecimento;
use App\Models\UsuarioExterno;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RegistroController extends Controller
{
    /**
     * Exibe o formulário de registro
     */
    public function showRegistroForm(Request $request)
    {
        // Cadastro habilitado para todos os CPFs
        $cpfFornecido = $request->query('cpf');
        
        return view('auth.registro', compact('cpfFornecido'));
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
            $dados['vinculo_estabelecimento'] = $dados['vinculo_estabelecimento'] ?? VinculoEstabelecimento::PROPRIETARIO->value;
            
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
        } catch (QueryException $e) {
            Log::error('Falha no cadastro de usuário externo (QueryException)', [
                'cpf' => preg_replace('/\D/', '', $request->input('cpf', '')),
                'email' => $request->input('email'),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            $mensagem = strtolower($e->getMessage());

            if (str_contains($mensagem, 'usuarios_externos_cpf_unique') || str_contains($mensagem, 'duplicate key') && str_contains($mensagem, 'cpf')) {
                return redirect()->back()->withInput()->withErrors(['cpf' => 'Este CPF já está cadastrado.']);
            }

            if (str_contains($mensagem, 'usuarios_externos_email_unique') || str_contains($mensagem, 'duplicate key') && str_contains($mensagem, 'email')) {
                return redirect()->back()->withInput()->withErrors(['email' => 'Este e-mail já está cadastrado.']);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao realizar cadastro. Tente novamente.');
        } catch (\Throwable $e) {
            Log::error('Falha no cadastro de usuário externo', [
                'cpf' => preg_replace('/\D/', '', $request->input('cpf', '')),
                'email' => $request->input('email'),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao realizar cadastro. Tente novamente.');
        }
    }

}
