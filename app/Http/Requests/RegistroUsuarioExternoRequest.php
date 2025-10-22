<?php

namespace App\Http\Requests;

use App\Enums\VinculoEstabelecimento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegistroUsuarioExternoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Qualquer pessoa pode se cadastrar
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:3', 'max:255'],
            'cpf' => ['required', 'string', 'size:14', 'unique:usuarios_externos,cpf', 'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuarios_externos,email'],
            'telefone' => ['required', 'string', 'min:14', 'max:15', 'regex:/^\(\d{2}\) \d{4,5}-\d{4}$/'],
            'vinculo_estabelecimento' => ['required', Rule::enum(VinculoEstabelecimento::class)],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()],
            'aceite_termos' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter no mínimo 3 caracteres.',
            
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'cpf.regex' => 'O CPF deve estar no formato: 000.000.000-00',
            
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            
            'telefone.required' => 'O telefone é obrigatório.',
            'telefone.regex' => 'O telefone deve estar no formato: (00) 00000-0000',
            
            'vinculo_estabelecimento.required' => 'Selecione o seu vínculo com o estabelecimento.',
            
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'As senhas não conferem.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            
            'aceite_termos.required' => 'Você deve aceitar os termos e condições.',
            'aceite_termos.accepted' => 'Você deve aceitar os termos e condições para continuar.',
        ];
    }

    /**
     * Prepara os dados para validação
     */
    protected function prepareForValidation(): void
    {
        // Remove máscaras antes de validar
        $this->merge([
            'cpf' => preg_replace('/\D/', '', $this->cpf ?? ''),
            'telefone' => preg_replace('/\D/', '', $this->telefone ?? ''),
        ]);

        // Reformata com máscara para validação
        if ($this->cpf) {
            $cpf = $this->cpf;
            if (strlen($cpf) === 11) {
                $this->merge([
                    'cpf' => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf)
                ]);
            }
        }

        if ($this->telefone) {
            $telefone = $this->telefone;
            if (strlen($telefone) === 11) {
                $this->merge([
                    'telefone' => preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone)
                ]);
            } elseif (strlen($telefone) === 10) {
                $this->merge([
                    'telefone' => preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone)
                ]);
            }
        }
    }
}

