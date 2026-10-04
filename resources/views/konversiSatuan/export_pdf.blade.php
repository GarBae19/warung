<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Data Konversi Satuan</title>
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
    <h3>Data Konversi Satuan</h3>
    <table>
        <thead>
            <tr>
                <th style="width:50px">No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Nilai Konversi</th>
                <th>Satuan Asal</th>
                <th>Satuan Konversi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($konversies as $i => $konversi)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{{ $konversi->barang->kode_barang ?? '-' }}</td>
                    <td>{{ $konversi->barang->nama_barang ?? '-' }}</td>
                    <td style="text-align:center">{{ $konversi->nilai_konversi }}</td>
                    <td>{{ $konversi->satuanAsal->nama_satuan ?? '-' }}</td>
                    <td>{{ $konversi->satuanKonversi->nama_satuan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
