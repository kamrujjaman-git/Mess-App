@extends('layouts.app')

@section('title', 'Edit maid bill — Mess App')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit maid bill</h1>
        <p class="mt-1 text-sm text-slate-500">Update maid bill details for this entry.</p>

        <form action="{{ route('maid-bills.update', $maidBill) }}" method="post" class="form-card" novalidate>
            @csrf
            @method('PUT')

            <div>
                <label for="maid-bill-user-id" class="form-label">User</label>
                <select name="user_id" id="maid-bill-user-id" required
                        class="form-control @error('user_id') form-control-invalid @enderror">
                    <option value="" disabled @selected(! old('user_id', $maidBill->user_id))>Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('user_id', $maidBill->user_id) === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="maid-bill-amount" class="form-label">Amount</label>
                <input type="text" name="amount" id="maid-bill-amount"
                       value="{{ old('amount', $maidBill->amount) }}"
                       required inputmode="decimal" autocomplete="off" placeholder="0.00"
                       pattern="^\d+(\.\d{1,2})?$"
                       class="form-control tabular-nums @error('amount') form-control-invalid @enderror">
                @error('amount')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="maid-bill-month" class="form-label">Month</label>
                <input type="month" name="month" id="maid-bill-month"
                       value="{{ old('month', $maidBill->month) }}" required
                       class="form-control @error('month') form-control-invalid @enderror">
                @error('month')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="maid-bill-note" class="form-label">Note <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="note" id="maid-bill-note" rows="3" placeholder="e.g. cleaning days, adjustment…"
                          class="form-control @error('note') form-control-invalid @enderror">{{ old('note', $maidBill->note) }}</textarea>
                @error('note')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="form-btn-primary">
                    Update
                </button>
                <a href="{{ route('maid-bills.index', ['month' => old('month', $maidBill->month)]) }}"
                   class="form-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
