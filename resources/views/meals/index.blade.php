@extends('layouts.app')

@section('title', 'Meals — Mess App')

@section('content')
    <form action="{{ route('meals.index') }}" method="get" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04] sm:p-5">
        <div class="min-w-0 flex-1 sm:max-w-xs">
            <label for="meals-month" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Month</label>
            <input type="month" name="month" id="meals-month" value="{{ request('month', date('Y-m')) }}"
                   class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm transition hover:border-slate-300 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
        </div>
        <button type="submit" class="btn-filter">
            <svg class="btn-filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Filter
        </button>
    </form>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Meals</h1>
            <p class="mt-1 text-sm text-slate-500">Breakfast, lunch, dinner, and guest meal entries for <span class="font-medium text-slate-700">{{ $monthLabel }}</span>.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('meals.bulk.form') }}" class="btn btn-primary">Bulk Add Meals</a>
            <a href="{{ route('meals.create') }}" class="btn-cta">
                Add meal
            </a>
        </div>
    </div>

    @if ($meals->isEmpty())
        <div class="empty-panel">
            No meals for {{ $monthLabel }}. <a href="{{ route('meals.create') }}" class="link-inline">Add one</a>.
        </div>
    @else
        <form method="POST" action="{{ route('meals.bulk.delete') }}">
            @csrf
        <div class="table-surface">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-200 bg-gray-100">
                            <th><input type="checkbox" id="selectAll"></th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-xs font-semibold uppercase tracking-wider text-slate-500">User</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Breakfast</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Lunch</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Dinner</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Guest Meals</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($meals as $meal)
                            <tr class="table-row-interactive">
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $meal->id }}">
                                </td>
                                <td class="px-6 py-5 text-slate-600">{{ $meal->date->format('M j, Y') }}</td>
                                <td class="px-6 py-5 font-medium text-slate-900">{{ $meal->user->name }}</td>
                                <td class="px-6 py-5 text-slate-700">{{ $meal->breakfast }}</td>
                                <td class="px-6 py-5">
                                    @if ($meal->lunch)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Yes</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-slate-600">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    @if ($meal->dinner)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Yes</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-slate-600">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-slate-700">{{ $meal->guest_meals }}</td>
                                <td class="whitespace-nowrap px-6 py-5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-3">
                                        <a href="{{ route('meals.edit', $meal) }}" class="action-link">Edit</a>
                                        <form action="{{ route('meals.destroy', $meal) }}" method="post" class="inline"
                                              onsubmit="return confirm('Delete this meal entry? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-link-danger cursor-pointer">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <button type="submit" class="mt-3 rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/30">
            Delete Selected
        </button>
        </form>
    @endif
@endsection

<script>
const selectAll = document.getElementById('selectAll');
if (selectAll) {
selectAll.onclick = function() {
    let checkboxes = document.querySelectorAll('input[name="ids[]"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
};
}
</script>
