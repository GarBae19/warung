<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: monospace;
            font-size: 10px;
        }

        table {
            width: 100%;
        }

        td {
            padding: 2px 0;
        }
    </style>
</head>

<body>

    <h3 style="text-align:center">TOKO SENDANG REZEKI</h3>
    <hr>

    <table>
        <tr>
            <td>NO</td>
            <td>:</td>
            <td>{{ $kode }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ date('d-m-Y H:i:s') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ auth()->user()->name }}</td>
        </tr>
    </table>

    <hr>

    <table>
        @foreach ($transaksi as $item)
            <tr>
                <td style="width: 30%">{{ $item->barang->nama_barang . ' (' . $item->satuan->nama_satuan . ')' }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="width: 30%">{{ number_format($item->harga) }}</td>
                <td>X</td>
                <td>{{ $item->qty }}</td>
                <td>=</td>
                <td>{{ number_format($item->harga * $item->qty) }}</td>
                <td></td>

            </tr>
        @endforeach
    </table>

    <hr>
    <p>Total : {{ number_format($total) }}</p>
    <p>Bayar : {{ number_format($bayar) }}</p>
    <p>Kembali : {{ number_format($kembali) }}</p>

</body>

</html>
