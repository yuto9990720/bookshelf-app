<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderBefore extends Notification
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
            'title' => '読書計画リマインド — 期限まであと 3 日',
            'body' => "「{$this->readingPlan->book->title}」の期限まで残り3日です。引き続き読書を進めましょう。",
            'reading_plan_id' => $this->readingPlan->id,
            'timing' => 'three_days_before',
        ];
    }
}