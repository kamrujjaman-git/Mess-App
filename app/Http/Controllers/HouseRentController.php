<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesControllerErrors;
use App\Models\HouseRent;
use App\Models\User;
use App\Support\Money;
use App\Support\Month;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class HouseRentController extends Controller
{
    use HandlesControllerErrors;

    public function create(): View
    {
        return view('house-rents.create', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function index(Request $request): JsonResponse|View
    {
        try {
            [$selectedMonth] = Month::normalize($request->input('month'));
            $start = $selectedMonth.'-01';

            $houseRents = HouseRent::query()
                ->with('user')
                ->where('month', $selectedMonth)
                ->orderByDesc('id')
                ->get();

            $totalRentCents = $houseRents->reduce(
                fn (int $carry, HouseRent $rent): int => $carry + Money::inputToCents((string) $rent->amount),
                0
            );
            $totalRent = Money::centsToString($totalRentCents);

            if ($request->wantsJson()) {
                return response()->json($houseRents);
            }

            return view('house-rents.index', [
                'houseRents' => $houseRents,
                'totalRent' => $totalRent,
                'month' => $selectedMonth,
                'monthLabel' => date('F Y', strtotime($start)),
            ]);
        } catch (Throwable $e) {
            $this->logControllerError($e, 'house_rents.index_failed', [
                'month' => $request->input('month'),
            ]);

            return $this->errorResponse($request, 'house-rents.index');
        }
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
                'month' => 'required|date_format:Y-m',
                'note' => 'nullable|string',
            ]);

            [$normalizedMonth] = Month::normalize($request->input('month'));
            if ($normalizedMonth !== $request->input('month')) {
                $this->logMissingData('house_rents.invalid_month_input', [
                    'month' => $request->input('month'),
                ]);

                return redirect()->back()->withInput()->withErrors(['month' => 'Enter a valid month.']);
            }

            $houseRent = HouseRent::create([
                'user_id' => $request->user_id,
                'amount' => Money::centsToString(Money::inputToCents((string) $request->input('amount'))),
                'month' => $request->month,
                'note' => $request->note,
            ]);
            $houseRent->load('user');

            if ($request->wantsJson()) {
                return response()->json($houseRent, 201);
            }

            return redirect()->route('house-rents.index')
                ->with('success', 'House rent added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'house_rents.insert_failed', [
                'user_id' => $request->input('user_id'),
                'month' => $request->input('month'),
            ]);

            return $this->errorResponse($request, 'house-rents.index');
        }
    }

    public function edit(HouseRent $houseRent): View
    {
        return view('house-rents.edit', [
            'houseRent' => $houseRent->load('user'),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, HouseRent $houseRent): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
                'month' => ['required', 'date_format:Y-m'],
                'note' => ['nullable', 'string'],
            ]);

            [$normalizedMonth] = Month::normalize($request->input('month'));
            if ($normalizedMonth !== $request->input('month')) {
                $this->logMissingData('house_rents.invalid_month_input', [
                    'id' => $houseRent->id,
                    'month' => $request->input('month'),
                ]);

                return redirect()->back()->withInput()->withErrors(['month' => 'Enter a valid month.']);
            }

            $houseRent->update([
                'user_id' => $request->user_id,
                'amount' => Money::centsToString(Money::inputToCents((string) $request->input('amount'))),
                'month' => $request->month,
                'note' => $request->note,
            ]);
            $houseRent->load('user');

            if ($request->wantsJson()) {
                return response()->json($houseRent->fresh(['user']));
            }

            return redirect()->route('house-rents.index')
                ->with('success', 'House rent added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'house_rents.update_failed', [
                'id' => $houseRent->id,
            ]);

            return $this->errorResponse($request, 'house-rents.index');
        }
    }

    public function bulkForm(): View
    {
        $users = User::query()->orderBy('name')->get();

        return view('house-rents.bulk', ['users' => $users]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->meals ?? [];

        foreach ($data as $rent) {
            HouseRent::create([
                'user_id' => $rent['user_id'],
                'amount' => Money::centsToString(Money::inputToCents((string) ($rent['amount'] ?? 0))),
                'month' => $rent['month'],
                'note' => $rent['note'] ?? null,
            ]);
        }

        return redirect()->route('house-rents.index')->with('success', 'Bulk house rent added successfully!');
    }

    public function bulkDelete(Request $request)
    {
        \App\Models\HouseRent::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Deleted successfully!');
    }

    public function destroy(Request $request, HouseRent $houseRent): JsonResponse|RedirectResponse
    {
        try {
            $month = $houseRent->month;
            $houseRent->delete();

            if ($request->wantsJson()) {
                return response()->json(null, 204);
            }

            return redirect()->route('house-rents.index')
                ->with('success', 'House rent added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'house_rents.delete_failed', [
                'id' => $houseRent->id,
            ]);

            return $this->errorResponse($request, 'house-rents.index');
        }
    }
}
