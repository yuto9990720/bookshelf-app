<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'target_date',
        'status',
        'completed_at',
        'notified_before_at',
        'notified_due_at',
        'notified_after_at',
    ];

    protected $casts = [
        'target_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
        'notified_before_at' => 'datetime',
        'notified_due_at' => 'datetime',
        'notified_after_at' => 'datetime',
        'status' => \App\Enums\ReadingPlanStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
