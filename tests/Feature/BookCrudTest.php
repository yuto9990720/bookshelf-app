<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_accessing_create_page(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_a_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト駆動開発',
            'author' => 'Kent Beck',
            'isbn' => '9784798124582',
            'published_date' => '2017-10-14',
            'description' => 'あいうえお',
            'genres' => [$genre->id],
        ]);

        $book = Book::first();
        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', ['title' => 'テスト駆動開発']);
    }

    public function test_book_creation_fails_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'author', 'isbn']);
    }

    public function test_book_creation_fails_with_duplicate_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $existingBook = Book::factory()->create(['isbn' => '9784798124582']);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => '別のタイトル',
            'author' => '別の著者',
            'isbn' => '9784798124582',
            'published_date' => '2020-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_book_creation_fails_without_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '9784798124582',
            'published_date' => '2020-01-01',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors('genres');
    }

    public function test_owner_can_update_their_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新後のタイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => '更新後のタイトル']);
    }

    public function test_update_with_unchanged_isbn_does_not_fail(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->for($user)->create(['isbn' => '9784798124582']);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => '9784798124582',
            'published_date' => $book->published_date->format('Y-m-d'),
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_non_owner_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        $response = $this->actingAs($otherUser)->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    public function test_non_owner_cannot_delete_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        $response = $this->actingAs($otherUser)->delete(route('books.destroy', $book));

        $response->assertForbidden();
    }

    public function test_owner_can_delete_their_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}