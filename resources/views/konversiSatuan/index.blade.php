@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Konversi Satuan</h3>
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
                        <button id="tambahData" type="button" class="btn btn-primary btn-sm mr-2" data-toggle="modal"
                            data-target="#modal-tambah">
                            <i class="fas fa-plus"></i> Tambah Data
                        </button>
                        <button id="listBelumTerkonversi" type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                            data-target="#modal-listBelumTerkonversi">
                            <i class="fas fa-list"></i> List Belum Terkonversi
                        </button>
                    </div>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="form-inline">
                            <input type="text" id="searchKonversi" class="form-control form-control-sm"
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

                    <table class="table table-bordered table-striped" id="tabel-konversi">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Nilai Konversi</th>
                                <th>Satuan Asal</th>
                                <th>Satuan Konversi</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-konversi">
                            <!-- diisi via JS -->
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center">
                        <div id="infoKonversi" class="text-muted small"></div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="pagination-konversi"></ul>
                        </nav>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </section>

        <div class="modal fade" id="modal-tambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="form-tambah-konversi-satuan" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahLabel">Tambah Barang</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="formMode" value="create"> <!-- atau 'edit' -->
                            <input type="hidden" id="idKonversi"> <!-- atau 'edit' -->
                            <div class="form-group">
                                <label for="select2-barang">Pilih Barang</label>
                                <select id="select2-barang" name="select2-barang" style="width:100%; height:38px"></select>
                            </div>
                            <div class="form-group">
                                <label for="satuan">Satuan Asal</label>
                                <input type="text" class="form-control" id="satuan" name="satuan" readonly>
                                <input type="hidden" class="form-control" id="id_satuan_asal" name="id_satuan_asal"
                                    readonly>
                                <input type="hidden" class="form-control" id="id_satuan_asal2" name="id_satuan_asal2"
                                    readonly>
                            </div>
                            <div class="form-group">
                                <label for="nilai_konversi">Nilai Konversi</label>
                                <input type="number" class="form-control" id="nilai_konversi" name="nilai_konversi"
                                    min="1" required>
                            </div>
                            <div class="form-group">
                                <label for="select2-satuan">Pilih Satuan Konversi</label>
                                <select id="select2-satuan" name="select2-satuan"
                                    style="width:100%; height:38px"></select>
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

        <div class="modal fade" id="modal-listBelumTerkonversi" tabindex="-1" role="dialog"
            aria-labelledby="modalListBelumTerkonversiLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalListBelumTerkonversiLabel">List Barang Belum Terkonversi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex justify-content-between mb-2">
                            <input type="text" id="searchBelumTerkonversi" class="form-control form-control-sm w-25"
                                placeholder="Cari...">
                        </div>
                        <table id="dt-listBelumTerkonversi" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-belumTerkonversi">
                                <!-- diisi via JS -->
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between align-items-center">
                            <div id="infoBelumTerkonversi" class="text-muted small"></div>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="pagination-belumTerkonversi"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
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

                $('#select2-barang').select2({
                    placeholder: 'Cari data...',
                    dropdownParent: $('#modal-tambah'), // ini sangat penting di modal Bootstrap
                    ajax: {
                        url: "{{ route('getDataBarangKonversiSatuan') }}", // sesuaikan dengan route kamu
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

                $('#select2-barang').on('select2:select', function(e) {
                    let data = e.params.data;
                    let text = data.text;

                    // pecah berdasarkan " - "
                    let satuan = text.split(' - ')[1];

                    console.log(satuan);

                    $('#satuan').val(satuan);

                    // alert(data.id_satuan2); // 👈 id_satuan bisa langsung dipakai

                    // contoh isi input hidden
                    $('#id_satuan_asal').val(data.id_satuan);
                    $('#id_satuan_asal2').val(data.id_satuan2);
                });

                $('#select2-satuan').select2({
                    // let satuan = $('#satuan').val();
                    placeholder: 'Cari data...',
                    dropdownParent: $('#modal-tambah'), // ini sangat penting di modal Bootstrap
                    ajax: {
                        url: "{{ route('getSatuanKonversi') }}", // sesuaikan dengan route kamu
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                id_satuan_asal: $('#id_satuan_asal').val() // keyword pencarian
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


                $('#tambahData').on('click', function(e) {
                    $('#btnBatal').show();
                    $('#btnSimpan').show();
                    $('#btnUpdate').hide();
                    $('#nilai_konversi').val('');
                    $('#satuan').val('');
                    $('#id_satuan_asal').val('');
                    $('#id_satuan_asal2').val('');
                    let newOptionBarang = new Option('', '', true, true);
                    $('#select2-barang').append(newOptionBarang).trigger('change');

                    let newOptionSatuan = new Option('', '', true, true);
                    $('#select2-satuan').append(newOptionSatuan).trigger('change');
                })

                $(document).on('click', '.btn-view', function() {
                    const id = $(this).data('id');
                    // alert(id);
                    $.ajax({
                        url: `konversi_satuan/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#nilai_konversi').val(res.data.nilai_konversi);
                                $('#satuan').val(res.data.satuan_asal.nama_satuan);
                                $('#id_satuan_asal').val(res.data.satuan_asal.id);
                                $('#id_satuan_asal2').val(res.data.satuan_asal.id);


                                let newOptionBarang = new Option(res.data.barang.nama_barang,
                                    res.data.barang.id, true, true);
                                $('#select2-barang').append(newOptionBarang).trigger('change');

                                $('#select2-barang').on('select2:opening select2:closing', function(
                                    e) {
                                    e.preventDefault(); // block opening/closing
                                });

                                let newOptionSatuan = new Option(res.data.satuan_konversi
                                    .nama_satuan,
                                    res.data.satuan_konversi.id, true, true);
                                $('#select2-satuan').append(newOptionSatuan).trigger('change');

                                $('#select2-satuan').on('select2:opening select2:closing', function(
                                    e) {
                                    e.preventDefault(); // block opening/closing
                                });

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
                    $('#idKonversi').val(id);
                    $.ajax({
                        url: `konversi_satuan/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#nilai_konversi').val(res.data.nilai_konversi);
                                $('#satuan').val(res.data.satuan_asal.nama_satuan);
                                $('#id_satuan_asal').val(res.id_satuan_asal);
                                $('#id_satuan_asal2').val(res.id_satuan_asal);

                                let newOptionBarang = new Option(res.data.barang.nama_barang,
                                    res.data.barang.id, true, true);
                                $('#select2-barang').append(newOptionBarang).trigger('change');

                                $('#select2-barang').on('select2:opening select2:closing', function(
                                    e) {
                                    e.preventDefault(); // block opening/closing
                                });

                                let newOptionSatuan = new Option(res.data.satuan_konversi
                                    .nama_satuan,
                                    res.data.satuan_konversi.id, true, true);
                                $('#select2-satuan').append(newOptionSatuan).trigger('change');
                                $('#select2-satuan').off('select2:opening select2:closing');


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
                    $('#form-tambah-konversi-satuan').submit();
                });

                $('#btnUpdate').on('click', function() {
                    $('#formMode').val('update');
                    $('#form-tambah-konversi-satuan').submit();
                });


                $('#form-tambah-konversi-satuan').on('submit', function(e) {
                    e.preventDefault(); // Cegah submit default

                    const form = this;

                    let mode = $('#formMode').val(); // 'create' atau 'edit'

                    // alert(mode);
                    let url = '';
                    let method = 'POST';

                    if (mode === 'create') {
                        url = "{{ route('konversi_satuan.store') }}";
                    } else if (mode === 'update') {
                        let idKonversi = $('#idKonversi').val();
                        url = "{{ url('konversi_satuan') }}/" + idKonversi;
                        // method = 'PUT';
                    }

                    Swal.fire({
                        title: mode === 'create' ? 'Simpan Barang?' : 'Update Barang?',
                        text: 'Pastikan data sudah benar.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: mode === 'create' ? 'Ya, Simpan' : 'Ya, Update',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Di sini kamu bisa kirim data ke server pakai AJAX
                            const id_barang = $('#select2-barang').val();
                            const id_satuan_asal = $('#id_satuan_asal2').val();
                            const nilai_konversi = $('#nilai_konversi').val();
                            const id_satuan_konversi = $('#select2-satuan').val();

                            if (!id_barang && !id_satuan_konversi && !nilai_konversi) {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: 'Barang gagal disimpan.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });

                            } else {
                                let formData = new FormData();

                                formData.append('_token', '{{ csrf_token() }}');
                                formData.append('_method', mode === 'update' ? 'PUT' : 'POST');
                                formData.append('id_barang', id_barang);
                                formData.append('id_satuan_asal', id_satuan_asal);
                                formData.append('nilai_konversi', nilai_konversi);
                                formData.append('id_satuan_konversi', id_satuan_konversi);

                                $.ajax({
                                    url: url,
                                    method: method,
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    success: function(res) {
                                        if (res.status == 'success') {
                                            $('#modal-tambah').modal('hide');
                                            form.reset();

                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: 'Barang berhasil disimpan.',
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            });

                                            // Kalau pakai DataTables, refresh:
                                            $('#dt-konversi').DataTable().ajax
                                                .reload(null,
                                                    false);
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
                        title: 'Hapus Konversi?',
                        text: 'Data tidak bisa dikembalikan setelah dihapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `konversi_satuan/${id}`,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        $('#dt-konversi').DataTable().ajax.reload(null,
                                            false);
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

                function loadKonversi(page = 1) {
                    currentPage = page;
                    const search = $('#searchKonversi').val();
                    const perPage = $('#perPage').val();

                    $.ajax({
                        url: "{{ url('/konversi_satuan') }}",
                        method: 'get',
                        data: {
                            page,
                            search,
                            per_page: perPage
                        },
                        beforeSend: function() {
                            $('#tbody-konversi').html(
                                '<tr><td colspan="7" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderTable(res.data, res.from);
                            renderPagination(res);
                            $('#infoKonversi').text(
                                `Menampilkan ${res.from ?? 0} - ${res.to ?? 0} dari ${res.total} data`);
                        },
                        error: function() {
                            $('#tbody-konversi').html(
                                '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderTable(data, startNumber) {
                    if (!data.length) {
                        $('#tbody-konversi').html('<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }

                    let rows = '';
                    data.forEach((konversi, i) => {
                        const bisaDiedit = !konversi.dipakai_sebagai_asal && konversi.id_satuan_konversi !=
                            null;
                        rows += `
    <tr>
        <td class="text-center">${startNumber + i}</td>
        <td>${konversi.kode_barang}</td>
        <td>${konversi.nama_barang}</td>
        <td class="text-center">${konversi.nilai_konversi}</td>
        <td>${konversi.satuan_asal_nama}</td>
        <td>${konversi.satuan_konversi_nama}</td>
        <td class="text-center">
            <div class="dropdown">
                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-cog"></i>
                </button>
                <div class="dropdown-menu">
                    <button class="dropdown-item btn-view" data-id="${konversi.id_encrypted}">View</button>
                    ${bisaDiedit ? `
                                                                                <button class="dropdown-item btn-edit" data-id="${konversi.id_encrypted}">Edit</button>
                                                                                <button type="button" class="dropdown-item text-danger btn-delete" data-id="${konversi.id}">Delete</button>
                                                                            ` : ''}
                </div>
            </div>
        </td>
    </tr>`;
                    });
                    $('#tbody-konversi').html(rows);
                }

                function renderPagination(res) {
                    let links = '';
                    const current = res.current_page;
                    const last = res.last_page;

                    // Tombol Previous
                    if (res.prev_page_url) {
                        links +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${current - 1}">&laquo;</a></li>`;
                    } else {
                        links += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
                    }

                    // Hitung range nomor yang ditampilkan (current ± 2)
                    const delta = 2;
                    let range = [];

                    for (let p = 1; p <= last; p++) {
                        if (p === 1 || p === last || (p >= current - delta && p <= current + delta)) {
                            range.push(p);
                        }
                    }

                    // Sisipkan "..." untuk nomor yang di-skip
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

                    // Tombol Next
                    if (res.next_page_url) {
                        links +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${current + 1}">&raquo;</a></li>`;
                    } else {
                        links += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
                    }

                    $('#pagination-konversi').html(links); // ganti id sesuai halaman masing-masing
                }

                $(document).on('click', '#pagination-konversi a', function(e) {
                    e.preventDefault();
                    loadKonversi($(this).data('page'));
                });

                $('#searchKonversi').on('keyup', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => loadKonversi(1), 400);
                });

                $('#perPage').on('change', function() {
                    loadKonversi(1);
                });

                $('#btnExportExcel').on('click', function() {
                    const search = $('#searchKonversi').val();
                    window.location.href = "{{ route('konversi_satuan.export.excel') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportCsv').on('click', function() {
                    const search = $('#searchKonversi').val();
                    window.location.href = "{{ route('konversi_satuan.export.csv') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportPdf').on('click', function() {
                    const search = $('#searchKonversi').val();
                    window.location.href = "{{ route('konversi_satuan.export.pdf') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnPrint').on('click', function() {
                    const search = $('#searchKonversi').val();
                    window.open("{{ route('konversi_satuan.print') }}?search=" + encodeURIComponent(search),
                        '_blank');
                });

                // ==== Modal: List Belum Terkonversi ====

                let currentPageBelum = 1;
                let searchTimeoutBelum = null;

                function loadBelumTerkonversi(page = 1) {
                    currentPageBelum = page;
                    const search = $('#searchBelumTerkonversi').val();

                    $.ajax({
                        url: "{{ url('/list_belum_terkonversi') }}",
                        method: 'get',
                        data: {
                            page,
                            search
                        },
                        beforeSend: function() {
                            $('#tbody-belumTerkonversi').html(
                                '<tr><td colspan="4" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderTableBelum(res.data, res.from);
                            renderPaginationBelum(res);
                            $('#infoBelumTerkonversi').text(
                                `Menampilkan ${res.from ?? 0} - ${res.to ?? 0} dari ${res.total} data`);
                        },
                        error: function() {
                            $('#tbody-belumTerkonversi').html(
                                '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderTableBelum(data, startNumber) {
                    if (!data.length) {
                        $('#tbody-belumTerkonversi').html(
                            '<tr><td colspan="4" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }

                    let rows = '';
                    data.forEach((item, i) => {
                        rows += `
    <tr>
        <td class="text-center">${startNumber + i}</td>
        <td>${item.kode_barang}</td>
        <td>${item.nama_barang}</td>
        <td>${item.satuan_asal_nama}</td>
    </tr>`;
                    });
                    $('#tbody-belumTerkonversi').html(rows);
                }

                function renderPaginationBelum(res) {

                    let links = '';
                    const current = res.current_page;
                    const last = res.last_page;

                    // Tombol Previous
                    if (res.prev_page_url) {
                        links +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${current - 1}">&laquo;</a></li>`;
                    } else {
                        links += `<li class="page-item disabled"><span class="page-link">&laquo;</span></li>`;
                    }

                    // Hitung range nomor yang ditampilkan (current ± 2)
                    const delta = 2;
                    let range = [];

                    for (let p = 1; p <= last; p++) {
                        if (p === 1 || p === last || (p >= current - delta && p <= current + delta)) {
                            range.push(p);
                        }
                    }

                    // Sisipkan "..." untuk nomor yang di-skip
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

                    // Tombol Next
                    if (res.next_page_url) {
                        links +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${current + 1}">&raquo;</a></li>`;
                    } else {
                        links += `<li class="page-item disabled"><span class="page-link">&raquo;</span></li>`;
                    }

                    $('#pagination-belumTerkonversi').html(links); // ganti id sesuai halaman masing-masing
                }

                $(document).on('click', '#pagination-belumTerkonversi a', function(e) {
                    e.preventDefault();
                    loadBelumTerkonversi($(this).data('page'));
                });

                $('#searchBelumTerkonversi').on('keyup', function() {
                    clearTimeout(searchTimeoutBelum);
                    searchTimeoutBelum = setTimeout(() => loadBelumTerkonversi(1), 400);
                });

                $("#modal-listBelumTerkonversi").on('show.bs.modal', function() {
                    loadBelumTerkonversi(1);
                });

                loadKonversi();
            });
        </script>
    @endpush

@endsection
