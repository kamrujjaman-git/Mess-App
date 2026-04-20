@extends('layouts.app')

@section('title', 'Bulk Expenses — Mess App')

@section('content')
    <form method="POST" action="{{ route('expenses.bulkStore') }}" class="space-y-4">
        @csrf

        <table class="table min-w-full">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody id="expenseRows">
                <tr>
                    <td>
                        <select name="meals[0][user_id]">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="date" name="meals[0][date]"></td>
                    <td><input type="number" step="0.01" min="0" name="meals[0][amount]" value="0"></td>
                    <td><input type="text" name="meals[0][note]" value=""></td>
                </tr>
            </tbody>
        </table>

        <button type="button" onclick="addExpenseRow()">+ Add Row</button>
        <button type="submit">Save All</button>
    </form>

    <script>
    let expenseIndex = 1;
    function addExpenseRow() {
        let row = `
        <tr>
            <td>
                <select name="meals[${expenseIndex}][user_id]">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="date" name="meals[${expenseIndex}][date]"></td>
            <td><input type="number" step="0.01" min="0" name="meals[${expenseIndex}][amount]" value="0"></td>
            <td><input type="text" name="meals[${expenseIndex}][note]" value=""></td>
        </tr>`;

        document.getElementById('expenseRows').insertAdjacentHTML('beforeend', row);
        expenseIndex++;
    }
    </script>
@endsection
