<?php

namespace App\Http\Requests\Api\V1;


use App\Http\Requests\BookRequest;

class StoreBookRequest extends BookRequest
{
      
    public function rules(): array
    {
       return array_merge(parent::rules(), [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'user_id.required' => '登録者IDは必須です。',
            'user_id.integer' => '登録者IDは整数で入力してください。',
            'user_id.exists' => '選択された登録者IDは正しくありません。',
        ]);
    }
    
}
