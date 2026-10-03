<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReadingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'target_date' => ['required', 'date'],
            ];
        }

        return [
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
                Rule::unique('reading_plans', 'book_id')
                    ->where('user_id', $this->user()->id)
                    ->where('status', 'in_progress'),
            ],
            'target_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '選択された書籍は正しくありません。',
            'book_id.unique' => 'この書籍は、すでに読書中として登録されています。',
            'target_date.required' => '期日は必須です。',
            'target_date.date' => '期日は有効な日付を入力してください。',
        ];
    }
}