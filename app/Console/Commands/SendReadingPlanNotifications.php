<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderAfter;
use App\Notifications\ReadingPlanReminderBefore;
use App\Notifications\ReadingPlanReminderDue;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendReadingPlanNotifications extends Command
{
    protected $signature = 'reading-plans:send-notifications';

    protected $description = '読書計画のリマインダー通知を送信し、期限切れの計画をexpiredに変更する';

    public function handle(): void
    {
        $this->sendBeforeReminders();
        $this->sendDueReminders();
        $this->expireOverduePlans();
        $this->sendAfterReminders();

        $this->info('読書計画の通知処理が完了しました。');
    }

    private function sendBeforeReminders(): void
    {
        $targetDate = Carbon::today()->addDays(3);

        ReadingPlan::where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', $targetDate)
            ->whereNull('notified_before_at')
            ->with('user', 'book')
            ->get()
            ->each(function (ReadingPlan $plan) {
                $plan->user->notify(new ReadingPlanReminderBefore($plan));
                $plan->update(['notified_before_at' => now()]);
            });
    }

    private function sendDueReminders(): void
    {
        ReadingPlan::where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', Carbon::today())
            ->whereNull('notified_due_at')
            ->with('user', 'book')
            ->get()
            ->each(function (ReadingPlan $plan) {
                $plan->user->notify(new ReadingPlanReminderDue($plan));
                $plan->update(['notified_due_at' => now()]);
            });
    }

    private function expireOverduePlans(): void
    {
        ReadingPlan::where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', '<', Carbon::today())
            ->update(['status' => ReadingPlanStatus::Expired]);
    }

    private function sendAfterReminders(): void
    {
        $targetDate = Carbon::today()->subDays(3);

        ReadingPlan::where('status', ReadingPlanStatus::Expired)
            ->whereDate('target_date', $targetDate)
            ->whereNull('notified_after_at')
            ->with('user', 'book')
            ->get()
            ->each(function (ReadingPlan $plan) {
                $plan->user->notify(new ReadingPlanReminderAfter($plan));
                $plan->update(['notified_after_at' => now()]);
            });
    }
}