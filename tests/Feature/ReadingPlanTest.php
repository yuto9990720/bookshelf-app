<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_reading_plans(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_a_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_user_cannot_create_duplicate_in_progress_plan_for_same_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->for($user)->for($book)->create(['status' => ReadingPlanStatus::InProgress]);

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addDays(14)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('book_id');
    }

    public function test_user_can_create_plan_for_book_already_completed(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->for($user)->for($book)->create(['status' => ReadingPlanStatus::Completed]);

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_owner_can_update_target_date(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('reading-plans.update', $plan), [
            'target_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
    }

    public function test_completed_plan_cannot_be_edited(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->create(['status' => ReadingPlanStatus::Completed]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $plan));

        $response->assertForbidden();
    }

    public function test_non_owner_cannot_edit_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->for($owner)->create();

        $response = $this->actingAs($otherUser)->get(route('reading-plans.edit', $plan));

        $response->assertForbidden();
    }

    public function test_owner_can_complete_a_plan(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $plan));

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);
    }

    public function test_owner_can_delete_a_plan(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $plan));

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseMissing('reading_plans', ['id' => $plan->id]);
    }

    public function test_index_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        ReadingPlan::factory()->for($user)->create(['status' => ReadingPlanStatus::InProgress]);
        ReadingPlan::factory()->for($user)->create(['status' => ReadingPlanStatus::Completed]);

        $response = $this->actingAs($user)->get(route('reading-plans.index', ['status' => 'completed']));

        $response->assertViewHas('readingPlans', function ($readingPlans) {
            return $readingPlans->total() === 1
                && $readingPlans->first()->status === ReadingPlanStatus::Completed;
        });
    }
}