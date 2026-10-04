@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Laporan Penjualan</h3>
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
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="text" id="daterange" class="form-control"
                                    placeholder="Pilih Rentang Tanggal (default: hari ini)">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-sm" id="btn-filter">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" id="btn-clear-date">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Ringkasan Total Harian -->
                    <h6 class="mt-3">Ringkasan Total Harian</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered table-hover" id="tabel-total-harian">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th class="text-center">Jumlah Transaksi</th>
                                    <th class="text-center">Total Qty</th>
                                    <th class="text-end">Total Beli</th>
                                    <th class="text-end">Total Jual</th>
                                    <th class="text-end">Profit</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-total-harian" style="cursor: pointer;">
                                <!-- diisi via JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Placeholder sebelum ada tanggal dipilih -->
                    <div id="detailPlaceholder" class="text-center text-muted py-4 border rounded">
                        <i class="fas fa-hand-pointer"></i>
                        Klik salah satu baris tanggal di atas untuk melihat detail transaksi.
                    </div>

                    <!-- Detail transaksi, muncul setelah klik salah satu tanggal -->
                    <div id="detailWrapper" style="display:none;">
                        <h6 class="mt-4 d-flex align-items-center justify-content-between">
                            <span>Detail Transaksi — <span id="selectedDateLabel"></span></span>
                            <button class="btn btn-sm btn-outline-secondary" id="btnCloseDetail">
                                <i class="fas fa-times"></i> Tutup
                            </button>
                        </h6>

                        <div class="row mb-2">
                            <div class="col-md-4">
                                <input type="text" id="searchPenjualan" class="form-control form-control-sm"
                                    placeholder="Cari kode pos / item / satuan...">
                            </div>
                            <div class="col-md-2">
                                <select id="perPage" class="form-control form-control-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="all">All</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tabel-penjualan">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Kode Transaksi</th>
                                        <th class="text-center">Tanggal</th>
                                        <th>Item Nama</th>
                                        <th>Satuan</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga Beli</th>
                                        <th class="text-end">Harga Jual</th>
                                        <th class="text-end">Total Beli</th>
                                        <th class="text-end">Total Jual</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-penjualan"></tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <th colspan="8" class="text-end">GRAND TOTAL</th>
                                        <th id="grand_total_beli" class="text-end"></th>
                                        <th id="grand_total_jual" class="text-end"></th>
                                    </tr>
                                    <tr class="table-warning">
                                        <th colspan="9" class="text-end">PROFIT</th>
                                        <th id="grand_profit" class="text-end"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div id="infoPenjualan" class="text-muted small"></div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="pagination-penjualan"></ul>
                            </nav>
                        </div>
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

                let currentPage = 1;
                let searchTimeout = null;
                let selectedDate = null; // format Y-m-d, tanggal yang sedang dipilih di ringkasan

                $('#daterange').daterangepicker({
                    autoUpdateInput: false,
                    opens: 'right',
                    locale: {
                        cancelLabel: 'Batal',
                        applyLabel: 'Terapkan',
                        format: 'DD/MM/YYYY',
                        separator: ' - ',
                        daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
                            'September', 'Oktober', 'November', 'Desember'
                        ],
                    }
                });

                $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format(
                        'DD/MM/YYYY'));
                });

                $('#btn-clear-date').on('click', function() {
                    $('#daterange').val('');
                    closeDetail();
                    loadDailyTotals();
                });

                $('#btn-filter').on('click', function() {
                    closeDetail();
                    loadDailyTotals();
                });

                $('#btnCloseDetail').on('click', function() {
                    closeDetail();
                });

                function getRangeParams() {
                    const picker = $('#daterange').data('daterangepicker');
                    if ($('#daterange').val() && picker) {
                        return {
                            start_date: picker.startDate.format('YYYY-MM-DD'),
                            end_date: picker.endDate.format('YYYY-MM-DD')
                        };
                    }
                    return {};
                }

                function formatRupiah(angka) {
                    return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
                }

                // ---------- Ringkasan Harian ----------
                function loadDailyTotals() {
                    $.ajax({
                        url: "{{ route('rptSales.index') }}",
                        method: 'get',
                        data: Object.assign({
                            per_page: 1
                        }, getRangeParams()),
                        beforeSend: function() {
                            $('#tbody-total-harian').html(
                                '<tr><td colspan="6" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderDailyTotals(res.daily_totals);
                        },
                        error: function() {
                            $('#tbody-total-harian').html(
                                '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderDailyTotals(dailyTotals) {
                    if (!dailyTotals || !dailyTotals.length) {
                        $('#tbody-total-harian').html(
                            '<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }

                    let rows = '';
                    let grand_profit = 0;
                    dailyTotals.forEach((row) => {
                        const profitClass = row.profit < 0 ? 'text-danger' : 'text-success';
                        const active = row.tanggal_raw === selectedDate ? 'table-active' : '';
                        rows += `
    <tr class="${active}" data-tanggal="${row.tanggal_raw}" data-label="${row.tanggal}">
        <td>${row.tanggal}</td>
        <td class="text-center">${row.jumlah_transaksi}</td>
        <td class="text-center">${row.total_qty}</td>
        <td class="text-end">${formatRupiah(row.total_beli)}</td>
        <td class="text-end">${formatRupiah(row.total_jual)}</td>
        <td class="text-end fw-bold ${profitClass}">${formatRupiah(row.profit)}</td>
    </tr>`;
                        grand_profit = row.profit + grand_profit;
                    });
                    rows += `
                    <tr>
                        <td colspan = "5" class="text-center fw-bold">Grand Profit</td>
                        <td class="text-end fw-bold text-success">${formatRupiah(grand_profit)}</td>
                    </tr>`;
                    $('#tbody-total-harian').html(rows);
                }

                // Klik baris ringkasan -> tampilkan detail tanggal itu
                $(document).on('click', '#tbody-total-harian tr', function() {
                    const tanggal = $(this).data('tanggal');
                    const label = $(this).data('label');
                    if (!tanggal) return;

                    selectedDate = tanggal;

                    $('#tbody-total-harian tr').removeClass('table-active');
                    $(this).addClass('table-active');

                    $('#selectedDateLabel').text(label);
                    $('#detailPlaceholder').hide();
                    $('#detailWrapper').show();

                    loadDetail(1);
                });

                function closeDetail() {
                    selectedDate = null;
                    $('#tbody-total-harian tr').removeClass('table-active');
                    $('#detailWrapper').hide();
                    $('#detailPlaceholder').show();
                }

                // ---------- Detail Transaksi (per tanggal terpilih) ----------
                function loadDetail(page = 1) {
                    if (!selectedDate) return;
                    currentPage = page;

                    const search = $('#searchPenjualan').val();
                    const perPage = $('#perPage').val();

                    $.ajax({
                        url: "{{ route('rptSales.index') }}",
                        method: 'get',
                        data: {
                            page,
                            search,
                            per_page: perPage,
                            start_date: selectedDate,
                            end_date: selectedDate
                        },
                        beforeSend: function() {
                            $('#tbody-penjualan').html(
                                '<tr><td colspan="10" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderTable(res.data, res.from);
                            renderPagination(res);
                            renderGrandTotal(res);
                            $('#infoPenjualan').text(
                                `Menampilkan ${res.from ?? 0} - ${res.to ?? 0} dari ${res.total} data`);
                        },
                        error: function() {
                            $('#tbody-penjualan').html(
                                '<tr><td colspan="10" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderTable(data, startNumber) {
                    if (!data.length) {
                        $('#tbody-penjualan').html('<tr><td colspan="10" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }
                    let rows = '';
                    data.forEach((item, i) => {
                        rows += `
    <tr>
        <td class="text-center">${startNumber + i}</td>
        <td>${item.kode_pos ?? '-'}</td>
        <td class="text-center">${item.tanggal}</td>
        <td>${item.item_nama}</td>
        <td>${item.satuan}</td>
        <td class="text-center">${item.qty}</td>
        <td class="text-end">${formatRupiah(item.harga_beli)}</td>
        <td class="text-end">${formatRupiah(item.harga_jual)}</td>
        <td class="text-end fw-bold">${formatRupiah(item.total_beli)}</td>
        <td class="text-end fw-bold">${formatRupiah(item.total_jual)}</td>
    </tr>`;
                    });
                    $('#tbody-penjualan').html(rows);
                }

                function renderGrandTotal(res) {
                    $('#grand_total_beli').text(formatRupiah(res.grand_total_beli));
                    $('#grand_total_jual').text(formatRupiah(res.grand_total_jual));
                    $('#grand_profit').text(formatRupiah(res.grand_profit));
                }

                function renderPagination(res) {
                    let links = '';
                    const current = res.current_page;
                    const last = res.last_page;

                    links += res.prev_page_url ?
                        `<li class="page-item"><a class="page-link" href="#" data-page="${current - 1}">&laquo;</a></li>` :
                        `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;

                    const delta = 2;
                    let range = [];
                    for (let p = 1; p <= last; p++) {
                        if (p === 1 || p === last || (p >= current - delta && p <= current + delta)) range.push(p);
                    }

                    let prevPage = 0;
                    range.forEach(function(p) {
                        if (prevPage && p - prevPage > 1) {
                            links += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                        }
                        links += `<li class="page-item ${p === current ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
                        prevPage = p;
                    });

                    links += res.next_page_url ?
                        `<li class="page-item"><a class="page-link" href="#" data-page="${current + 1}">&raquo;</a></li>` :
                        `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;

                    $('#pagination-penjualan').html(links);
                }

                $(document).on('click', '#pagination-penjualan a', function(e) {
                    e.preventDefault();
                    loadDetail($(this).data('page'));
                });

                $('#searchPenjualan').on('keyup', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => loadDetail(1), 400);
                });

                $('#perPage').on('change', function() {
                    loadDetail(1);
                });

                loadDailyTotals(); // hanya load ringkasan saat pertama buka halaman

                function buildExportUrl(routeName) {
                    const search = $('#searchPenjualan').val() || '';
                    let dateParams = {};

                    if (selectedDate) {
                        // sedang lihat detail 1 tanggal -> export tanggal itu saja
                        dateParams = {
                            start_date: selectedDate,
                            end_date: selectedDate
                        };
                    } else {
                        // belum pilih tanggal -> export sesuai filter rentang di atas
                        dateParams = getRangeParams();
                    }

                    const params = new URLSearchParams(Object.assign({
                        search
                    }, dateParams));
                    return routeName + '?' + params.toString();
                }

                $('#btnExportExcel').on('click', function() {
                    window.location.href = buildExportUrl("{{ route('rptSales.export.excel') }}");
                });

                $('#btnExportCsv').on('click', function() {
                    window.location.href = buildExportUrl("{{ route('rptSales.export.csv') }}");
                });

                $('#btnExportPdf').on('click', function() {
                    window.location.href = buildExportUrl("{{ route('rptSales.export.pdf') }}");
                });

                $('#btnPrint').on('click', function() {
                    window.open(buildExportUrl("{{ route('rptSales.print') }}"), '_blank');
                });
            });
        </script>
    @endpush
@endsection
