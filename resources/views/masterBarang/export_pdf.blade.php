<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Data Master Barang</title>
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
    <h3>Data Master Barang</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jenis Barang</th>
                <th>Brand</th>
                <th>Min Stock</th>
                <th>Satuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barangs as $i => $barang)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{{ $barang->kode_barang }}</td>
                    <td>{{ $barang->nama_barang }}</td>
                    <td>{{ $barang->jenis_barang->nama_jenis ?? '-' }}</td>
                    <td>{{ $barang->brand->nama_brand ?? '-' }}</td>
                    <td style="text-align:center">{{ $barang->min_stock ?? '-' }}</td>
                    <td>{{ $barang->satuan->nama_satuan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
