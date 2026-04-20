@extends('layouts.app')

@section('title', 'Add house rent — Mess App')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add house rent</h1>
        <p class="mt-1 text-sm text-slate-500">Record rent for a member. Month uses <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-slate-700">Y-m</code> format.</p>

        @if ($users->isEmpty())
            <div class="form-callout mt-6">
                Add at least one user before recording house rent.
                <a href="{{ route('users.create') }}" class="font-semibold text-amber-900 underline decoration-amber-900/30 underline-offset-2 transition-colors duration-200 hover:text-amber-950">Create a user</a>
            </div>
        @endif

        <form action="{{ route('house-rents.store') }}" method="post" class="form-card" novalidate>
            @csrf

            <div>
                <label for="house-rent-user-id" class="form-label">User</label>
                <select name="user_id" id="house-rent-user-id" required @disabled($users->isEmpty())
                        class="form-control @error('user_id') form-control-invalid @enderror">
                    <option value="" disabled @selected(! old('user_id'))>Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) old('user_id') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="house-rent-amount" class="form-label">Amount</label>
                <input type="text" name="amount" id="house-rent-amount" value="{{ old('amount') }}"
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
                       value="{{ old('month', request('month')) }}" required
                       class="form-control @error('month') form-control-invalid @enderror">
                @error('month')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="house-rent-note" class="form-label">Note <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="note" id="house-rent-note" rows="3" placeholder="e.g. partial payment, reference…"
                          class="form-control @error('note') form-control-invalid @enderror">{{ old('note') }}</textarea>
                @error('note')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" @disabled($users->isEmpty())
                        class="form-btn-primary">
                    Submit
                </button>
                <a href="{{ route('house-rents.index') }}"
                   class="form-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
