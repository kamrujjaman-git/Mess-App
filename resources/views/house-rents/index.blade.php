@extends('layouts.app')

@section('title', 'House rent — Mess App')

@section('content')
    <form method="get" action="{{ route('house-rents.index') }}"
          class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm ring-1 ring-slate-900/[0.04] sm:p-5">
        <div class="min-w-0 flex-1 sm:max-w-xs">
            <label for="house-rents-month-filter" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Filter by month</label>
            <input type="month" name="month" id="house-rents-month-filter" value="{{ $month }}"
                   class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm transition hover:border-slate-300 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
        </div>
        <button type="submit" class="btn-filter">
            <svg class="btn-filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            Apply
        </button>
    </form>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">House rent</h1>
            <p class="mt-1 text-sm text-slate-500">
                Showing <span class="font-medium text-slate-700 tabular-nums">{{ $month }}</span>
                <span class="text-slate-400">·</span>
                <span class="text-slate-600">{{ $monthLabel }}</span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('house-rents.bulkForm') }}" class="btn btn-primary">Bulk Add Rent</a>
            <a href="{{ route('house-rents.create', ['month' => $month]) }}"
               class="btn-cta">
                Add rent
            </a>
        </div>
    </div>

    @if ($houseRents->isEmpty())
        <div class="empty-panel">
            No house rent for {{ $monthLabel }}.
            <a href="{{ route('house-rents.create', ['month' => $month]) }}" class="link-inline">Add an entry</a>.
        </div>
    @else
        <form method="POST" action="{{ route('house-rents.bulk.delete') }}">
            @csrf
            @method('DELETE')
        <div class="table-surface">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-200 bg-gray-100">
                            <th><input type="checkbox" id="selectAll"></th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">User</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Amount</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Month</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Note</th>
                            <th scope="col" class="whitespace-nowrap px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($houseRents as $rent)
                            <tr class="table-row-interactive">
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $rent->id }}">
                                </td>
                                <th scope="row" class="px-6 py-4 font-medium text-slate-900">{{ $rent->user->name }}</th>
                                <td class="px-6 py-4 text-right tabular-nums text-slate-900">{{ number_format($rent->amount, 2) }}</td>
                                <td class="px-6 py-4 tabular-nums text-slate-700">{{ $rent->month }}</td>
                                <td class="max-w-md px-6 py-4 text-slate-600">{{ $rent->note ? \Illuminate\Support\Str::limit($rent->note, 120) : '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-3">
                                        <a href="{{ route('house-rents.edit', $rent) }}" class="action-link">
                                            Edit
                                        </a>
                                        <form action="{{ route('house-rents.destroy', $rent) }}" method="post" class="inline"
                                              onsubmit="return confirm('Delete this rent entry? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-link-danger cursor-pointer">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 font-bold">
                            <td class="px-6 py-4 text-slate-800" colspan="1">Total</td>
                            <td class="px-6 py-4 text-right tabular-nums text-slate-900">
                                Total: {{ number_format($totalRent, 2) }}
                            </td>
                            <td class="px-6 py-4" colspan="4"></td>
                        </tr>
                    </tfoot>
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
