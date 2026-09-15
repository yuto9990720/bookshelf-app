<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_post_a_review(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('reviews.store', $book), [
            'rating' => 5,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_post_a_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 5,
            'comment' => '素晴らしい本でした。',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_rating_out_of_range_fails(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 6,
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_user_cannot_post_duplicate_review_for_same_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        Review::factory()->for($user)->for($book)->create();

        $response = $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 3,
            'comment' => '2回目の投稿',
        ]);

        $response->assertSessionHasErrors('book_id');
        $this->assertEquals(1, Review::where('book_id', $book->id)->where('user_id', $user->id)->count());
    }

    public function test_owner_can_update_their_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 4,
            'comment' => '更新後のコメント',
        ]);

        $response->assertRedirect(route('books.show', $review->book));
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 4]);
    }

    public function test_non_owner_cannot_update_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->for($owner)->create();

        $response = $this->actingAs($otherUser)->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    public function test_non_owner_cannot_delete_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->for($owner)->create();

        $response = $this->actingAs($otherUser)->delete(route('reviews.destroy', $review));

        $response->assertForbidden();
    }

    public function test_owner_can_delete_their_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $review->book));
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}