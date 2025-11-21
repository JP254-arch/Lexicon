<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
        }

        .info {
            margin: 10px 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 14px;
        }

        th {
            background: #949898;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title" style="color: indigo">Lexicon Receipt</div>
        <div>Official Payment Receipt</div>
    </div>

    <div class="info">
        <strong>Receipt No:</strong> {{ $payment->id }} <br>
        <strong>Date:</strong> {{ $payment->created_at->format('Y-m-d') }} <br>
        <strong>Paid by:</strong> {{ $payment->user->name }} <br>
        <strong>Payment Method:</strong> {{ $payment->method }} <br>
        <strong>Reference:</strong> {{ $payment->reference }}
    </div>

    <h3>Items</h3>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Price (Ksh)</th>
                <th>Total (Ksh)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Borrow Fee</td>
                <td>1</td>
                <td>{{ $payment->borrow_fee }}</td>
                <td>{{ $payment->borrow_fee }}</td>
            </tr>

            @if ($payment->fine_days > 0)
                <tr>
                    <td>Fine – {{ $payment->fine_days }} days late</td>
                    <td>{{ $payment->fine_days }}</td>
                    <td>{{ $payment->fine_per_day }}</td>
                    <td>{{ $payment->fine_total }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <h2 style="margin-top: 25px;">
        TOTAL PAID: {{ $payment->total }} Ksh
    </h2>

    <div class="info" style="margin-top: 30px;">
        <strong>Book:</strong> {{ $payment->loan->book->title }} <br>
        <strong>Loan ID:</strong> {{ $payment->loan->id }} <br>
        <strong>Status:</strong> PAID
    </div>

</body>

</html>
