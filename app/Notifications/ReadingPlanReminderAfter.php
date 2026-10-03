<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderAfter extends Notification
{
    use Queueable;

    public function __construct(
        public ReadingPlan $readingPlan,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => '読書計画 — 期限超過 3 日経過',
            'body' => "「{$this->readingPlan->book->title}」の期限から3日が経過しました。読了済みなら完了登録、続けるなら期限を変更してください。",
            'reading_plan_id' => $this->readingPlan->id,
            'timing' => 'three_days_after',
        ];
    }
}