<?php

return [

    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で入力してください。',
    'email' => ':attributeは有効なメールアドレス形式で入力してください。',
    'unique' => ':attributeはすでに使用されています。',
    'digits' => ':attributeは:digits桁で入力してください。',
    'date' => ':attributeは有効な日付を入力してください。',
    'url' => ':attributeは有効なURL形式で入力してください。',
    'array' => ':attributeを選択してください。',
    'exists' => '選択された:attributeは正しくありません。',
    'integer' => ':attributeは整数で入力してください。',
    'confirmed' => ':attributeと確認用の入力が一致しません。',

    'max' => [
        'numeric' => ':attributeは:max以下の数値で入力してください。',
        'string' => ':attributeは:max文字以内で入力してください。',
    ],

    'min' => [
        'numeric' => ':attributeは:min以上の数値で入力してください。',
        'string' => ':attributeは:min文字以上で入力してください。',
    ],

    'between' => [
        'numeric' => ':attributeは:min〜:maxの範囲で入力してください。',
    ],

    'custom' => [
        //
    ],

    'attributes' => [
        'title' => 'タイトル',
        'author' => '著者',
        'isbn' => 'ISBN',
        'published_date' => '出版日',
        'description' => '説明',
        'image_url' => '画像URL',
        'genres' => 'ジャンル',
        'rating' => '評価',
        'comment' => 'コメント',
        'name' => 'ジャンル名',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
    ],

];