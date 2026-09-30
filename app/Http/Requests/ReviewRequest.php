<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('book')) {
            $this->merge([
                'book_id' => $this->route('book')->id,
            ]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];

        // 新規投稿時のみ、同一ユーザー×同一書籍の重複投稿をチェックする
        if ($this->isMethod('post')) {
            $rules['book_id'] = [
                Rule::unique('reviews', 'book_id')->where('user_id', $this->user()->id),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
        'rating.required' => '評価は必須です。',
        'rating.integer' => '評価は整数で入力してください。',
        'rating.between' => '評価は1〜5の範囲で入力してください。',
        'comment.string' => 'コメントは文字列で入力してください。',
        'comment.max' => 'コメントは1000文字以内で入力してください。',    
        'book_id.unique' => 'この書籍には、すでにレビューを投稿済みです。',
    ];
    }
}