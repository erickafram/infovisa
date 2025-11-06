<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalvarBuscaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('interno')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'texto' => 'required|string|max:255',
            'data_inicial' => 'required|date',
            'data_final' => 'required|date'
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome da busca é obrigatório',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres',
            'texto.required' => 'O texto de busca é obrigatório',
            'texto.max' => 'O texto não pode ter mais de 255 caracteres',
            'data_inicial.required' => 'A data inicial é obrigatória',
            'data_inicial.date' => 'A data inicial deve ser uma data válida',
            'data_final.required' => 'A data final é obrigatória',
            'data_final.date' => 'A data final deve ser uma data válida'
        ];
    }
}
