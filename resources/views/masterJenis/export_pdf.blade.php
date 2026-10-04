<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Data Master Jenis Barang</title>
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
    <h3>Data Master Jenis Barang</h3>
    <table>
        <thead>
            <tr>
                <th style="width:60px">No</th>
                <th>Kode Jenis</th>
                <th>Nama Jenis</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jenises as $i => $jenis)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{{ $jenis->kode_jenis }}</td>
                    <td>{{ $jenis->nama_jenis }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
