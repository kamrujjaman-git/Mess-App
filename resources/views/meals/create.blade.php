@extends('layouts.app')

@section('title', 'Add Meal — Mess App')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add meal</h1>
        <p class="mt-1 text-sm text-slate-500">Log meal quantities for a member.</p>

        @if ($users->isEmpty())
            <div class="form-callout">
                Add at least one user before logging meals.
                <a href="{{ route('users.create') }}" class="font-semibold text-amber-900 underline decoration-amber-900/30 underline-offset-2 transition-colors duration-200 hover:text-amber-950">Create a user</a>
            </div>
        @endif

        <form action="{{ route('meals.store') }}" method="post" class="form-card" novalidate>
            @csrf
            <div>
                <label for="user_id" class="form-label">User</label>
                <select name="user_id" id="user_id" required @disabled($users->isEmpty())
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
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" id="date" value="{{ old('date') }}" required
                       class="form-control @error('date') form-control-invalid @enderror">
                @error('date')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="breakfast" class="form-label">Breakfast</label>
                <input type="number" name="breakfast" id="breakfast" value="{{ old('breakfast', 0) }}" min="0"
                       class="form-control @error('breakfast') form-control-invalid @enderror">
                @error('breakfast')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="guest_meals" class="form-label">Guest Meals</label>
                <input type="number" name="guest_meals" id="guest_meals" value="{{ old('guest_meals', 0) }}" min="0"
                       class="form-control @error('guest_meals') form-control-invalid @enderror">
                @error('guest_meals')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="lunch" class="form-label">Lunch</label>
                <input type="number" name="lunch" id="lunch" value="{{ old('lunch', 0) }}" min="0"
                       class="form-control @error('lunch') form-control-invalid @enderror">
                @error('lunch')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="dinner" class="form-label">Dinner</label>
                <input type="number" name="dinner" id="dinner" value="{{ old('dinner', 0) }}" min="0"
                       class="form-control @error('dinner') form-control-invalid @enderror">
                @error('dinner')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <p class="form-hint">At most one meal record per user per day.</p>
            <div class="form-actions">
                <button type="submit" @disabled($users->isEmpty()) class="form-btn-primary">Save meal</button>
                <a href="{{ route('meals.index') }}" class="form-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
