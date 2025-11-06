<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuscarDiarioRequest extends FormRequest
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
            'texto' => 'required|string|min:3|max:255',
            'data_inicial' => 'required|date|before_or_equal:data_final',
            'data_final' => 'required|date|after_or_equal:data_inicial|before_or_equal:today'
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'texto.required' => 'O texto de busca é obrigatório',
            'texto.min' => 'O texto deve ter pelo menos 3 caracteres',
            'texto.max' => 'O texto não pode ter mais de 255 caracteres',
            'data_inicial.required' => 'A data inicial é obrigatória',
            'data_inicial.date' => 'A data inicial deve ser uma data válida',
            'data_inicial.before_or_equal' => 'A data inicial deve ser anterior ou igual à data final',
            'data_final.required' => 'A data final é obrigatória',
            'data_final.date' => 'A data final deve ser uma data válida',
            'data_final.after_or_equal' => 'A data final deve ser posterior ou igual à data inicial',
            'data_final.before_or_equal' => 'A data final não pode ser uma data futura'
        ];
    }
}
