<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Http\Requests\ReadingPlanRequest;
use Illuminate\Support\Facades\Auth;

class ReadingPlanController extends Controller
{
    public function index()
    {
        $readingPlans = Auth::user()->readingPlans()->with('book')->paginate(10);

        return view('reading_plans.index', compact('readingPlans'));
    }

    public function create()
    {
        $books = Book::all();

        return view('reading_plans.create', compact('books'));
    }

    public function store(ReadingPlanRequest $request)
    {
        Auth::user()->readingPlans()->create($request->validated());

        return redirect()->route('reading-plans.index')->with('success', '読書計画を登録しました。');
    }

    public function edit(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $books = Book::all();

        return view('reading_plans.edit', compact('readingPlan', 'books'));
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