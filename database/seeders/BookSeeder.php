<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::first()->id;

        $books = [
            ['no' => 1, 'title' => '吾輩は猫である', 'author' => '夏目漱石', 'isbn' => '9784101010014', 'published_date' => '1905-01-01', 'description' => '教師の家に迷い込んだ一匹の猫の目を通して、人間社会を風刺的かつユーモラスに描いた夏目漱石の代表作。', 'genres' => ['小説']],
            ['no' => 2, 'title' => '人を動かす', 'author' => 'D・カーネギー', 'isbn' => '9784422100524', 'published_date' => '1936-10-01', 'description' => '人間関係の原則を説いた自己啓発書の古典。相手の立場に立つことの重要性を説く。', 'genres' => ['ビジネス', '自己啓発']],
            ['no' => 3, 'title' => 'リーダブルコード', 'author' => 'Dustin Boswell', 'isbn' => '9784873115658', 'published_date' => '2012-06-23', 'description' => '読みやすく保守しやすいコードを書くための実践的なテクニックをまとめたエンジニア必読書。', 'genres' => ['技術書']],
            ['no' => 4, 'title' => '7つの習慣', 'author' => 'スティーブン・R・コヴィー', 'isbn' => '9784863940246', 'published_date' => '2013-08-30', 'description' => '人格主義に基づいた、人生とビジネスにおける成功の原則を解説する世界的ベストセラー。', 'genres' => ['ビジネス', '自己啓発']],
            ['no' => 5, 'title' => '坊っちゃん', 'author' => '夏目漱石', 'isbn' => '9784101010021', 'published_date' => '1906-04-01', 'description' => '江戸っ子気質の教師が四国の中学校に赴任し、周囲と衝突しながら奮闘する痛快な物語。', 'genres' => ['小説']],
            ['no' => 6, 'title' => 'サピエンス全史', 'author' => 'ユヴァル・ノア・ハラリ', 'isbn' => '9784309226712', 'published_date' => '2016-09-08', 'description' => '認知革命・農業革命・科学革命という切り口から、人類の歴史を壮大なスケールで描き直す。', 'genres' => ['歴史', '科学']],
            ['no' => 7, 'title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9784048930598', 'published_date' => '2017-12-18', 'description' => '汚いコードと綺麗なコードの違いを具体例とともに解説する、ソフトウェア開発者向けの指南書。', 'genres' => ['技術書']],
            ['no' => 8, 'title' => '嫌われる勇気', 'author' => '岸見一郎・古賀史健', 'isbn' => '9784478025819', 'published_date' => '2013-12-13', 'description' => 'アドラー心理学を対話形式で紐解き、自由に生きるための考え方を提示するベストセラー。', 'genres' => ['自己啓発']],
            ['no' => 9, 'title' => '火花', 'author' => '又吉直樹', 'isbn' => '9784163902302', 'published_date' => '2015-03-11', 'description' => '売れない芸人同士の師弟関係を描いた、芥川賞受賞の青春小説。', 'genres' => ['小説']],
            ['no' => 10, 'title' => 'FACTFULNESS', 'author' => 'ハンス・ロスリング', 'isbn' => '9784822289607', 'published_date' => '2019-01-11', 'description' => 'データに基づき世界を正しく見るための10の思考法を紹介する国際的ベストセラー。', 'genres' => ['ビジネス', '科学']],
            ['no' => 11, 'title' => 'コンテナ物語', 'author' => 'マルク・レビンソン', 'isbn' => '9784822251468', 'published_date' => '2007-01-18', 'description' => 'コンテナという輸送技術の発明が世界経済をどう変えたかを描くノンフィクション。', 'genres' => ['ビジネス', '歴史']],
        ];

        foreach ($books as $data) {
            $book = Book::firstOrCreate(
                ['isbn' => $data['isbn']],
                [
                    'title' => $data['title'],
                    'author' => $data['author'],
                    'published_date' => $data['published_date'],
                    'description' => $data['description'],
                    'image_url' => "https://placehold.co/200x300/e2e8f0/475569?text={$data['no']}",
                    'user_id' => $userId,
                ]
            );

            $genreIds = Genre::whereIn('name', $data['genres'])->pluck('id');
            $book->genres()->sync($genreIds);
        }
    }
}