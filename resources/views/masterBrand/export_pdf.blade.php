<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Data Master Brand</title>
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
    <h3>Data Master Brand</h3>
    <table>
        <thead>
            <tr>
                <th style="width:60px">No</th>
                <th>Kode Brand</th>
                <th>Nama Brand</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $i => $brand)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{{ $brand->kode_brand }}</td>
                    <td>{{ $brand->nama_brand }}</td>
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
