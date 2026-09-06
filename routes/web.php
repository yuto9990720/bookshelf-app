<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index'])->name('books.index');

Route::middleware('auth')->group(function () {
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
});

Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// TODO: 各機能実装後、正式なControllerに差し替える
Route::get('/ranking', fn () => view('ranking.index'))->name('ranking.index');
Route::get('/favorites', fn () => view('favorites.index'))->name('favorites.index');
Route::get('/genres', fn () => view('genres.index'))->name('genres.index');