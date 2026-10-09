<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookIsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_isbn_search_returns_book_information(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'リーダブルコード',
                            'authors' => ['Dustin Boswell', 'Trevor Foucher'],
                            'description' => '読みやすいコードについての本。',
                            'publishedDate' => '2012-06-23',
                            'imageLinks' => ['thumbnail' => 'https://example.com/image.jpg'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784873115658');

        $response->assertOk();
        $response->assertJson([
            'title' => 'リーダブルコード',
            'author' => 'Dustin Boswell、Trevor Foucher',
            'published_date' => '2012-06-23',
        ]);
    }

    public function test_isbn_search_returns_error_when_book_not_found(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response(['totalItems' => 0], 200),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9780000000000');

        $response->assertStatus(404);
        $response->assertJson(['error' => '書籍が見つかりませんでした。']);
    }

    public function test_isbn_search_returns_error_when_api_fails(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([], 429),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784873115658');

        $response->assertStatus(429);
        $response->assertJson(['error' =>'Google Books API のクォータを超過しました。.env に GOOGLE_BOOKS_API_KEY を設定してください。']);
    }

    public function test_isbn_search_returns_error_on_server_failure(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/*' => Http::response([], 503),
        ]);

        $response = $this->actingAs($user)->getJson('/books/isbn/9784873115658');

        $response->assertStatus(500);
        $response->assertJson(['error' => 'API通信エラーが発生しました。']);
    }
}