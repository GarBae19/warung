@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <style>
        #scanner video,
        #scanner canvas {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            /* biar memenuhi div tanpa gepeng */
        }
    </style>
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Tambah Stock Opname</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('stockOpname.index') }}" id="kembali" class="btn btn-secondary mb-3"
                        style="displa:none">Kembali</a>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kode_stock_opname">Kode Stock Opname</label>
                                <input type="text" id="kode_stock_opname" name="kode_stock_opname" class="form-control"
                                    value="{{ $stockOpname->kode_stock_opname ?? '' }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="periode">Periode</label>
                                <input type="date" id="periode" name="periode" class="form-control" required
                                    value="{{ $stockOpname->periode ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_gudang">Gudang</label>
                                <input type="text" id="id_gudang" name="id_gudang" class="form-control"
                                    value="{{ $gudang ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-2 mt-3">
                        <input type="text" id="searchDetail" class="form-control form-control-sm w-25"
                            placeholder="Cari barang...">
                        <select id="perPageDetail" class="form-control form-control-sm w-auto">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <table id="table-stockOpname-detail" class="table table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Nama Satuan</th>
                                <th>Qty Stock</th>
                                <th>Qty Real</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-detail">
                            <!-- diisi via JS -->
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center">
                        <div id="infoDetail" class="text-muted small"></div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="pagination-detail"></ul>
                        </nav>
                    </div>

                    <div class="modal fade" id="modal-scan" tabindex="-1" aria-labelledby="modalScanLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalScanLabel">Scan Barcode</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">

                                    <!-- input qty -->
                                    <div class="form-group mt-3">
                                        <label for="qty_scan">Qty</label>
                                        <input type="number" id="qty_scan" class="form-control" min="0"
                                            value="0">
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                        <button type="button" class="btn btn-primary" id="btn-save-scan">Simpan</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>


    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                const kodeStockOpnameGlobal = $('#kode_stock_opname').val();
                let currentPageDetail = 1;
                let searchTimeoutDetail = null;

                $(document).on('click', '.btn-reset', function() {
                    let id_barang = $(this).data('id');
                    let id_satuan = $(this).data('satuan');
                    let kode_stock_opname = $('#kode_stock_opname').val();

                    Swal.fire({
                        title: 'Yakin?',
                        text: "Data stock opname detail akan dihapus!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('stockOpname.reset') }}",
                                type: "DELETE",
                                data: {
                                    id_barang: id_barang,
                                    id_satuan: id_satuan,
                                    kode_stock_opname: kode_stock_opname,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Terhapus!', res.message, 'success');
                                        loadDetail(currentPageDetail);
                                    } else {
                                        Swal.fire('Gagal!', 'Data gagal dihapus.', 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error!', 'Terjadi kesalahan server.',
                                        'error');
                                }
                            });
                        }
                    });
                });

                // ketika tombol scan di klik
                $(document).on('click', '.btn-scan', function() {
                    let id = $(this).data('id');
                    let id_barang = $(this).data('id');
                    let id_satuan = $(this).data('satuan');
                    $('#modal-scan').modal('show');
                    $('#qty_scan').val('');

                    $('#btn-save-scan').off('click').on('click', function() {
                        let qty = $('#qty_scan').val();
                        let kode_stock_opname = $('#kode_stock_opname').val();

                        $.ajax({
                            url: "{{ route('stockOpname.scan') }}",
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: id,
                                qty: qty,
                                kode_stock_opname: kode_stock_opname,
                                id_barang: id_barang,
                                id_satuan: id_satuan
                            },
                            success: function(res) {
                                if (res.status == 'success') {
                                    Swal.fire('Berhasil!', 'Data berhasil disimpan',
                                        'success');
                                    $('#modal-scan').modal('hide');
                                    loadDetail(currentPageDetail);
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: res.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: 'Terjadi kesalahan saat menyimpan data.' +
                                        error,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    });
                });

                // stop scanner kalau modal ditutup manual
                $('#modal-scan').on('hidden.bs.modal', function() {
                    if (typeof Quagga !== 'undefined' && Quagga.running) {
                        Quagga.stop();
                    }
                });

                function loadDetail(page = 1) {
                    currentPageDetail = page;
                    const search = $('#searchDetail').val();
                    const perPage = $('#perPageDetail').val();

                    $.ajax({
                        url: "{{ route('stockOpname.detail') }}",
                        method: 'get',
                        data: {
                            kode_stock_opname: kodeStockOpnameGlobal,
                            page,
                            search,
                            per_page: perPage
                        },
                        beforeSend: function() {
                            $('#tbody-detail').html(
                                '<tr><td colspan="6" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderDetailTable(res.data);
                            renderPaginationDetail(res);
                            $('#infoDetail').text(
                                `Menampilkan ${res.from ?? 0} - ${res.to ?? 0} dari ${res.total} data`);
                        },
                        error: function() {
                            $('#tbody-detail').html(
                                '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderDetailTable(data) {
                    if (!data.length) {
                        $('#tbody-detail').html('<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }

                    let rows = '';
                    data.forEach((barang) => {
                        let action = '';
                        if (barang.can_edit) {
                            action = `
                    <button class="btn btn-sm btn-primary btn-scan" data-id="${barang.barang_encrypted}" data-satuan="${barang.satuan_encrypted}">Input</button>
                    <button class="btn btn-sm btn-danger btn-reset" data-id="${barang.barang_encrypted}" data-satuan="${barang.satuan_encrypted}">Reset</button>
                `;
                        }

                        rows += `
    <tr>
        <td>${barang.kode_barang}</td>
        <td>${barang.nama_barang}</td>
        <td>${barang.nama_satuan}</td>
        <td class="text-center">${barang.qty_stock}</td>
        <td class="text-center">${barang.qty_real}</td>
        <td class="text-center">${action}</td>
    </tr>`;
                    });
                    $('#tbody-detail').html(rows);
                }

                function renderPaginationDetail(res) {
                    let links = '';
                    const current = res.current_page;
                    const last = res.last_page;

                    if (res.prev_page_url) {
                        links +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${current - 1}">&laquo;</a></li>`;
                    } else {
                        links += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
                    }

                    const delta = 2;
                    let range = [];
                    for (let p = 1; p <= last; p++) {
                        if (p === 1 || p === last || (p >= current - delta && p <= current + delta)) {
                            range.push(p);
                        }
                    }

                    let prevPage = 0;
                    range.forEach(function(p) {
                        if (prevPage && p - prevPage > 1) {
                            links += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                        }
                        links += `<li class="page-item ${p === current ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${p}">${p}</a>
                      </li>`;
                        prevPage = p;
                    });

                    if (res.next_page_url) {
                        links +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${current + 1}">&raquo;</a></li>`;
                    } else {
                        links += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
                    }

                    $('#pagination-detail').html(links);
                }

                $(document).on('click', '#pagination-detail a', function(e) {
                    e.preventDefault();
                    loadDetail($(this).data('page'));
                });

                $('#searchDetail').on('keyup', function() {
                    clearTimeout(searchTimeoutDetail);
                    searchTimeoutDetail = setTimeout(() => loadDetail(1), 400);
                });

                $('#perPageDetail').on('change', function() {
                    loadDetail(1);
                });

                loadDetail();
            });
        </script>
    @endpush

@endsection
