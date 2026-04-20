@extends('layouts.app')

@section('title', 'Expenses — Mess App')

@section('content')
    <form action="{{ route('expenses.index') }}" method="get" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04] sm:p-5">
        <div class="min-w-0 flex-1 sm:max-w-xs">
            <label for="expenses-month" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Month</label>
            <input type="month" name="month" id="expenses-month" value="{{ request('month', date('Y-m')) }}"
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
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Expenses</h1>
            <p class="mt-1 text-sm text-slate-500">Market expense records for <span class="font-medium text-slate-700">{{ $monthLabel }}</span>.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('expenses.bulkForm') }}" class="btn btn-primary">Bulk Add Expenses</a>
            <a href="{{ route('expenses.create') }}" class="btn-cta">
                Add expense
            </a>
        </div>
    </div>

    @if ($expenses->isEmpty())
        <div class="empty-panel">
            No expenses for {{ $monthLabel }}. <a href="{{ route('expenses.create') }}" class="link-inline">Add one</a>.
        </div>
    @else
        <form method="POST" action="{{ route('expenses.bulk.delete') }}">
            @csrf
            @method('DELETE')
        <div class="table-surface">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-200 bg-gray-100">
                            <th><input type="checkbox" id="selectAll"></th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-xs font-semibold uppercase tracking-wider text-slate-500">User</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Amount</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Note</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($expenses as $expense)
                            <tr class="table-row-interactive">
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $expense->id }}">
                                </td>
                                <td class="px-6 py-5 text-slate-600">{{ $expense->date->format('M j, Y') }}</td>
                                <td class="px-6 py-5 font-medium text-slate-900">{{ $expense->user->name }}</td>
                                <td class="px-6 py-5 text-right tabular-nums text-slate-900">{{ number_format((float) $expense->amount, 2) }}</td>
                                <td class="max-w-xs truncate px-6 py-5 text-slate-500">{{ $expense->note ?? '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-3">
                                        <a href="{{ route('expenses.edit', $expense) }}" class="action-link">Edit</a>
                                        <form action="{{ route('expenses.destroy', $expense) }}" method="post" class="inline"
                                              onsubmit="return confirm('Delete this expense? This cannot be undone.');">
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
        <button type="submit" class="btn btn-danger">Delete Selected</button>
        </form>
    @endif
@endsection

<script>
document.getElementById('selectAll').addEventListener('click', function(e) {
    document.querySelectorAll('input[name="ids[]"]').forEach(cb => {
        cb.checked = e.target.checked;
    });
});
</script>
