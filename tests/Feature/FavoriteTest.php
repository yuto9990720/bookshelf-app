<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_toggle_favorite(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('login'));
    }

    public function test_toggle_favorite_three_times(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 1回目：追加
        $this->actingAs($user)->post(route('favorites.toggle', $book));
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'book_id' => $book->id]);

        // 2回目：解除
        $this->actingAs($user)->post(route('favorites.toggle', $book));
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'book_id' => $book->id]);

        // 3回目：再追加
        $this->actingAs($user)->post(route('favorites.toggle', $book));
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'book_id' => $book->id]);
    }

    public function test_favorite_index_shows_only_own_favorites(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $myFavorite = Book::factory()->create();
        $othersFavorite = Book::factory()->create();

        $user->favoriteBooks()->attach($myFavorite);
        $otherUser->favoriteBooks()->attach($othersFavorite);

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertViewHas('books', function ($books) use ($myFavorite, $othersFavorite) {
            return $books->contains($myFavorite) && ! $books->contains($othersFavorite);
        });
    }

    public function test_toggle_favorite_does_not_set_flash_message(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));

        $response->assertSessionMissing('success');
        $response->assertSessionMissing('error');
    }
}