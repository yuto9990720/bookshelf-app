<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Http\Requests\Api\V1\BookIndexRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\BookRequest;

class BookController extends Controller
{
    public function index(BookIndexRequest $request)
    {
        $query = Book::withCount('reviews')->withAvg('reviews', 'rating')->with('genres');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->input('genre_id'));
            });
        }

       $books = $query->paginate((int) $request->input('per_page', 20));

        return BookResource::collection($books);
    }

    public function show(Book $book)
    {
        $book->loadCount('reviews')->loadAvg('reviews', 'rating')->load('genres', 'reviews.user');

        return new BookResource($book);
    }

    public function store(StoreBookRequest $request)
    {
        

       $book = Book::create($request->safe()->except('genres'));
        $book->genres()->sync($request->validated('genres'));

        return new BookResource($book->load('genres'));
    }

    public function update(BookRequest $request, Book $book)
    {
        
        $book->update($request->safe()->except('genres'));
        $book->genres()->sync($request->validated('genres'));

        return new BookResource($book->load('genres'));
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(null, 204);
    }
}