@extends('layouts.app')

@section('title', 'Bulk Advance Payments — Mess App')

@section('content')
    <form method="POST" action="{{ route('advance-payments.bulkStore') }}" class="space-y-4">
        @csrf

        <table class="table min-w-full">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody id="paymentRows">
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
                </tr>
            </tbody>
        </table>

        <button type="button" onclick="addPaymentRow()">+ Add Row</button>
        <button type="submit">Save All</button>
    </form>

    <script>
    let paymentIndex = 1;
    function addPaymentRow() {
        let row = `
        <tr>
            <td>
                <select name="meals[${paymentIndex}][user_id]">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="date" name="meals[${paymentIndex}][date]"></td>
            <td><input type="number" step="0.01" min="0" name="meals[${paymentIndex}][amount]" value="0"></td>
        </tr>`;

        document.getElementById('paymentRows').insertAdjacentHTML('beforeend', row);
        paymentIndex++;
    }
    </script>
@endsection
