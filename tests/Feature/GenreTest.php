<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_genre_index_shows_book_count(): void
    {
        $genre = Genre::factory()->create();
        Book::factory()->count(2)->create()->each(fn ($book) => $book->genres()->attach($genre));

        $response = $this->get(route('genres.index'));

        $response->assertOk();
        $response->assertViewHas('genres', function ($genres) use ($genre) {
            return $genres->firstWhere('id', $genre->id)->books_count === 2;
        });
    }

    public function test_authenticated_user_can_create_a_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'ミステリー',
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', ['name' => 'ミステリー']);
    }

    public function test_genre_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => 'ミステリー']);

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'ミステリー',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_any_authenticated_user_can_update_a_genre(): void
    {
        $genre = Genre::factory()->create(['name' => '旧ジャンル名']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => '新ジャンル名',
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => '新ジャンル名']);
    }

    public function test_genre_deletion_is_blocked_when_books_are_linked(): void
    {
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    public function test_genre_can_be_deleted_when_no_books_are_linked(): void
    {
        $genre = Genre::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }
}