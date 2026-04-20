@extends('layouts.app')

@section('title', 'Edit house rent — Mess App')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit house rent</h1>
        <p class="mt-1 text-sm text-slate-500">Update rent details for this entry.</p>

        <form action="{{ route('house-rents.update', $houseRent) }}" method="post" class="form-card" novalidate>
            @csrf
            @method('PUT')

            <div>
                <label for="house-rent-user-id" class="form-label">User</label>
                <select name="user_id" id="house-rent-user-id" required
                        class="form-control @error('user_id') form-control-invalid @enderror">
                    <option value="" disabled @selected(! old('user_id', $houseRent->user_id))>Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('user_id', $houseRent->user_id) === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="house-rent-amount" class="form-label">Amount</label>
                <input type="text" name="amount" id="house-rent-amount"
                       value="{{ old('amount', $houseRent->amount) }}"
                       required inputmode="decimal" autocomplete="off" placeholder="0.00"
                       pattern="^\d+(\.\d{1,2})?$"
                       class="form-control tabular-nums @error('amount') form-control-invalid @enderror">
                @error('amount')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="house-rent-month" class="form-label">Month</label>
                <input type="month" name="month" id="house-rent-month"
                       value="{{ old('month', $houseRent->month) }}" required
                       class="form-control @error('month') form-control-invalid @enderror">
                @error('month')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="house-rent-note" class="form-label">Note <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="note" id="house-rent-note" rows="3" placeholder="e.g. partial payment, reference…"
                          class="form-control @error('note') form-control-invalid @enderror">{{ old('note', $houseRent->note) }}</textarea>
                @error('note')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="form-btn-primary">
                    Update
                </button>
                <a href="{{ route('house-rents.index', ['month' => old('month', $houseRent->month)]) }}"
                   class="form-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
