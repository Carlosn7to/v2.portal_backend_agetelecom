<?php

namespace App\Http\Requests\Portal\AgeReport;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BuildReportRequest extends FormRequest
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
            'dateFilter.columnFilter' => 'required|string',
            'dateFilter.startDate' => 'required|date|before_or_equal:dateFilter.endDate',
            'dateFilter.endDate' => 'required|date|after_or_equal:dateFilter.startDate',
            'options.columns' => 'required|array',
            'options.typeArchive' => 'required|string|in:xlsx,csv,pdf', // Adicione mais tipos de arquivos se precisar
            'reportId' => 'required|integer|exists:age_relatorios,id', // Verifica se o reportId existe na tabela `reports`
        ];
    }

    public function messages()
    {

        return [
            'command.required' => 'O campo command é obrigatório',
            'command.string' => 'O campo command deve ser uma string',
            'dateFilter.columnFilter.required' => 'O campo dateFilter.columnFilter é obrigatório',
            'dateFilter.columnFilter.string' => 'O campo dateFilter.columnFilter deve ser uma string',
            'dateFilter.startDate.required' => 'O campo dateFilter.startDate é obrigatório',
            'dateFilter.startDate.date' => 'O campo dateFilter.startDate deve ser uma data',
            'dateFilter.startDate.before_or_equal' => 'O campo dateFilter.startDate deve ser menor ou igual a dateFilter.endDate',
            'dateFilter.endDate.required' => 'O campo dateFilter.endDate é obrigatório',
            'dateFilter.endDate.date' => 'O campo dateFilter.endDate deve ser uma data',
            'dateFilter.endDate.after_or_equal' => 'O campo dateFilter.endDate deve ser maior ou igual a dateFilter.startDate',
            'options.columns.required' => 'O campo options.columns é obrigatório',
            'options.columns.array' => 'O campo options.columns deve ser um array',
            'options.typeArchive.required' => 'O campo options.typeArchive é obrigatório',
            'options.typeArchive.string' => 'O campo options.typeArchive deve ser uma string',
            'options.typeArchive.in' => 'O campo options.typeArchive deve ser xlsx, csv ou pdf',
            'reportId.required' => 'O campo reportId é obrigatório',
            'reportId.integer' => 'O campo reportId deve ser um inteiro',
            'reportId.exists' => 'O campo reportId não existe na tabela de relatórios',
        ];

    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
