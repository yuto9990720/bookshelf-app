<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalReviews = $user->reviews()->count();
        $booksRead = $user->readingPlans()->where('status', 'completed')->count();
        $averageRating = round($user->reviews()->avg('rating') ?? 0, 1);

        $groupedRatings = $user->reviews()->get()->groupBy('rating');
        $ratingDistribution = collect(range(1, 5))
            ->map(fn ($rating) => $groupedRatings->get($rating, collect())->count());

        $topRatedBooks = $user->reviews()
            ->with('book')
            ->where('rating', '>=', 4)
            ->orderByDesc('rating')
            ->take(5)
            ->get()
            ->map(fn ($review) => [
                'id' => $review->book->id,
                'title' => $review->book->title,
                'author' => $review->book->author,
                'rating' => $review->rating,
            ]);

        $genreRatings = Genre::query()
            ->join('book_genre', 'genres.id', '=', 'book_genre.genre_id')
            ->join('books', 'books.id', '=', 'book_genre.book_id')
            ->join('reviews', 'reviews.book_id', '=', 'books.id')
            ->where('reviews.user_id', $user->id)
            ->select('genres.id', 'genres.name')
            ->selectRaw('COUNT(reviews.id) as count, AVG(reviews.rating) as average_rating')
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->take(5)
            ->get()
            ->map(fn ($genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
                'count' => $genre->count,
                'average_rating' => round($genre->average_rating, 1),
            ]);

        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $booksRead,
                'average_rating' => $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}