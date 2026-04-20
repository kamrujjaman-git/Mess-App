<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesControllerErrors;
use App\Models\MarketExpense;
use App\Models\User;
use App\Support\Money;
use App\Support\Month;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class MarketExpenseController extends Controller
{
    use HandlesControllerErrors;

    public function create(): View
    {
        $users = User::query()->orderBy('name')->get();
        if ($users->isEmpty()) {
            $this->logMissingData('market_expenses.create_missing_users');
        }

        return view('market-expenses.create', ['users' => $users]);
    }

    public function edit(MarketExpense $marketExpense): View
    {
        return view('market-expenses.edit', [
            'expense' => $marketExpense->load('user'),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        try {
            [$month, $year, $monthNum] = Month::normalize($request->input('month'));

            $expenses = MarketExpense::query()
                ->with('user')
                ->forMonth($year, $monthNum)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();

            if ($request->wantsJson()) {
                return response()->json($expenses);
            }

            return view('expenses.index', [
                'expenses' => $expenses,
                'month' => $month,
                'monthLabel' => date('F Y', strtotime($month.'-01')),
            ]);
        } catch (Throwable $e) {
            $this->logControllerError($e, 'market_expenses.index_failed', [
                'month' => $request->input('month'),
            ]);

            return $this->errorResponse($request, 'expenses.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
                'date' => ['required', 'date_format:Y-m-d'],
                'note' => ['nullable', 'string'],
            ]);

            $expense = MarketExpense::create([
                'user_id' => $request->user_id,
                'amount' => Money::centsToString(Money::inputToCents((string) $request->input('amount'))),
                'date' => $request->date,
                'note' => $request->note,
            ]);
            $expense->load('user');

            if ($request->wantsJson()) {
                return response()->json($expense, 201);
            }

            return redirect()->route('expenses.index')->with('success', 'Expense added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'market_expenses.insert_failed', [
                'user_id' => $request->input('user_id'),
                'date' => $request->input('date'),
            ]);

            return $this->errorResponse($request, 'expenses.index');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MarketExpense $marketExpense): JsonResponse
    {
        $marketExpense->load('user');

        return response()->json($marketExpense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MarketExpense $marketExpense): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
                'date' => ['required', 'date_format:Y-m-d'],
                'note' => ['nullable', 'string'],
            ]);

            $marketExpense->update([
                'user_id' => $request->user_id,
                'amount' => Money::centsToString(Money::inputToCents((string) $request->input('amount'))),
                'date' => $request->date,
                'note' => $request->note,
            ]);
            $marketExpense->load('user');

            if ($request->wantsJson()) {
                return response()->json($marketExpense->fresh(['user']));
            }

            return redirect()->route('expenses.index')->with('success', 'Expense added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'market_expenses.update_failed', [
                'id' => $marketExpense->id,
            ]);

            return $this->errorResponse($request, 'expenses.index');
        }
    }

    public function bulkForm(): View
    {
        $users = User::query()->orderBy('name')->get();

        return view('expenses.bulk', ['users' => $users]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->meals ?? [];

        foreach ($data as $expense) {
            MarketExpense::create([
                'user_id' => $expense['user_id'],
                'amount' => Money::centsToString(Money::inputToCents((string) ($expense['amount'] ?? 0))),
                'date' => $expense['date'],
                'note' => $expense['note'] ?? null,
            ]);
        }

        return redirect()->route('expenses.index')->with('success', 'Bulk expenses added successfully!');
    }

    public function bulkDelete(Request $request)
    {
        \App\Models\MarketExpense::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Deleted successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, MarketExpense $marketExpense): JsonResponse|RedirectResponse
    {
        try {
            $marketExpense->delete();

            if ($request->wantsJson()) {
                return response()->json(null, 204);
            }

            return redirect()->route('expenses.index')->with('success', 'Expense added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'market_expenses.delete_failed', [
                'id' => $marketExpense->id,
            ]);

            return $this->errorResponse($request, 'expenses.index');
        }
    }
}
