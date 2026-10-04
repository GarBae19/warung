@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')

    <style>
        .card-body {
            display: flex;
            flex-direction: column;
        }

        .card-title {
            min-height: 40px;
        }

        .card-text {
            min-height: 20px;
        }

        .card .btn {
            margin-top: auto;
        }

        .text-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                {{-- <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">POS (Point Of Sales)</h3>
                </div> --}}

                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('stockCard') }}" method="GET">
                        <div class="row justify-content-center align-items-center">

                            <div class="col-md-2">
                                <label for="id_barang" class="mb-0">Pilih Barang : </label>
                            </div>
                            {{-- @dd(decrypt(request()->query('id_barang'))); --}}
                            <div class="col-md-3">
                                <select id="id_barang" name="id_barang" class="form-control" style="width:100%; height:38px"
                                    required>
                                    <option value="">Pilih Barang</option>
                                    @foreach ($barang as $barang)
                                        <option
                                            @php $param = request()->query('id_barang');
                                                if (!empty($param)) {
                                                    $idBarang = $param;
                                                    
                                                    $idBarang = decrypt($idBarang) ? decrypt($idBarang) : null; 
                                                }else{
                                                    $idBarang = null;
                                                } @endphp
                                            value="{{ encrypt($barang->id) }}"
                                            {{ $idBarang == $barang->id ? 'selected' : '' }}>
                                            {{ $barang->nama_barang }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label for="id_barang" class="mb-0">Periode : </label>
                            </div>
                            <div class="col-md-2">
                                <input type="month" id="tanggal" name="tanggal" class="form-control"
                                    value="{{ request()->query('tanggal') ?? date('Y-m') }}">
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">
                                    Tampilkan
                                </button>
                            </div>

                            <input type="hidden" id="id_satuan" name="id_satuan">
                            <input type="hidden" id="id_barang2" name="id_barang2">

                        </div>
                    </form>
                    {{-- @endforeach --}}
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Nama Satuan</th>
                                <th>Keterangan</th>
                                <th>Tanggal</th>
                                <th>Qty In</th>
                                <th>Qty Out</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (empty($stockCards) || count($stockCards) == 0)
                                <tr>
                                    <td colspan="8" class="text-center">
                                        Tidak ada data stock card untuk barang ini.
                                    </td>
                                </tr>
                            @else
                                {{-- @dd($stockCards) --}}
                                @php
                                    $totalStock = 0;
                                @endphp

                                @php
                                    if (isset($saldoAwal)) {
                                        $saldoAwal = $saldoAwal;
                                        $totalStock = $saldoAwal;
                                    } else {
                                        $saldoAwal = 0;
                                        $totalStock = 0;
                                    }
                                @endphp
                                <tr>
                                    <td colspan="5" style="text-align: center;"><strong>Saldo Awal</strong></td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align: center;"><strong>{{ $saldoAwal }}</strong></td>
                                </tr>

                                @foreach ($stockCards as $index => $stockCard)
                                    @php
                                        if ($stockCard->qty < 0) {
                                            $qtyIn = $stockCard->qty;
                                            $qtyOut = 0;
                                        } else {
                                            $qtyIn = 0;
                                            $qtyOut = $stockCard->qty;
                                        }
                                        $qty = $stockCard->qty ?? 0;
                                        $totalStock += $qty;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $stockCard->barang->nama_barang ?? 'N/A' }}</td>
                                        <td>{{ $stockCard->barang->satuan->nama_satuan ?? 'N/A' }}</td>
                                        <td>{{ $stockCard->keterangan ?? 'N/A' }}</td>
                                        <td>{{ $stockCard->tanggal ?? 'N/A' }}</td>
                                        <td style="text-align: center;">{{ $qtyIn }}</td>
                                        <td style="text-align: center;">{{ $qtyOut }}</td>
                                        <td style="text-align: center;">{{ $totalStock }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="5" style="text-align: center;"><strong>Total Stock</strong></td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align: center;"><strong>{{ $totalStock }}</strong></td>
                                </tr>

                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#id_barang').select2({
                    placeholder: 'Pilih Barang',
                    allowClear: true,
                    width: '100%'
                });
            });
        </script>
    @endpush
@endsection
