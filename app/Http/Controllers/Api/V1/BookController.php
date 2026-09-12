<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request)
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

        $perPage = min($request->input('per_page', 20), 100);

        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    public function show(Book $book)
    {
        $book->loadCount('reviews')->loadAvg('reviews', 'rating')->load('genres');

        return new BookResource($book);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'digits:13', 'unique:books,isbn'],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:300'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $book = Book::create(collect($validated)->except('genres')->toArray());
        $book->genres()->sync($validated['genres']);

        return new BookResource($book->load('genres'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'digits:13', Rule::unique('books', 'isbn')->ignore($book->id)],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:300'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
        ]);

        $book->update(collect($validated)->except('genres')->toArray());
        $book->genres()->sync($validated['genres']);

        return new BookResource($book->load('genres'));
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(null, 204);
    }
}