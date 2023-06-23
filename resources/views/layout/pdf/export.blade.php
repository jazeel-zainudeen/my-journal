<style>
    * {
        font-family: Arial, Helvetica, sans-serif;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 12px
    }

    table td,
    table th {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }

    table tbody tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    table tr:hover {
        background-color: #ddd;
    }

    table th {
        /* padding-top: 10px; */
        /* padding-bottom: 10px; */
        text-align: left;
        background-color: #04AA6D;
        color: white;
    }
</style>

<h2 style="margin: 0; padding: 0; margin-bottom: 2px; text-align: center;">Ticket Report</h2>
<div style="text-align: center; margin-bottom: 20px; color: #7987a1 !important;">
    <small>Generated On: {{ now()->format('F j, Y h:i A') }}</small>
</div>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Care Of</th>
            <th>Name</th>
            <th>Ticket No</th>
            <th>Supplier</th>
            <th>Cost</th>
            <th>Profit</th>
            <th>Total</th>
            <th>Collected</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('F d, Y h:i A') }}</td>
                <td>{{ $item->reference->name }}</td>
                <td>{{ $item->customer_name }}</td>
                <td>{{ $item->ticket_number }}</td>
                <td>{{ $item->supplier->name }}</td>
                <td>{{ 'SAR ' . number_format($item->cost, 2) }}</td>
                <td>{{ 'SAR ' . number_format($item->profit, 2) }}</td>
                <td>{{ 'SAR ' . number_format($item->total, 2) }}</td>
                <td>{{ 'SAR ' . number_format($item->collection_amount, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight: bold;">
            <td colspan="5" style="text-align: right;">Total:</td>
            <td>{{ 'SAR ' . number_format($totals['cost'], 2) }}</td>
            <td>{{ 'SAR ' . number_format($totals['profit'], 2) }}</td>
            <td>{{ 'SAR ' . number_format($totals['total'], 2) }}</td>
            <td>{{ 'SAR ' . number_format($totals['collection_amount'], 2) }}</td>
        </tr>
        <tr style="color: #69758b;">
            <td colspan="8" style="text-align: right;">Pending:</td>
            <td>SAR {{ number_format($totals['total'] - $totals['collection_amount'], 2) }}</td>
        </tr>
    </tfoot>
</table>
