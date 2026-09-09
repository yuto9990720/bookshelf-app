<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function toggle(Review $review)
    {
        $this->authorize('like', $review);

        Auth::user()->likedReviews()->toggle($review);

        return back();
    }
}