<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesControllerErrors;
use App\Models\MaidBill;
use App\Models\User;
use App\Support\Money;
use App\Support\Month;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class MaidBillController extends Controller
{
    use HandlesControllerErrors;

    public function create(): View
    {
        return view('maid-bills.create', [
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function index(Request $request): JsonResponse|View
    {
        try {
            [$selectedMonth] = Month::normalize($request->input('month'));
            $start = $selectedMonth.'-01';

            $maidBills = MaidBill::query()
                ->with('user')
                ->where('month', $selectedMonth)
                ->orderByDesc('id')
                ->get();

            $totalMaidCents = $maidBills->reduce(
                fn (int $carry, MaidBill $bill): int => $carry + Money::inputToCents((string) $bill->amount),
                0
            );
            $totalMaid = Money::centsToString($totalMaidCents);

            if ($request->wantsJson()) {
                return response()->json($maidBills);
            }

            return view('maid-bills.index', [
                'maidBills' => $maidBills,
                'totalMaid' => $totalMaid,
                'month' => $selectedMonth,
                'monthLabel' => date('F Y', strtotime($start)),
            ]);
        } catch (Throwable $e) {
            $this->logControllerError($e, 'maid_bills.index_failed', [
                'month' => $request->input('month'),
            ]);

            return $this->errorResponse($request, 'maid-bills.index');
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
                $this->logMissingData('maid_bills.invalid_month_input', [
                    'month' => $request->input('month'),
                ]);

                return redirect()->back()->withInput()->withErrors(['month' => 'Enter a valid month.']);
            }

            $maidBill = MaidBill::create([
                'user_id' => $request->user_id,
                'amount' => Money::centsToString(Money::inputToCents((string) $request->input('amount'))),
                'month' => $request->month,
                'note' => $request->note,
            ]);
            $maidBill->load('user');

            if ($request->wantsJson()) {
                return response()->json($maidBill, 201);
            }

            return redirect()->route('maid-bills.index')
                ->with('success', 'Maid bill updated successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'maid_bills.insert_failed', [
                'user_id' => $request->input('user_id'),
                'month' => $request->input('month'),
            ]);

            return $this->errorResponse($request, 'maid-bills.index');
        }
    }

    public function edit(MaidBill $maidBill): View
    {
        return view('maid-bills.edit', [
            'maidBill' => $maidBill->load('user'),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, MaidBill $maidBill): JsonResponse|RedirectResponse
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
                $this->logMissingData('maid_bills.invalid_month_input', [
                    'id' => $maidBill->id,
                    'month' => $request->input('month'),
                ]);

                return redirect()->back()->withInput()->withErrors(['month' => 'Enter a valid month.']);
            }

            $maidBill->update([
                'user_id' => $request->user_id,
                'amount' => Money::centsToString(Money::inputToCents((string) $request->input('amount'))),
                'month' => $request->month,
                'note' => $request->note,
            ]);
            $maidBill->load('user');

            if ($request->wantsJson()) {
                return response()->json($maidBill->fresh(['user']));
            }

            return redirect()->route('maid-bills.index')
                ->with('success', 'Maid bill updated successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'maid_bills.update_failed', [
                'id' => $maidBill->id,
            ]);

            return $this->errorResponse($request, 'maid-bills.index');
        }
    }

    public function bulkForm(): View
    {
        $users = User::query()->orderBy('name')->get();

        return view('maid-bills.bulk', ['users' => $users]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->meals ?? [];

        foreach ($data as $bill) {
            MaidBill::create([
                'user_id' => $bill['user_id'],
                'amount' => Money::centsToString(Money::inputToCents((string) ($bill['amount'] ?? 0))),
                'month' => $bill['month'],
                'note' => $bill['note'] ?? null,
            ]);
        }

        return redirect()->route('maid-bills.index')->with('success', 'Bulk maid bills added successfully!');
    }

    public function bulkDelete(Request $request)
    {
        \App\Models\MaidBill::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Deleted successfully!');
    }

    public function destroy(Request $request, MaidBill $maidBill): JsonResponse|RedirectResponse
    {
        try {
            $month = $maidBill->month;
            $maidBill->delete();

            if ($request->wantsJson()) {
                return response()->json(null, 204);
            }

            return redirect()->route('maid-bills.index')
                ->with('success', 'Maid bill updated successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'maid_bills.delete_failed', [
                'id' => $maidBill->id,
            ]);

            return $this->errorResponse($request, 'maid-bills.index');
        }
    }
}
