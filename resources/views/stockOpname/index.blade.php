@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    {{-- <style>
        #scanner video,
        #scanner canvas {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            /* biar memenuhi div tanpa gepeng */
        }
    </style> --}}
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Stock Opname</h3>
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
                        <a href="{{ route('stockOpname.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Data
                        </a>
                    </div>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="form-inline">
                            <input type="text" id="searchStock" class="form-control form-control-sm"
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

                    <table class="table table-bordered table-striped" id="tabel-stock">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Stock Opname</th>
                                <th>Periode</th>
                                <th>Approve By</th>
                                <th>Approve At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-stock">
                            <!-- diisi via JS -->
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center">
                        <div id="infoStock" class="text-muted small"></div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="pagination-stock"></ul>
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
                // Quagga.init({
                //     inputStream: {
                //         name: "Live",
                //         type: "LiveStream",
                //         target: document.querySelector('#scanner'),
                //         constraints: {
                //             width: 1280, // resolusi asli kamera
                //             height: 720,
                //             facingMode: "environment" // kamera belakang
                //         }
                //     },
                //     decoder: {
                //         readers: [
                //             "code_128_reader",
                //             "ean_reader",
                //             "ean_8_reader",
                //             "code_39_reader",
                //             "code_39_vin_reader",
                //             "codabar_reader",
                //             "upc_reader",
                //             "upc_e_reader",
                //             "i2of5_reader",
                //             "2of5_reader",
                //             "code_93_reader"
                //         ]
                //     }
                // }, function(err) {
                //     if (err) {
                //         console.log(err);
                //         return;
                //     }
                //     Quagga.start();
                // });

                // Quagga.onDetected(function(result) {
                //     if (result && result.codeResult && result.codeResult.code) {
                //         let code = result.codeResult.code;
                //         alert("Barcode terbaca:", code);

                //         // Tambahkan ke textarea (baris baru setiap scan)
                //         let textarea = document.getElementById("barcode");
                //         textarea.value += code + ",";

                //         // Kalau tidak mau duplikat, bisa tambahkan pengecekan:
                //         // if (!textarea.value.includes(code)) {
                //         //     textarea.value += code + "\n";
                //         // }
                //     }
                // });


                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $('#select2-barang').select2({
                    placeholder: 'Cari data...',
                    ajax: {
                        url: "{{ route('getDataBarang') }}", // sesuaikan dengan route kamu
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term // keyword pencarian
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    }
                });


                $(document).on('click', '.btn-view', function() {
                    const id = $(this).data('id');
                    // alert(id);
                    $.ajax({
                        url: `masterbarang/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_barang').val(res.data.kode_barang);
                                $('#nama_barang').val(res.data.nama_barang);

                                let newOptionJenis = new Option(res.data.jenis_barang.nama_jenis,
                                    res.data.kode_jenis_barang, true, true);
                                $('#select2-jenisBarang').append(newOptionJenis).trigger('change');

                                let newOptionBrand = new Option(res.data.brand.nama_brand,
                                    res.data.kode_brand, true, true);
                                $('#select2-brand').append(newOptionBrand).trigger('change');

                                let newOptionSatuan = new Option(res.data.satuan.nama_satuan,
                                    res.data.kode_satuan, true, true);
                                $('#select2-satuan').append(newOptionSatuan).trigger('change');

                                $('#modal-tambah').modal('show');
                                $('#btnBatal').hide();
                                $('#btnSimpan').hide();
                                $('#btnUpdate').hide();
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
                                text: 'Terjadi kesalahan.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                })

                $(document).on('click', '.btn-edit', function() {
                    const id = $(this).data('id');
                    // alert(id);
                    $('#idBarang').val(id);
                    $.ajax({
                        url: `masterbarang/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_barang').val(res.data.kode_barang);
                                $('#nama_barang').val(res.data.nama_barang);

                                let newOptionJenis = new Option(res.data.jenis_barang.nama_jenis,
                                    res.data.kode_jenis_barang, true, true);
                                $('#select2-jenisBarang').append(newOptionJenis).trigger('change');

                                let newOptionBrand = new Option(res.data.brand.nama_brand,
                                    res.data.kode_brand, true, true);
                                $('#select2-brand').append(newOptionBrand).trigger('change');

                                let newOptionSatuan = new Option(res.data.satuan.nama_satuan,
                                    res.data.kode_satuan, true, true);
                                $('#select2-satuan').append(newOptionSatuan).trigger('change');

                                $('#modal-tambah').modal('show');
                                $('#btnBatal').show();
                                $('#btnSimpan').hide();
                                $('#btnUpdate').show();
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
                                text: 'Terjadi kesalahan.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                })



                $(document).on('click', '.btn-delete', function() {
                    const id = $(this).data('id');

                    Swal.fire({
                        title: 'Hapus Satuan?',
                        text: 'Data tidak bisa dikembalikan setelah dihapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `{{ url('stockOpname') }}/${id}`,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        loadStock(); // reload halaman saat ini
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

                $(document).on('click', '.btn-approve', function() {
                    const id = $(this).data('id');

                    Swal.fire({
                        title: 'Yakin approve?',
                        text: 'Data tidak bisa dikembalikan setelah diapporve.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, apporve',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `{{ route('stockOpname.approve', ':id') }}`.replace(':id',
                                    id),
                                type: 'GET',
                                data: {
                                    _method: 'GET',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        loadStock(); // reload halaman saat ini
                                    } else {
                                        Swal.fire('Gagal', res.message, 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error', 'Gagal approve data.', 'error');
                                }
                            });
                        }
                    });
                });


                let currentPage = 1;
                let searchTimeout = null;

                function loadStock(page = 1) {
                    currentPage = page;
                    const search = $('#searchStock').val();
                    const perPage = $('#perPage').val();

                    $.ajax({
                        url: "{{ route('stockOpname.index') }}",
                        method: 'get',
                        data: {
                            page,
                            search,
                            per_page: perPage
                        },
                        beforeSend: function() {
                            $('#tbody-stock').html(
                                '<tr><td colspan="6" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderTable(res.data, res.from);
                            renderPagination(res);
                            $('#infoStock').text(
                                `Menampilkan ${res.from ?? 0} - ${res.to ?? 0} dari ${res.total} data`);
                        },
                        error: function() {
                            $('#tbody-stock').html(
                                '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderTable(data, startNumber) {
                    if (!data.length) {
                        $('#tbody-stock').html('<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }

                    let rows = '';
                    data.forEach((stock, i) => {
                        let buttons = `<a href="{{ url('stockOpname') }}/${stock.id_encrypted}" class="dropdown-item text-info">
                                <i class="fas fa-eye"></i> Show
                           </a>`;

                        if (!stock.approve_by) {
                            buttons += `
                    <button type="button" class="dropdown-item text-success btn-approve" data-id="${stock.kode_encrypted}">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button type="button" class="dropdown-item text-danger btn-delete" data-id="${stock.kode_encrypted}">
                        <i class="fas fa-trash"></i> Delete
                    </button>`;
                        }

                        rows += `
    <tr>
        <td class="text-center">${startNumber + i}</td>
        <td>${stock.kode_stock_opname}</td>
        <td>${stock.periode}</td>
        <td>${stock.approve_by ?? '-'}</td>
        <td>${stock.approve_at ?? '-'}</td>
        <td class="text-center">
            <div class="dropdown">
                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-cog"></i>
                </button>
                <div class="dropdown-menu">
                    ${buttons}
                </div>
            </div>
        </td>
    </tr>`;
                    });
                    $('#tbody-stock').html(rows);
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

                    $('#pagination-stock').html(links);
                }

                $(document).on('click', '#pagination-stock a', function(e) {
                    e.preventDefault();
                    loadStock($(this).data('page'));
                });

                $('#searchStock').on('keyup', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => loadStock(1), 400);
                });

                $('#perPage').on('change', function() {
                    loadStock(1);
                });

                $('#btnExportExcel').on('click', function() {
                    const search = $('#searchStock').val();
                    window.location.href = "{{ route('stockOpname.export.excel') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportCsv').on('click', function() {
                    const search = $('#searchStock').val();
                    window.location.href = "{{ route('stockOpname.export.csv') }}?search=" + encodeURIComponent(
                        search);
                });

                $('#btnExportPdf').on('click', function() {
                    const search = $('#searchStock').val();
                    window.location.href = "{{ route('stockOpname.export.pdf') }}?search=" + encodeURIComponent(
                        search);
                });

                $('#btnPrint').on('click', function() {
                    const search = $('#searchStock').val();
                    window.open("{{ route('stockOpname.print') }}?search=" + encodeURIComponent(search),
                        '_blank');
                });

                loadStock();
            });
        </script>
    @endpush

@endsection
