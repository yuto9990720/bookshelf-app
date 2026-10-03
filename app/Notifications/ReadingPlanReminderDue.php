<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderDue extends Notification
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
            'title' => '読書計画 — 本日が期限',
            'body' => "「{$this->readingPlan->book->title}」は本日が期限です。読了済みなら完了登録を、もう少し必要なら期限を変更してください。",
            'reading_plan_id' => $this->readingPlan->id,
            'timing' => 'on_due_date'
        ];
    }
}