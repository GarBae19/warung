<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Data Master Satuan</title>
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
    <h3>Data Master Satuan</h3>
    <table>
        <thead>
            <tr>
                <th style="width:50px">No</th>
                <th>Kode Satuan</th>
                <th>Nama Satuan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($satuans as $i => $satuan)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{{ $satuan->kode_satuan }}</td>
                    <td>{{ $satuan->nama_satuan }}</td>
                    <td>{{ $satuan->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
