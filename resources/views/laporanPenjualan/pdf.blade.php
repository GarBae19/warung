<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        h3 {
            margin-bottom: 2px;
        }

        .periode {
            margin-bottom: 10px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 4px 6px;
        }

        th {
            background: #eee;
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        tfoot th {
            background: #f5f5f5;
        }
    </style>
</head>

<body>
    <h3>Laporan Penjualan</h3>
    <div class="periode">
        Periode:
        {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Hari ini' }}
        s/d
        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode Transaksi</th>
                <th>Tanggal</th>
                <th>Item Nama</th>
                <th>Satuan</th>
                <th>Qty</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
                <th>Total Beli</th>
                <th>Total Jual</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row->kode_pos ?? '-' }}</td>
                    <td class="text-center">
                        {{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $row->nama_barang ?? '-' }}</td>
                    <td>{{ $row->nama_satuan ?? '-' }}</td>
                    <td class="text-center">{{ $row->qty }}</td>
                    <td class="text-end">{{ number_format($row->harga_beli, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($row->harga_jual, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($row->harga_beli * $row->qty, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($row->harga_jual * $row->qty, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" class="text-end">GRAND TOTAL</th>
                <th class="text-end">{{ number_format($grandTotalBeli, 0, ',', '.') }}</th>
                <th class="text-end">{{ number_format($grandTotalJual, 0, ',', '.') }}</th>
            </tr>
            <tr>
                <th colspan="9" class="text-end">PROFIT</th>
                <th class="text-end">{{ number_format($grandProfit, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
