<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesControllerErrors;
use App\Models\Meal;
use App\Models\User;
use App\Support\Month;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class MealController extends Controller
{
    use HandlesControllerErrors;

    public function create(): View
    {
        $users = User::query()->orderBy('name')->get();
        if ($users->isEmpty()) {
            $this->logMissingData('meals.create_missing_users');
        }

        return view('meals.create', ['users' => $users]);
    }

    public function edit(Meal $meal): View
    {
        return view('meals.edit', [
            'meal' => $meal->load('user'),
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

            $meals = Meal::query()
                ->with('user')
                ->forMonth($year, $monthNum)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();

            if ($request->wantsJson()) {
                return response()->json($meals);
            }

            return view('meals.index', [
                'meals' => $meals,
                'month' => $month,
                'monthLabel' => date('F Y', strtotime($month.'-01')),
            ]);
        } catch (Throwable $e) {
            $this->logControllerError($e, 'meals.index_failed', [
                'month' => $request->input('month'),
            ]);

            return $this->errorResponse($request, 'meals.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => [
                    'required',
                    'exists:users,id',
                ],
                'date' => ['required', 'date_format:Y-m-d'],
                'breakfast' => ['nullable', 'integer', 'min:0'],
                'lunch' => ['nullable', 'integer', 'min:0'],
                'dinner' => ['nullable', 'integer', 'min:0'],
                'guest_meals' => ['nullable', 'integer', 'min:0'],
            ]);

            $breakfast = $request->breakfast ?? 0;
            $lunch = $request->lunch ?? 0;
            $dinner = $request->dinner ?? 0;
            $guest = $request->guest_meals ?? 0;

            $total = $breakfast + $lunch + $dinner + $guest;
            if ($total == 0) {
                return back()->withInput()->with('error', 'At least one meal is required');
            }

            $meal = Meal::updateOrCreate(
                ['user_id' => $request->user_id, 'date' => $request->date],
                [
                    'breakfast' => $breakfast,
                    'lunch' => $lunch,
                    'dinner' => $dinner,
                    'guest_meals' => $guest,
                ]
            );
            $meal->load('user');

            if ($request->wantsJson()) {
                return response()->json($meal, 201);
            }

            return redirect()->route('meals.index')->with('success', 'Meal added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'meals.insert_failed', [
                'user_id' => $request->input('user_id'),
                'date' => $request->input('date'),
            ]);

            return $this->errorResponse($request, 'meals.index');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Meal $meal): JsonResponse
    {
        $meal->load('user');

        return response()->json($meal);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Meal $meal): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => [
                    'required',
                    'integer',
                    'exists:users,id',
                    Rule::unique('meals', 'user_id')->where(
                        fn ($query) => $query->whereDate('date', $request->input('date'))
                    )->ignore($meal->id),
                ],
                'date' => ['required', 'date_format:Y-m-d'],
                'breakfast' => ['nullable', 'integer', 'min:0'],
                'lunch' => ['nullable', 'integer', 'min:0'],
                'dinner' => ['nullable', 'integer', 'min:0'],
                'guest_meals' => ['nullable', 'integer', 'min:0'],
            ]);

            $breakfast = $request->breakfast ?? 0;
            $lunch = $request->lunch ?? 0;
            $dinner = $request->dinner ?? 0;
            $guest = $request->guest_meals ?? 0;

            $total = $breakfast + $lunch + $dinner + $guest;
            if ($total == 0) {
                return back()->withInput()->with('error', 'At least one meal is required');
            }

            $meal->update([
                'user_id' => $validated['user_id'],
                'date' => $validated['date'],
                'breakfast' => $breakfast,
                'lunch' => $lunch,
                'dinner' => $dinner,
                'guest_meals' => $guest,
            ]);
            $meal->load('user');

            if ($request->wantsJson()) {
                return response()->json($meal->fresh(['user']));
            }

            return redirect()->route('meals.index')->with('success', 'Meal updated successfully.');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'meals.update_failed', [
                'id' => $meal->id,
            ]);

            return $this->errorResponse($request, 'meals.index');
        }
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'meals' => ['required', 'array', 'min:1'],
            'meals.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'meals.*.date' => ['required', 'date_format:Y-m-d'],
            'meals.*.breakfast' => ['nullable', 'integer', 'min:0'],
            'meals.*.lunch' => ['nullable', 'integer', 'min:0'],
            'meals.*.dinner' => ['nullable', 'integer', 'min:0'],
            'meals.*.guest_meals' => ['nullable', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['meals'] as $meal) {
                Meal::updateOrCreate(
                    ['user_id' => $meal['user_id'], 'date' => $meal['date']],
                    [
                        'breakfast' => (int) ($meal['breakfast'] ?? 0),
                        'lunch' => (int) ($meal['lunch'] ?? 0),
                        'dinner' => (int) ($meal['dinner'] ?? 0),
                        'guest_meals' => (int) ($meal['guest_meals'] ?? 0),
                    ]
                );
            }
        });

        return redirect()->route('meals.index')->with('success', 'Bulk meals saved successfully!');
    }

    public function bulkForm()
    {
        $users = \App\Models\User::all();
        return view('meals.bulk', compact('users'));
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:meals,id'],
        ]);

        $ids = $validated['ids'] ?? [];
        if ($ids === []) {
            return redirect()->route('meals.index')->with('error', 'Please select at least one meal.');
        }

        Meal::whereIn('id', $ids)->delete();

        return redirect()->route('meals.index')->with('success', 'Selected meals deleted!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Meal $meal): JsonResponse|RedirectResponse
    {
        try {
            $meal->delete();

            if ($request->wantsJson()) {
                return response()->json(null, 204);
            }

            return redirect()->route('meals.index')->with('success', 'Meal deleted successfully.');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'meals.delete_failed', [
                'id' => $meal->id,
            ]);

            return $this->errorResponse($request, 'meals.index');
        }
    }
}
