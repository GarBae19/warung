<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Data Stock Opname</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #aaa;
            padding: 5px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        h3 {
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <h3>Data Stock Opname</h3>
    <table>
        <thead>
            <tr>
                <th style="width:50px">No</th>
                <th>Kode Stock Opname</th>
                <th>Periode</th>
                <th>Approve By</th>
                <th>Approve At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $i => $stock)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{{ $stock->kode_stock_opname }}</td>
                    <td>{{ $stock->periode }}</td>
                    <td>{{ $stock->approve_by ?? '-' }}</td>
                    <td>{{ $stock->approve_at ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
