<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderBefore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_their_notifications(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->create();
        $user->notify(new ReadingPlanReminderBefore($plan));

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewHas('notifications', fn ($notifications) => $notifications->total() === 1);
    }

    public function test_user_can_mark_a_notification_as_read(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->create();
        $user->notify(new ReadingPlanReminderBefore($plan));
        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id));

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->for($owner)->create();
        $owner->notify(new ReadingPlanReminderBefore($plan));
        $notification = $owner->notifications()->first();

        $response = $this->actingAs($otherUser)->post(route('notifications.read', $notification->id));

        $response->assertNotFound();
    }
}