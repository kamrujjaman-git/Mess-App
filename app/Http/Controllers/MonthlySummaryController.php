<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesControllerErrors;
use App\Models\AdvancePayment;
use App\Models\HouseRent;
use App\Models\MaidBill;
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

class MonthlySummaryController extends Controller
{
    use HandlesControllerErrors;

    public function index(Request $request): View|RedirectResponse
    {
        try {
            [$month, $year, $monthNum] = Month::normalize($request->input('month'));

            $startDate = $month.'-01';

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

            $costPerMealCents = Money::roundDivToCents($totalExpenseCents, $totalMeals);

            $totalMealsAll = 0;
            $totalAdvanceAllCents = 0;
            $totalMealCostAllCents = 0;
            $totalMealBalanceAllCents = 0;
            $totalRentAllCents = 0;
            $totalMaidAllCents = 0;
            $totalFinalBalanceAllCents = 0;

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

            $rentByUser = HouseRent::query()
                ->where('month', $month)
                ->get(['user_id', 'amount'])
                ->groupBy('user_id')
                ->map(
                    fn (Collection $rows): int => $rows->reduce(
                        fn (int $carry, HouseRent $rent): int => $carry + Money::inputToCents((string) $rent->amount),
                        0
                    )
                );

            $maidByUser = MaidBill::query()
                ->where('month', $month)
                ->get(['user_id', 'amount'])
                ->groupBy('user_id')
                ->map(
                    fn (Collection $rows): int => $rows->reduce(
                        fn (int $carry, MaidBill $bill): int => $carry + Money::inputToCents((string) $bill->amount),
                        0
                    )
                );

            $users = User::query()->orderBy('name')->get();
            if ($users->isEmpty()) {
                $this->logMissingData('monthly_summary.missing_users', ['month' => $month]);
            }

            $usersData = $users
                ->map(function (User $user) use (
                    $costPerMealCents,
                    $mealUnitsByUser,
                    $advanceByUser,
                    $rentByUser,
                    $maidByUser,
                    &$totalMealsAll,
                    &$totalAdvanceAllCents,
                    &$totalMealCostAllCents,
                    &$totalMealBalanceAllCents,
                    &$totalRentAllCents,
                    &$totalMaidAllCents,
                    &$totalFinalBalanceAllCents
                ) {
                    $userMeals = (int) ($mealUnitsByUser->get($user->id, 0));
                    $advancePaidCents = (int) ($advanceByUser->get($user->id, 0));
                    $mealCostCents = $userMeals * $costPerMealCents;
                    $mealBalanceCents = $advancePaidCents - $mealCostCents;
                    $rentCents = (int) ($rentByUser->get($user->id, 0));
                    $maidCents = (int) ($maidByUser->get($user->id, 0));
                    $finalBalanceCents = $mealBalanceCents - $rentCents - $maidCents;

                    $totalMealsAll += $userMeals;
                    $totalAdvanceAllCents += $advancePaidCents;
                    $totalMealCostAllCents += $mealCostCents;
                    $totalMealBalanceAllCents += $mealBalanceCents;
                    $totalRentAllCents += $rentCents;
                    $totalMaidAllCents += $maidCents;
                    $totalFinalBalanceAllCents += $finalBalanceCents;

                    return [
                        'name' => $user->name,
                        'totalMeals' => $userMeals,
                        'advancePaid' => Money::centsToString($advancePaidCents),
                        'mealCost' => Money::centsToString($mealCostCents),
                        'mealBalance' => Money::centsToString($mealBalanceCents),
                        'rent' => Money::centsToString($rentCents),
                        'maid' => Money::centsToString($maidCents),
                        'finalBalance' => Money::centsToString($finalBalanceCents),
                        'mealBalanceCents' => $mealBalanceCents,
                        'finalBalanceCents' => $finalBalanceCents,
                    ];
                });

            return view('monthly-summary.index', [
                'totalExpense' => Money::centsToString($totalExpenseCents),
                'totalMeals' => $totalMeals,
                'costPerMeal' => Money::centsToString($costPerMealCents),
                'usersData' => $usersData,
                'month' => $month,
                'monthLabel' => date('F Y', strtotime($startDate)),
                'totalMealsAll' => $totalMealsAll,
                'totalAdvanceAll' => Money::centsToString($totalAdvanceAllCents),
                'totalMealCostAll' => Money::centsToString($totalMealCostAllCents),
                'totalMealBalanceAll' => Money::centsToString($totalMealBalanceAllCents),
                'totalRentAll' => Money::centsToString($totalRentAllCents),
                'totalMaidAll' => Money::centsToString($totalMaidAllCents),
                'totalFinalBalanceAll' => Money::centsToString($totalFinalBalanceAllCents),
            ]);
        } catch (Throwable $e) {
            $this->logControllerError($e, 'monthly_summary.calculation_failed', [
                'month' => $request->input('month'),
            ]);

            return $this->errorResponse($request, 'monthly-summary.index');
        }
    }
}
