<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TarefaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // O utilizador autenticado pode criar/editar tarefas
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'estado' => 'sometimes|required|in:pendente,em_progresso,concluida,cancelada',
            'prioridade' => 'sometimes|required|in:baixa,media,alta,urgente',
            'categoria_id' => 'nullable|integer|exists:categorias,id',
            'data_conclusao' => 'nullable|date',
            'concluida' => 'sometimes|boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'O título é obrigatório.',
            'titulo.max' => 'O título não pode ter mais de 255 caracteres.',
            'estado.in' => 'O estado selecionado é inválido.',
            'prioridade.in' => 'A prioridade selecionada é inválida.',
            'data_conclusao.date' => 'A data de conclusão deve ser uma data válida.',
        ];
    }
}
