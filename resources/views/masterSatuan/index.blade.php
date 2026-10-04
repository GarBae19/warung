@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Master Satuan</h3>
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
                        <button id="tambahData" type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#modal-tambah">
                            <i class="fas fa-plus"></i> Tambah Data
                        </button>
                    </div>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="form-inline">
                            <input type="text" id="searchSatuan" class="form-control form-control-sm"
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

                    <table class="table table-bordered table-striped" id="tabel-satuan">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Satuan</th>
                                <th>Nama Satuan</th>
                                <th>Keterangan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-satuan">
                            <!-- diisi via JS -->
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center">
                        <div id="infoSatuan" class="text-muted small"></div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="pagination-satuan"></ul>
                        </nav>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </section>

        <div class="modal fade" id="modal-tambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="form-tambah-satuan">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahLabel">Tambah Satuan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary btn-sm mb-3" id="btnImportExcel"
                                    data-toggle="modal" data-target="#modal-import-excel">
                                    <i class="fas fa-file-excel"></i> Import Excel
                                </button>
                            </div>
                            <input type="hidden" id="formMode" value="create"> <!-- atau 'edit' -->
                            <input type="hidden" id="idSatuan"> <!-- atau 'edit' -->
                            <div class="form-group">
                                <label for="kode_satuan">Kode Satuan</label>
                                <input type="text" class="form-control" id="kode_satuan" name="kode_satuan" required>
                            </div>
                            <div class="form-group">
                                <label for="nama_satuan">Nama Satuan</label>
                                <input type="text" class="form-control" id="nama_satuan" name="nama_satuan" required>
                            </div>
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                id="btnBatal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                            <button type="submit" class="btn btn-primary" id="btnUpdate">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="modal fade" id="modal-import-excel" tabindex="-1" role="dialog"
            aria-labelledby="modalImportLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="form-import-satuan" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalImportLabel">Import Satuan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-end">
                                <a href="{{ asset('template_excel/template_satuan.xlsx') }}"
                                    class="btn btn-sm btn-success mb-3" download>
                                    <i class="fas fa-download"></i> Download Template Excel
                                </a>
                            </div>
                            <div class="form-group">
                                <label for="file_excel">Pilih File Excel</label>
                                <input type="file" class="form-control" id="file_excel" name="file_excel"
                                    accept=".xlsx, .xls" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                id="btnBatal">Batal</button>
                            <button type="submit" class="btn btn-primary" id="btnSimpanImport">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $('#btnImportExcel').on('click', function() {
                    // Tutup modal tambah
                    $('#modal-tambah').modal('hide');

                    // Tunggu sedikit biar transisi modal smooth, lalu buka modal import
                    setTimeout(function() {
                        $('#modal-import-excel').modal('show');
                    }, 400);
                });

                // Saat modal import ditutup (klik batal / close)
                $('#modal-import-excel').on('hidden.bs.modal', function() {
                    // Buka kembali modal tambah
                    $('#modal-tambah').modal('show');
                });

                $('#tambahData').on('click', function(e) {
                    $('#btnBatal').show();
                    $('#btnSimpan').show();
                    $('#btnUpdate').hide();
                    $('#nama_satuan').val('');
                    $('#keterangan').val('');
                    $.ajax({
                        url: "{{ url('mastersatuan-kode') }}",
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_satuan').val(res.kodeSatuan);
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

                $(document).on('click', '.btn-view', function() {
                    const id = $(this).data('id');
                    // alert(id);
                    $.ajax({
                        url: `mastersatuan/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_satuan').val(res.data.kode_satuan);
                                $('#nama_satuan').val(res.data.nama_satuan);
                                $('#keterangan').val(res.data.keterangan);
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
                    $('#idSatuan').val(id);
                    $.ajax({
                        url: `mastersatuan/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_satuan').val(res.data.kode_satuan);
                                $('#nama_satuan').val(res.data.nama_satuan);
                                $('#keterangan').val(res.data.keterangan);
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

                $('#btnSimpan').on('click', function() {
                    $('#formMode').val('create');
                    $('#form-tambah-satuan').submit();
                });

                $('#btnUpdate').on('click', function() {
                    $('#formMode').val('update');
                    $('#form-tambah-satuan').submit();
                });


                $('#form-tambah-satuan').on('submit', function(e) {
                    e.preventDefault(); // Cegah submit default

                    const form = this;

                    let mode = $('#formMode').val(); // 'create' atau 'edit'

                    // alert(mode);
                    let url = '';
                    let method = 'POST';

                    if (mode === 'create') {
                        url = "{{ route('mastersatuan.store') }}";
                    } else if (mode === 'update') {
                        let idSatuan = $('#idSatuan').val();
                        url = "{{ url('mastersatuan') }}/" + idSatuan;
                        // method = 'PUT';
                    }

                    Swal.fire({
                        title: mode === 'create' ? 'Simpan Satuan?' : 'Update Satuan?',
                        text: 'Pastikan data sudah benar.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: mode === 'create' ? 'Ya, Simpan' : 'Ya, Update',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Di sini kamu bisa kirim data ke server pakai AJAX
                            const kodeSatuan = $('#kode_satuan').val();
                            const namaSatuan = $('#nama_satuan').val();
                            const keterangan = $('#keterangan').val();

                            if (!kodeSatuan || !namaSatuan || !keterangan) {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: 'Satuan gagal disimpan. Pastikan semua terisi.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });

                            } else {
                                $.ajax({
                                    url: url,
                                    method: method,
                                    data: $(form).serialize() + (mode === 'update' ?
                                        '&_method=PUT' : ''),
                                    // data: {
                                    //     kode_satuan: kodeSatuan,
                                    //     nama_satuan: namaSatuan
                                    // },
                                    success: function(res) {
                                        if (res.status == 'success') {
                                            $('#modal-tambah').modal('hide');
                                            form.reset();

                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: 'Satuan berhasil disimpan.',
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            });

                                            // Kalau pakai DataTables, refresh:
                                            loadSatuan();
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
                                            text: 'Terjadi kesalahan saat menyimpan data.',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                });
                            }
                        }
                    });
                });

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
                                url: `mastersatuan/${id}`,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        loadSatuan();
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

                $('#form-import-satuan').on('submit', function(e) {
                    e.preventDefault();

                    let formData = new FormData(this);
                    $('#btnSimpanImport').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> Mengimpor...');

                    $.ajax({
                        url: "{{ route('masterSatuan.importExcel') }}",
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Tutup modal import dan buka modal tambah
                                $('#modal-import-excel').modal('hide');
                                $('#form-import-satuan')[0].reset();
                                loadSatuan();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: res.message
                                });
                                loadSatuan();

                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message ||
                                    'Terjadi kesalahan saat import.'
                            });
                            loadSatuan();
                        },
                        complete: function() {
                            $('#btnSimpanImport').prop('disabled', false).html('Simpan');
                        }
                    });
                });

                let currentPage = 1;
                let searchTimeout = null;

                function loadSatuan(page = 1) {
                    currentPage = page;
                    const search = $('#searchSatuan').val();
                    const perPage = $('#perPage').val();

                    $.ajax({
                        url: "{{ url('/mastersatuan') }}",
                        method: 'get',
                        data: {
                            page,
                            search,
                            per_page: perPage
                        },
                        beforeSend: function() {
                            $('#tbody-satuan').html(
                                '<tr><td colspan="5" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderTable(res.data, res.from);
                            renderPagination(res);
                            $('#infoSatuan').text(
                                `Menampilkan ${res.from ?? 0} - ${res.to ?? 0} dari ${res.total} data`);
                        },
                        error: function() {
                            $('#tbody-satuan').html(
                                '<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderTable(data, startNumber) {
                    if (!data.length) {
                        $('#tbody-satuan').html('<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }

                    let rows = '';
                    data.forEach((satuan, i) => {
                        rows += `
    <tr>
        <td class="text-center">${startNumber + i}</td>
        <td>${satuan.kode_satuan}</td>
        <td>${satuan.nama_satuan}</td>
        <td>${satuan.keterangan ?? '-'}</td>
        <td class="text-center">
            <div class="dropdown">
                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-cog"></i>
                </button>
                <div class="dropdown-menu">
                    <button class="dropdown-item btn-view" data-id="${satuan.id_encrypted}">View</button>
                    ${!satuan.is_used ? `
                                                                                        <button class="dropdown-item btn-edit" data-id="${satuan.id_encrypted}">Edit</button>
                                                                                        <button type="button" class="dropdown-item text-danger btn-delete" data-id="${satuan.id}">Delete</button>
                                                                                    ` : ''}
                </div>
            </div>
        </td>
    </tr>`;
                    });
                    $('#tbody-satuan').html(rows);
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

                    $('#pagination-satuan').html(links);
                }

                $(document).on('click', '#pagination-satuan a', function(e) {
                    e.preventDefault();
                    loadSatuan($(this).data('page'));
                });

                $('#searchSatuan').on('keyup', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => loadSatuan(1), 400);
                });

                $('#perPage').on('change', function() {
                    loadSatuan(1);
                });

                $('#btnExportExcel').on('click', function() {
                    const search = $('#searchSatuan').val();
                    window.location.href = "{{ route('mastersatuan.export.excel') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportCsv').on('click', function() {
                    const search = $('#searchSatuan').val();
                    window.location.href = "{{ route('mastersatuan.export.csv') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportPdf').on('click', function() {
                    const search = $('#searchSatuan').val();
                    window.location.href = "{{ route('mastersatuan.export.pdf') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnPrint').on('click', function() {
                    const search = $('#searchSatuan').val();
                    window.open("{{ route('mastersatuan.print') }}?search=" + encodeURIComponent(search),
                        '_blank');
                });

                loadSatuan();
            });
        </script>
    @endpush

@endsection
