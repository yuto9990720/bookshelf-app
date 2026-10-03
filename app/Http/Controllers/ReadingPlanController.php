<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Http\Requests\ReadingPlanRequest;
use Illuminate\Support\Facades\Auth;
use App\Enums\ReadingPlanStatus;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ReadingPlanController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(ReadingPlanStatus::class)],
        ]);

        $query = Auth::user()->readingPlans()->with('book');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $readingPlans = $query->paginate(10)->withQueryString();

        return view('reading-plans.index', [
            'readingPlans' => $readingPlans,
            'currentStatus' => $validated['status'] ?? null,
        ]);
    }

    public function create()
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    public function store(ReadingPlanRequest $request)
    {
        Auth::user()->readingPlans()->create($request->validated());

        return redirect()->route('reading-plans.index')->with('success', '読書計画を登録しました。');
    }

    public function edit(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function update(ReadingPlanRequest $request, ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update($request->validated());

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました。');
    }

    public function complete(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update(['status' => 'completed', 'completed_at' => now()]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を完了にしました。');
    }

    public function destroy(ReadingPlan $readingPlan)
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }
}