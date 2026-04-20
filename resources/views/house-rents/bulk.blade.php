@extends('layouts.app')

@section('title', 'Bulk House Rent — Mess App')

@section('content')
    <form method="POST" action="{{ route('house-rents.bulkStore') }}" class="space-y-4">
        @csrf

        <table class="table min-w-full">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Month</th>
                    <th>Amount</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody id="rentRows">
                <tr>
                    <td>
                        <select name="meals[0][user_id]">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="month" name="meals[0][month]"></td>
                    <td><input type="number" step="0.01" min="0" name="meals[0][amount]" value="0"></td>
                    <td><input type="text" name="meals[0][note]" value=""></td>
                </tr>
            </tbody>
        </table>

        <button type="button" onclick="addRentRow()">+ Add Row</button>
        <button type="submit">Save All</button>
    </form>

    <script>
    let rentIndex = 1;
    function addRentRow() {
        let row = `
        <tr>
            <td>
                <select name="meals[${rentIndex}][user_id]">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="month" name="meals[${rentIndex}][month]"></td>
            <td><input type="number" step="0.01" min="0" name="meals[${rentIndex}][amount]" value="0"></td>
            <td><input type="text" name="meals[${rentIndex}][note]" value=""></td>
        </tr>`;

        document.getElementById('rentRows').insertAdjacentHTML('beforeend', row);
        rentIndex++;
    }
    </script>
@endsection
