<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_book_list(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_can_search_books_by_keyword(): void
    {
        Book::factory()->create(['title' => '吾輩は猫である']);
        Book::factory()->create(['title' => '別の本']);

        $response = $this->getJson('/api/v1/books?keyword=猫');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_filter_books_by_genre(): void
    {
        $genre = Genre::factory()->create();
        $matchingBook = Book::factory()->create();
        $matchingBook->genres()->attach($genre);
        Book::factory()->create(); // 紐付けなし

        $response = $this->getJson("/api/v1/books?genre_id={$genre->id}");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_pagination_respects_per_page_parameter(): void
    {
        Book::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/books?per_page=2');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('meta.per_page', 2);
    }

    public function test_per_page_is_capped_at_100(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books?per_page=500');

        $response->assertJsonPath('meta.per_page', 100);
    }

    public function test_can_get_book_detail(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $book->id);
    }

    public function test_book_detail_returns_404_for_nonexistent_book(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertNotFound();
        $response->assertJson(['message' => '書籍が見つかりませんでした。']);
    }

    public function test_can_create_book(): void
    {
        $genre = Genre::factory()->create();
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/books', [
            'title' => 'APIテスト本',
            'author' => 'テスト太郎',
            'isbn' => '9780000000001',
            'published_date' => '2024-01-01',
            'genres' => [$genre->id],
            'user_id' => $user->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('books', ['title' => 'APIテスト本']);
    }

    public function test_create_book_fails_with_invalid_data(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'author', 'isbn']);
    }

    public function test_can_update_book_with_unchanged_isbn(): void
    {
        $book = Book::factory()->create(['isbn' => '9780000000001']);
        $genre = Genre::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後タイトル',
            'author' => $book->author,
            'isbn' => '9780000000001',
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$genre->id],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', '更新後タイトル');
    }

    public function test_can_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}