<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Review::all() as $review) {
            // このレビューの投稿者“以外”のユーザーだけを対象にする（Q22：自己いいね禁止）
            $candidateUserIds = User::where('id', '!=', $review->user_id)->pluck('id');

            $count = rand(0, 3);
            $likerIds = $candidateUserIds->random(min($count, $candidateUserIds->count()));

            $review->likedByUsers()->syncWithoutDetaching($likerIds);
        }
    }
}