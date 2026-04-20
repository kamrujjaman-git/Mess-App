<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesControllerErrors;
use App\Models\AdvancePayment;
use App\Models\MarketExpense;
use App\Models\Meal;
use App\Models\User;
use App\Support\Money;
use App\Support\Month;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    use HandlesControllerErrors;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            [$month, $year, $monthNum] = Month::normalize($request->input('month'));

            $totalExpenseCents = MarketExpense::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->get(['amount'])
                ->reduce(
                    fn (int $carry, MarketExpense $expense): int => $carry + Money::inputToCents((string) $expense->amount),
                    0
                );

            $totalMeals = (int) (Meal::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->selectRaw(
                    'SUM(COALESCE(breakfast, 0) + CASE WHEN lunch THEN 1 ELSE 0 END + CASE WHEN dinner THEN 1 ELSE 0 END + COALESCE(guest_meals, 0)) as meal_units'
                )
                ->value('meal_units') ?? 0);

            $totalAdvanceCents = AdvancePayment::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->get(['amount'])
                ->reduce(
                    fn (int $carry, AdvancePayment $payment): int => $carry + Money::inputToCents((string) $payment->amount),
                    0
                );

            $costPerMealCents = Money::roundDivToCents($totalExpenseCents, $totalMeals);

            $mealUnitsByUser = Meal::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->groupBy('user_id')
                ->selectRaw(
                    'user_id, SUM(COALESCE(breakfast, 0) + CASE WHEN lunch THEN 1 ELSE 0 END + CASE WHEN dinner THEN 1 ELSE 0 END + COALESCE(guest_meals, 0)) as units'
                )
                ->pluck('units', 'user_id');

            $advanceByUser = AdvancePayment::query()
                ->whereYear('date', $year)
                ->whereMonth('date', $monthNum)
                ->get(['user_id', 'amount'])
                ->groupBy('user_id')
                ->map(
                    fn (Collection $rows): int => $rows->reduce(
                        fn (int $carry, AdvancePayment $payment): int => $carry + Money::inputToCents((string) $payment->amount),
                        0
                    )
                );

            $users = User::query()->orderBy('name')->get();
            if ($users->isEmpty()) {
                $this->logMissingData('dashboard.missing_users', ['month' => $month]);
            }

            $perUserBalances = $users->map(function (User $user) use ($costPerMealCents, $mealUnitsByUser, $advanceByUser) {
                    $meals = (int) ($mealUnitsByUser->get($user->id, 0));
                    $advancePaidCents = (int) ($advanceByUser->get($user->id, 0));
                    $costCents = $meals * $costPerMealCents;
                    $balanceCents = $advancePaidCents - $costCents;

                    return [
                        'name' => $user->name,
                        'meals' => $meals,
                        'advancePaid' => Money::centsToString($advancePaidCents),
                        'cost' => Money::centsToString($costCents),
                        'balance' => Money::centsToString($balanceCents),
                        'balanceCents' => $balanceCents,
                    ];
                });

            return view('dashboard', [
                'totalExpense' => Money::centsToString($totalExpenseCents),
                'totalMeals' => $totalMeals,
                'totalAdvance' => Money::centsToString($totalAdvanceCents),
                'costPerMeal' => Money::centsToString($costPerMealCents),
                'perUserBalances' => $perUserBalances,
                'month' => $month,
                'monthLabel' => date('F Y', strtotime($month.'-01')),
            ]);
        } catch (Throwable $e) {
            $this->logControllerError($e, 'dashboard.calculation_failed', [
                'month' => $request->input('month'),
            ]);

            return $this->errorResponse($request, 'dashboard');
        }
    }
}
