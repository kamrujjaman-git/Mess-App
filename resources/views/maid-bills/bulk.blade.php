@extends('layouts.app')

@section('title', 'Bulk Maid Bills — Mess App')

@section('content')
    <form method="POST" action="{{ route('maid-bills.bulkStore') }}" class="space-y-4">
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
            <tbody id="maidRows">
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

        <button type="button" onclick="addMaidRow()">+ Add Row</button>
        <button type="submit">Save All</button>
    </form>

    <script>
    let maidIndex = 1;
    function addMaidRow() {
        let row = `
        <tr>
            <td>
                <select name="meals[${maidIndex}][user_id]">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="month" name="meals[${maidIndex}][month]"></td>
            <td><input type="number" step="0.01" min="0" name="meals[${maidIndex}][amount]" value="0"></td>
            <td><input type="text" name="meals[${maidIndex}][note]" value=""></td>
        </tr>`;

        document.getElementById('maidRows').insertAdjacentHTML('beforeend', row);
        maidIndex++;
    }
    </script>
@endsection
