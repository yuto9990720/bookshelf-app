<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_report(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_summary_counts_only_the_authenticated_users_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $bookA = Book::factory()->create();
        $bookB = Book::factory()->create();
        $otherBook = Book::factory()->create();

        Review::factory()->for($user)->for($bookA)->create(['rating' => 4]);
        Review::factory()->for($user)->for($bookB)->create(['rating' => 2]);
        Review::factory()->for($otherUser)->for($otherBook)->create(['rating' => 5]);

        ReadingPlan::factory()->for($user)->for($bookA)->create(['status' => ReadingPlanStatus::Completed]);
        ReadingPlan::factory()->for($otherUser)->for($otherBook)->create(['status' => ReadingPlanStatus::Completed]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 2
                && $stats['summary']['books_read'] === 1
                && $stats['summary']['average_rating'] === 3.0;
        });
    }

    public function test_rating_distribution_counts_each_star_level(): void
    {
        $user = User::factory()->create();
        $bookA = Book::factory()->create();
        $bookB = Book::factory()->create();
        $bookC = Book::factory()->create();

        Review::factory()->for($user)->for($bookA)->create(['rating' => 5]);
        Review::factory()->for($user)->for($bookB)->create(['rating' => 5]);
        Review::factory()->for($user)->for($bookC)->create(['rating' => 3]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            return $stats['rating_distribution'][4] === 2
                && $stats['rating_distribution'][2] === 1
                && $stats['rating_distribution'][0] === 0;
        });
    }

    public function test_top_rated_books_only_includes_rating_four_or_above(): void
    {
        $user = User::factory()->create();
        $highRated = Book::factory()->create(['title' => '高評価本']);
        $lowRated = Book::factory()->create(['title' => '低評価本']);

        Review::factory()->for($user)->for($highRated)->create(['rating' => 5]);
        Review::factory()->for($user)->for($lowRated)->create(['rating' => 2]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            $titles = collect($stats['top_rated_books'])->pluck('title');

            return $titles->contains('高評価本') && ! $titles->contains('低評価本');
        });
    }

    public function test_top_rated_books_is_limited_to_five(): void
    {
        $user = User::factory()->create();

        Book::factory()->count(6)->create()->each(function ($book) use ($user) {
            Review::factory()->for($user)->for($book)->create(['rating' => 5]);
        });

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', fn ($stats) => count($stats['top_rated_books']) === 5);
    }

    public function test_genre_ratings_aggregates_only_the_authenticated_users_reviews(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $genre = Genre::factory()->create(['name' => 'ビジネス']);
        $bookA = Book::factory()->create();
        $bookB = Book::factory()->create();
        $bookA->genres()->attach($genre);
        $bookB->genres()->attach($genre);

        Review::factory()->for($user)->for($bookA)->create(['rating' => 4]);
        Review::factory()->for($user)->for($bookB)->create(['rating' => 2]);
        Review::factory()->for($otherUser)->for($bookA)->create(['rating' => 5]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertViewHas('stats', function ($stats) {
            $genreRating = collect($stats['genre_ratings'])->firstWhere('name', 'ビジネス');

            return $genreRating !== null
                && $genreRating['count'] === 2
                && $genreRating['average_rating'] === 3.0;
        });
    }
}