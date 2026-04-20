<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesControllerErrors;
use App\Models\AdvancePayment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdvancePaymentController extends Controller
{
    use HandlesControllerErrors;

    public function index(): View
    {
        try {
            $payments = AdvancePayment::query()
                ->with('user')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();

            return view('advance-payments.index', compact('payments'));
        } catch (Throwable $e) {
            $this->logControllerError($e, 'advance_payments.index_failed');

            return view('advance-payments.index', ['payments' => collect()])->with('error', 'Something went wrong, please try again');
        }
    }

    public function create(): View
    {
        $users = User::query()->orderBy('name')->get();
        if ($users->isEmpty()) {
            $this->logMissingData('advance_payments.create_missing_users');
        }

        return view('advance-payments.create', ['users' => $users]);
    }

    public function show(AdvancePayment $advancePayment): JsonResponse
    {
        $advancePayment->load('user');

        return response()->json($advancePayment);
    }

    public function edit(AdvancePayment $advancePayment): View
    {
        return view('advance-payments.edit', [
            'payment' => $advancePayment->load('user'),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
                'date' => 'required|date_format:Y-m-d',
            ]);

            $payment = AdvancePayment::create([
                'user_id' => $request->user_id,
                'amount' => Money::centsToString(Money::inputToCents((string) $request->input('amount'))),
                'date' => $request->date,
            ]);
            $payment->load('user');

            if ($request->wantsJson()) {
                return response()->json($payment, 201);
            }

            return redirect()->route('advance-payments.index')->with('success', 'Advance payment added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'advance_payments.insert_failed', [
                'user_id' => $request->input('user_id'),
                'date' => $request->input('date'),
            ]);

            return $this->errorResponse($request, 'advance-payments.index');
        }
    }

    public function update(Request $request, AdvancePayment $advancePayment): JsonResponse|RedirectResponse
    {
        try {
            $request->validate([
                'user_id' => ['required', 'integer', 'exists:users,id'],
                'amount' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
                'date' => ['required', 'date_format:Y-m-d'],
            ]);

            $advancePayment->update([
                'user_id' => $request->user_id,
                'amount' => Money::centsToString(Money::inputToCents((string) $request->input('amount'))),
                'date' => $request->date,
            ]);
            $advancePayment->load('user');

            if ($request->wantsJson()) {
                return response()->json($advancePayment->fresh(['user']));
            }

            return redirect()->route('advance-payments.index')->with('success', 'Advance payment added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'advance_payments.update_failed', [
                'id' => $advancePayment->id,
            ]);

            return $this->errorResponse($request, 'advance-payments.index');
        }
    }

    public function bulkForm(): View
    {
        $users = User::query()->orderBy('name')->get();

        return view('advance-payments.bulk', ['users' => $users]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->meals ?? [];

        foreach ($data as $payment) {
            AdvancePayment::create([
                'user_id' => $payment['user_id'],
                'amount' => Money::centsToString(Money::inputToCents((string) ($payment['amount'] ?? 0))),
                'date' => $payment['date'],
            ]);
        }

        return redirect()->route('advance-payments.index')->with('success', 'Bulk advance payments added successfully!');
    }

    public function bulkDelete(Request $request)
    {
        \App\Models\AdvancePayment::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Deleted successfully!');
    }

    public function destroy(Request $request, AdvancePayment $advancePayment): JsonResponse|RedirectResponse
    {
        try {
            $advancePayment->delete();

            if ($request->wantsJson()) {
                return response()->json(null, 204);
            }

            return redirect()->route('advance-payments.index')->with('success', 'Advance payment added successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'advance_payments.delete_failed', [
                'id' => $advancePayment->id,
            ]);

            return $this->errorResponse($request, 'advance-payments.index');
        }
    }
}
