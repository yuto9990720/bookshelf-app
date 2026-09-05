<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $bookIds = Book::pluck('id');

        foreach (User::all() as $user) {
            $count = rand(3, 5);
            $favoriteBookIds = $bookIds->random($count);

            $user->favoriteBooks()->syncWithoutDetaching($favoriteBookIds);
        }
    }
}