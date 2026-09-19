<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_ranking_page(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertOk();
    }

    public function test_books_are_ordered_by_average_rating_descending(): void
    {
        $highRated = Book::factory()->create(['title' => '高評価の本']);
        Review::factory()->for($highRated)->create(['rating' => 5]);

        $lowRated = Book::factory()->create(['title' => '低評価の本']);
        Review::factory()->for($lowRated)->create(['rating' => 2]);

        $response = $this->get(route('ranking.index'));

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($highRated, $lowRated) {
            return $rankedBooks->search(fn ($b) => $b->id === $highRated->id)
                 < $rankedBooks->search(fn ($b) => $b->id === $lowRated->id);
        });
    }

    public function test_books_without_reviews_are_excluded(): void
    {
        $reviewedBook = Book::factory()->create();
        Review::factory()->for($reviewedBook)->create();

        $noReviewBook = Book::factory()->create();

        $response = $this->get(route('ranking.index'));

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($reviewedBook, $noReviewBook) {
            return $rankedBooks->contains('id', $reviewedBook->id)
                && ! $rankedBooks->contains('id', $noReviewBook->id);
        });
    }

    public function test_ranking_is_limited_to_top_10(): void
    {
        Book::factory()->count(11)->create()->each(function ($book) {
            Review::factory()->for($book)->create(['rating' => 5]);
        });

        $response = $this->get(route('ranking.index'));

        $response->assertViewHas('rankedBooks', fn ($rankedBooks) => $rankedBooks->count() === 10);
    }

    public function test_tie_breaks_by_title_ascending(): void
    {
        $bookB = Book::factory()->create(['title' => 'Bタイトル']);
        Review::factory()->for($bookB)->create(['rating' => 4]);

        $bookA = Book::factory()->create(['title' => 'Aタイトル']);
        Review::factory()->for($bookA)->create(['rating' => 4]);

        $response = $this->get(route('ranking.index'));

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($bookA, $bookB) {
            return $rankedBooks->search(fn ($b) => $b->id === $bookA->id)
                 < $rankedBooks->search(fn ($b) => $b->id === $bookB->id);
        });
    }
}