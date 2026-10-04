@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Master Std Harga Beli</h3>
                    <div class="ml-auto d-flex">
                        <div class="btn-group mr-2">
                            <button type="button" class="btn btn-success btn-sm" id="btnExportExcel">
                                <i class="fas fa-file-excel"></i> Excel
                            </button>
                            <button type="button" class="btn btn-info btn-sm" id="btnExportCsv">
                                <i class="fas fa-file-csv"></i> CSV
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" id="btnExportPdf">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" id="btnPrint">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                        <a href="{{ route('masterStdHargaBeli.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Data
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="form-inline">
                            <input type="text" id="searchHargaBeli" class="form-control form-control-sm"
                                placeholder="Cari...">
                        </div>
                        <div>
                            <select id="perPage" class="form-control form-control-sm">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped" id="tabel-harga-beli">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Std Harga Beli</th>
                                <th>Item Kode</th>
                                <th>Item Nama</th>
                                <th>Cabang</th>
                                <th>Harga Beli</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-harga-beli">
                            <!-- diisi via JS -->
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center">
                        <div id="infoHargaBeli" class="text-muted small"></div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="pagination-harga-beli"></ul>
                        </nav>
                    </div>
                </div>
                <!-- /.card-body -->
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

                $(document).on('click', '.btn-view', function() {
                    const id = $(this).data('id');
                    window.location.href = "{{ url('/masterStdHargaBeli/show') }}" + "?id_std_harga_beli=" +
                        id + "&mode=view";
                });

                $(document).on('click', '.btn-edit', function() {
                    const id = $(this).data('id');
                    window.location.href = "{{ url('/masterStdHargaBeli/show') }}" + "?id_std_harga_beli=" +
                        id + "&mode=edit";
                });

                $(document).on('click', '.btn-delete', function() {
                    const id = $(this).data('id');
                    const kd = $(this).data('kd');

                    Swal.fire({
                        title: 'Hapus Std Harga Beli?',
                        text: 'Data tidak bisa dikembalikan setelah dihapus. Kode : ' + kd,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `{{ url('masterStdHargaBeli') }}/${id}`,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        loadHargaBeli(currentPage);
                                    } else {
                                        Swal.fire('Gagal', res.message, 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error', 'Gagal menghapus data.', 'error');
                                }
                            });
                        }
                    });
                });

                let currentPage = 1;
                let searchTimeout = null;

                function loadHargaBeli(page = 1) {
                    currentPage = page;
                    const search = $('#searchHargaBeli').val();
                    const perPage = $('#perPage').val();

                    $.ajax({
                        url: "{{ route('masterStdHargaBeli.index') }}",
                        method: 'get',
                        data: {
                            page,
                            search,
                            per_page: perPage
                        },
                        beforeSend: function() {
                            $('#tbody-harga-beli').html(
                                '<tr><td colspan="7" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderTable(res.data, res.from);
                            renderPagination(res);
                            $('#infoHargaBeli').text(
                                `Menampilkan ${res.from ?? 0} - ${res.to ?? 0} dari ${res.total} data`);
                        },
                        error: function() {
                            $('#tbody-harga-beli').html(
                                '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderTable(data, startNumber) {
                    if (!data.length) {
                        $('#tbody-harga-beli').html(
                            '<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }

                    // hitung rowspan per grup kode_std_harga_beli
                    let groups = [];
                    data.forEach(item => {
                        if (item.show_header) {
                            groups.push({
                                count: 1
                            });
                        } else {
                            groups[groups.length - 1].count++;
                        }
                    });

                    let rows = '';
                    let groupIndex = -1;
                    let nomor = startNumber;

                    data.forEach((item) => {
                        if (item.show_header) {
                            groupIndex++;
                            const g = groups[groupIndex];
                            rows += `<tr>
                                <td class="text-center" rowspan="${g.count}">${nomor}</td>
                                <td rowspan="${g.count}">${item.kode_std_harga_beli}</td>
                                <td rowspan="${g.count}">${item.item_kode}</td>
                                <td rowspan="${g.count}">${item.item_nama}</td>
                                <td>${item.nama_cabang}</td>
                                <td class="text-right">${formatRupiah(item.harga_beli)}</td>
                                <td class="text-center" rowspan="${g.count}">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <button class="dropdown-item btn-view" data-id="${item.id_encrypted}">View</button>
                                            <button class="dropdown-item btn-edit" data-id="${item.id_encrypted}">Edit</button>
                                            <button type="button" class="dropdown-item text-danger btn-delete" data-id="${item.id_encrypted}" data-kd="${item.kode_std_harga_beli}">Delete</button>
                                        </div>
                                    </div>
                                </td>
                            </tr>`;
                            nomor++;
                        } else {
                            rows += `<tr>
                                <td>${item.nama_cabang}</td>
                                <td class="text-right">${formatRupiah(item.harga_beli)}</td>
                            </tr>`;
                        }
                    });

                    $('#tbody-harga-beli').html(rows);
                }

                function formatRupiah(angka) {
                    if (angka === null || angka === undefined) return '-';
                    return 'Rp ' + Number(angka).toLocaleString('id-ID');
                }

                function renderPagination(res) {
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

                    $('#pagination-harga-beli').html(links);
                }

                $(document).on('click', '#pagination-harga-beli a', function(e) {
                    e.preventDefault();
                    loadHargaBeli($(this).data('page'));
                });

                $('#searchHargaBeli').on('keyup', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => loadHargaBeli(1), 400);
                });

                $('#perPage').on('change', function() {
                    loadHargaBeli(1);
                });

                $('#btnExportExcel').on('click', function() {
                    const search = $('#searchHargaBeli').val();
                    window.location.href = "{{ route('masterStdHargaBeli.export.excel') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportCsv').on('click', function() {
                    const search = $('#searchHargaBeli').val();
                    window.location.href = "{{ route('masterStdHargaBeli.export.csv') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportPdf').on('click', function() {
                    const search = $('#searchHargaBeli').val();
                    window.location.href = "{{ route('masterStdHargaBeli.export.pdf') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnPrint').on('click', function() {
                    const search = $('#searchHargaBeli').val();
                    window.open("{{ route('masterStdHargaBeli.print') }}?search=" + encodeURIComponent(search),
                        '_blank');
                });

                loadHargaBeli();
            });
        </script>

        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: "{{ session('success') }}",
                    confirmButtonText: 'OK'
                })
            </script>
        @endif

        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    confirmButtonText: 'OK'
                });
            </script>
        @endif
    @endpush
@endsection
