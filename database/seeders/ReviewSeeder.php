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
            3 => ['普通でした。', '可もなく不可もなく、という印象です。', '期待したほどではありませんでした。'],
            4 => ['とても参考になりました。', '読みやすくておすすめです。', '期待通りの内容でした。'],
            5 => ['素晴らしい本でした！', '人生観が変わる一冊です。', '何度も読み返しています。'],
        ];

        // 各書籍に配分するレビュー件数（2〜4件、合計32件になるよう調整）
        $reviewCounts = [3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 2];

        $books = Book::orderBy('id')->get();

        foreach ($books as $index => $book) {
            $count = $reviewCounts[$index] ?? 3;

            // その本にレビューするユーザーを、5人の中からランダムにcount人選ぶ
            $reviewers = User::inRandomOrder()->limit($count)->get();

            foreach ($reviewers as $user) {
                $rating = rand(3, 5);

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