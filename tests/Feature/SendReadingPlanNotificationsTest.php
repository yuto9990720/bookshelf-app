<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderAfter;
use App\Notifications\ReadingPlanReminderBefore;
use App\Notifications\ReadingPlanReminderDue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendReadingPlanNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_before_reminder_for_plan_due_in_three_days(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->addDays(3),
        ]);

        $this->artisan('reading-plans:send-notifications');

        Notification::assertSentTo($user, ReadingPlanReminderBefore::class);
        $this->assertNotNull($plan->fresh()->notified_before_at);
    }

    public function test_sends_due_reminder_for_plan_due_today(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today(),
        ]);

        $this->artisan('reading-plans:send-notifications');

        Notification::assertSentTo($user, ReadingPlanReminderDue::class);
        $this->assertNotNull($plan->fresh()->notified_due_at);
    }

    public function test_expires_overdue_in_progress_plans(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->subDays(1),
        ]);

        $this->artisan('reading-plans:send-notifications');

        $this->assertSame(ReadingPlanStatus::Expired, $plan->fresh()->status);
    }

    public function test_sends_after_reminder_three_days_after_expiration(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->for(Book::factory())->create([
            'status' => ReadingPlanStatus::Expired,
            'target_date' => Carbon::today()->subDays(3),
        ]);

        $this->artisan('reading-plans:send-notifications');

        Notification::assertSentTo($user, ReadingPlanReminderAfter::class);
        $this->assertNotNull($plan->fresh()->notified_after_at);
    }

    public function test_does_not_resend_before_reminder_if_already_notified(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        ReadingPlan::factory()->for($user)->for(Book::factory())->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->addDays(3),
            'notified_before_at' => now(),
        ]);

        $this->artisan('reading-plans:send-notifications');

        Notification::assertNothingSent();
    }

    public function test_does_not_notify_plan_outside_target_dates(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        ReadingPlan::factory()->for($user)->for(Book::factory())->create([
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => Carbon::today()->addDays(7),
        ]);

        $this->artisan('reading-plans:send-notifications');

        Notification::assertNothingSent();
    }
}