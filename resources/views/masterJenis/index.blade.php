@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Master Jenis</h3>
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
                            <input type="text" id="searchJenis" class="form-control form-control-sm"
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

                    <table class="table table-bordered table-striped" id="tabel-jenis">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Jenis</th>
                                <th>Nama Jenis</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-jenis">
                            <!-- diisi via JS -->
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center">
                        <div id="infoJenis" class="text-muted small"></div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="pagination-jenis"></ul>
                        </nav>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </section>

        <div class="modal fade" id="modal-tambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="form-tambah-jenis">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahLabel">Tambah Jenis Barang</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="formMode" value="create"> <!-- atau 'edit' -->
                            <input type="hidden" id="idJenis"> <!-- atau 'edit' -->
                            <div class="form-group">
                                <label for="kode_jenis">Kode Jenis Barang</label>
                                <input type="text" class="form-control" id="kode_jenis" name="kode_jenis" required>
                            </div>
                            <div class="form-group">
                                <label for="nama_jenis">Nama Jenis Barang</label>
                                <input type="text" class="form-control" id="nama_jenis" name="nama_jenis" required>
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
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $('#tambahData').on('click', function(e) {
                    $('#btnBatal').show();
                    $('#btnSimpan').show();
                    $('#btnUpdate').hide();
                    $('#nama_jenis').val('');
                    $.ajax({
                        url: "{{ url('masterjenisbarang-kode') }}",
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_jenis').val(res.kodeJenis);
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
                        url: `jenisBarang/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_jenis').val(res.data.kode_jenis);
                                $('#nama_jenis').val(res.data.nama_jenis);
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
                    $('#idJenis').val(id);
                    $.ajax({
                        url: `jenisBarang/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_jenis').val(res.data.kode_jenis);
                                $('#nama_jenis').val(res.data.nama_jenis);
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
                    $('#form-tambah-jenis').submit();
                });

                $('#btnUpdate').on('click', function() {
                    $('#formMode').val('update');
                    $('#form-tambah-jenis').submit();
                });


                $('#form-tambah-jenis').on('submit', function(e) {
                    e.preventDefault(); // Cegah submit default

                    const form = this;

                    let mode = $('#formMode').val(); // 'create' atau 'edit'

                    // alert(mode);
                    let url = '';
                    let method = 'POST';

                    if (mode === 'create') {
                        url = "{{ route('jenisBarang.store') }}";
                    } else if (mode === 'update') {
                        let idJenis = $('#idJenis').val();
                        url = "{{ url('jenisBarang') }}/" + idJenis;
                        // method = 'PUT';
                    }

                    Swal.fire({
                        title: mode === 'create' ? 'Simpan Jenis?' : 'Update Jenis?',
                        text: 'Pastikan data sudah benar.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: mode === 'create' ? 'Ya, Simpan' : 'Ya, Update',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Di sini kamu bisa kirim data ke server pakai AJAX
                            const kodeJenis = $('#kode_jenis').val();
                            const namaJenis = $('#nama_jenis').val();

                            if (!kodeJenis || !namaJenis) {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: 'Jenis Barang gagal disimpan. Pastikan semua terisi.',
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
                                    //     kode_jenis: kodeJenis,
                                    //     nama_jenis: namaJenis
                                    // },
                                    success: function(res) {
                                        if (res.status == 'success') {
                                            $('#modal-tambah').modal('hide');
                                            form.reset();

                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: 'Jenis berhasil disimpan.',
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            });

                                            // Kalau pakai DataTables, refresh:
                                            loadJenis();
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
                        title: 'Hapus Jenis?',
                        text: 'Data tidak bisa dikembalikan setelah dihapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `jenisBarang/${id}`,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        loadJenis();
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

                function loadJenis(page = 1) {
                    currentPage = page;
                    const search = $('#searchJenis').val();
                    const perPage = $('#perPage').val();

                    $.ajax({
                        url: "{{ url('/jenisBarang') }}",
                        method: 'get',
                        data: {
                            page,
                            search,
                            per_page: perPage
                        },
                        beforeSend: function() {
                            $('#tbody-jenis').html(
                                '<tr><td colspan="4" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderTable(res.data, res.from);
                            renderPagination(res);
                            $('#infoJenis').text(
                                `Menampilkan ${res.from ?? 0} - ${res.to ?? 0} dari ${res.total} data`);
                        },
                        error: function() {
                            $('#tbody-jenis').html(
                                '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderTable(data, startNumber) {
                    if (!data.length) {
                        $('#tbody-jenis').html('<tr><td colspan="4" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }

                    let rows = '';
                    data.forEach((jenis, i) => {
                        rows += `
            <tr>
                <td class="text-center">${startNumber + i}</td>
                <td>${jenis.kode_jenis}</td>
                <td>${jenis.nama_jenis}</td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button class="dropdown-item btn-view" data-id="${jenis.id_encrypted}">View</button>
                            ${!jenis.is_used ? `
                                                                                                                <button class="dropdown-item btn-edit" data-id="${jenis.id_encrypted}">Edit</button>
                                                                                                                <button type="button" class="dropdown-item text-danger btn-delete" data-id="${jenis.id}">Delete</button>
                                                                                                            ` : ''}
                        </div>
                    </div>
                </td>
            </tr>`;
                    });
                    $('#tbody-jenis').html(rows);
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

                    $('#pagination-jenis').html(links);
                }

                $(document).on('click', '#pagination-jenis a', function(e) {
                    e.preventDefault();
                    loadJenis($(this).data('page'));
                });

                $('#searchJenis').on('keyup', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => loadJenis(1), 400);
                });

                $('#perPage').on('change', function() {
                    loadJenis(1);
                });

                $('#btnExportExcel').on('click', function() {
                    const search = $('#searchJenis').val();
                    window.location.href = "{{ route('jenisBarang.export.excel') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportCsv').on('click', function() {
                    const search = $('#searchJenis').val();
                    window.location.href = "{{ route('jenisBarang.export.csv') }}?search=" + encodeURIComponent(
                        search);
                });

                $('#btnExportPdf').on('click', function() {
                    const search = $('#searchJenis').val();
                    window.location.href = "{{ route('jenisBarang.export.pdf') }}?search=" + encodeURIComponent(
                        search);
                });

                $('#btnPrint').on('click', function() {
                    const search = $('#searchJenis').val();
                    window.open("{{ route('jenisBarang.print') }}?search=" + encodeURIComponent(search),
                        '_blank');
                });



                loadJenis();

            });
        </script>
    @endpush

@endsection
