<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            1 => ['残念ながら合いませんでした。', '期待と違いました。'],
            2 => ['少し期待外れでした。', '内容が薄い印象。', 'もう少し深掘りしてほしかった。'],
            3 => ['普通でした。', '可もなく不可もなく、という印象です。', '期待したほどではありませんでした。'],
            4 => ['とても参考になりました。', '読みやすくておすすめです。', '期待通りの内容でした。'],
            5 => ['素晴らしい本でした！', '人生観が変わる一冊です。', '何度も読み返しています。'],
        ];

        $books = Book::orderBy('id')->get();

        foreach ($books as $book) {
            $count = rand(2, 4);
            $reviewers = User::inRandomOrder()->limit($count)->get();

            foreach ($reviewers as $user) {
                $rating = rand(1, 5);

                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating][array_rand($comments[$rating])],
                ]);
            }
        }
    }
}