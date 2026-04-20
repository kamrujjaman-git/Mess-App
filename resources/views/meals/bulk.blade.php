@extends('layouts.app')

@section('title', 'Bulk Add Meals — Mess App')

@section('content')
<div class="mx-auto max-w-6xl">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Bulk Add Meals</h1>
    <p class="mt-1 text-sm text-slate-500">Add or update multiple meal entries in one submission.</p>

    <form method="POST" action="{{ route('meals.bulk.store') }}" class="mt-6 space-y-4">
        @csrf

        <div class="table-surface">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-sm text-slate-700" id="bulkTable">
                    <thead>
                        <tr class="border-b border-slate-200 bg-gray-100">
                            <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">User</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Breakfast</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Lunch</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Dinner</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Guest Meals</th>
                            <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Remove</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr>
                            <td class="px-4 py-3">
                                <select name="meals[0][user_id]" class="form-control" required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3"><input type="date" name="meals[0][date]" class="form-control" required></td>
                            <td class="px-4 py-3"><input type="number" name="meals[0][breakfast]" value="0" min="0" class="form-control"></td>
                            <td class="px-4 py-3"><input type="number" name="meals[0][lunch]" value="0" min="0" class="form-control"></td>
                            <td class="px-4 py-3"><input type="number" name="meals[0][dinner]" value="0" min="0" class="form-control"></td>
                            <td class="px-4 py-3"><input type="number" name="meals[0][guest_meals]" value="0" min="0" class="form-control"></td>
                            <td class="px-4 py-3"><button type="button" onclick="removeRow(this)" class="action-link-danger">Remove</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" onclick="addRow()" class="btn btn-primary">Add Row</button>
            <button type="submit" class="form-btn-primary">Save All</button>
            <a href="{{ route('meals.index') }}" class="form-btn-secondary">Cancel</a>
        </div>
    </form>

</div>

<script>
let index = 1;

function addRow() {
    let table = document.querySelector("#bulkTable tbody");
    let row = table.rows[0].cloneNode(true);

    row.querySelectorAll("input, select").forEach(input => {
        let name = input.name.replace(/\d+/, index);
        input.name = name;
        input.value = input.type === 'number' ? 0 : '';
    });

    table.appendChild(row);
    index++;
}

function removeRow(btn) {
    const table = document.querySelector("#bulkTable tbody");
    if (table.rows.length === 1) {
        return;
    }

    btn.closest('tr').remove();
}
</script>
@endsection
