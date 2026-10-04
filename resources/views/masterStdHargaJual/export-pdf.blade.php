<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Data Std Harga Jual</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 4px 6px;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h3 class="text-center">Data Std Harga Jual</h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Std Harga Jual</th>
                <th>Item Kode</th>
                <th>Item Nama</th>
                <th>Cabang</th>
                <th>Harga Jual</th>
                <th>Harga Beli</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $row)
                <tr>
                    @if ($row->show_header)
                        <td class="text-center" rowspan="{{ $row->rowspan }}">{{ $row->no }}</td>
                        <td rowspan="{{ $row->rowspan }}">{{ $row->kode_std_harga_jual }}</td>
                        <td rowspan="{{ $row->rowspan }}">{{ $row->item_kode }}</td>
                        <td rowspan="{{ $row->rowspan }}">{{ $row->item_nama }}</td>
                    @endif
                    <td>{{ $row->nama_cabang }}</td>
                    <td class="text-right">{{ number_format($row->harga_jual, 0, ',', '.') }}</td>
                    <td class="text-right">
                        {{ $row->harga_beli !== null ? number_format($row->harga_beli, 0, ',', '.') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
