<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_books_by_keyword(): void
    {
        Book::factory()->create(['title' => '吾輩は猫である']);
        Book::factory()->create(['title' => '別のタイトル']);

        $response = $this->get(route('books.index', ['keyword' => '猫']));

        $response->assertOk();
        $response->assertViewHas('books', fn ($books) => $books->total() === 1);
    }

    public function test_can_filter_books_by_genre(): void
    {
        $genre = Genre::factory()->create();
        $matchingBook = Book::factory()->create();
        $matchingBook->genres()->attach($genre);
        Book::factory()->create();

        $response = $this->get(route('books.index', ['genre' => $genre->id]));

        $response->assertOk();
        $response->assertViewHas('books', fn ($books) => $books->total() === 1);
    }

    public function test_books_can_be_sorted_by_title(): void
    {
        Book::factory()->create(['title' => 'Bタイトル']);
        Book::factory()->create(['title' => 'Aタイトル']);

        $response = $this->get(route('books.index', ['sort' => 'title']));

        $response->assertViewHas('books', function ($books) {
            return $books->items()[0]->title === 'Aタイトル';
        });
    }

    public function test_books_without_reviews_are_sorted_last_when_sorted_by_rating(): void
    {
        $noReviewBook = Book::factory()->create();
        $reviewedBook = Book::factory()->create();
        Review::factory()->for($reviewedBook)->create(['rating' => 3]);

        $response = $this->get(route('books.index', ['sort' => 'rating']));

        $response->assertViewHas('books', function ($books) use ($reviewedBook, $noReviewBook) {
            $items = $books->items();
            return $items[0]->id === $reviewedBook->id
                && $items[1]->id === $noReviewBook->id;
        });
    }

    public function test_search_conditions_are_preserved_across_pagination(): void
    {
        $genre = Genre::factory()->create();
        Book::factory()->count(15)->create()->each(fn ($book) => $book->genres()->attach($genre));

        $response = $this->get(route('books.index', ['genre' => $genre->id, 'page' => 2]));

        $response->assertOk();
        $response->assertSee('genre=' . $genre->id);
    }
}