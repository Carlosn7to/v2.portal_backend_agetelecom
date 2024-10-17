<?php

namespace App\Http\Requests\Portal\AgeReport;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'command' => 'required|string',
            'userId' => 'required|integer|exists:portal_usuarios,id',
            'level' => 'required|string|in:usuario,administrador',
            'reports' => 'array',
        ];
    }

    public function messages()
    {
        return [
            'command.required' => 'O campo command é obrigatório',
            'command.string' => 'O campo command deve ser uma string',
            'userId.required' => 'O campo userId é obrigatório',
            'userId.integer' => 'O campo userId deve ser um inteiro',
            'userId.exists' => 'O userId não existe na tabela de usuários',
            'level.required' => 'O campo level é obrigatório',
            'level.string' => 'O campo level deve ser uma string',
            'level.in' => 'O campo level deve ser usuario ou administrador',
            'reports.array' => 'O campo reports deve ser um array',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
