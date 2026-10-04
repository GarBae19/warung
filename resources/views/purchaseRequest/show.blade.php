@extends('layouts.tamplate')
@section('title', 'Detail Purchase Request')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Detail Purchase Request</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('purchaseRequest.index') }}" class="btn btn-secondary mb-3">Kembali</a>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="35%">Kode PR</th>
                                    <td>: {{ $pr->kode_pr }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>: {{ date('d-m-Y', strtotime($pr->tanggal)) }}</td>
                                </tr>
                                <tr>
                                    <th>Cabang</th>
                                    <td>: {{ $pr->cabang->nama_cabang ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Departemen</th>
                                    <td>: {{ $pr->departemens->nama_departemen ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="35%">Request By</th>
                                    <td>: {{ $pr->request_by }}</td>
                                </tr>
                                <tr>
                                    <th>Jenis PR</th>
                                    <td>: {{ str_replace('_', ' ', ucfirst($pr->jenis_pr)) }}</td>
                                </tr>
                                <tr>
                                    <th>Kode WO</th>
                                    <td>: {{ $pr->kode_wo ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Keterangan</th>
                                    <td>: {{ $pr->keterangan ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Qty PR</th>
                                    <th>Qty PO</th>
                                    <th>Qty Outs</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pr->items as $i => $item)
                                    @php
                                        $badge = match ($item->status) {
                                            'open' => 'badge-secondary',
                                            'po' => 'badge-info',
                                            'selesai' => 'badge-success',
                                            'cancel' => 'badge-danger',
                                            default => 'badge-light',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item->barang->kode_barang ?? '-' }}</td>
                                        <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                                        <td>{{ $item->barang->satuan->nama_satuan ?? '-' }}</td>
                                        <td>{{ $item->qty_pr }}</td>
                                        <td>{{ $item->qty_po }}</td>
                                        <td>{{ $item->qty_outs }}</td>
                                        <td>{{ $item->notes }}</td>
                                        <td><span class="badge {{ $badge }}">{{ ucfirst($item->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada item</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
