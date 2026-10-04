@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">

                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Master Barang</h3>
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
                            <input type="text" id="searchBarang" class="form-control form-control-sm"
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

                    <table class="table table-bordered table-striped" id="tabel-barang">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Jenis Barang</th>
                                <th>Brand</th>
                                <th>Min Stock</th>
                                <th>Satuan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-barang">
                            <!-- diisi via JS -->
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center">
                        <div id="infoBarang" class="text-muted small"></div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="pagination-barang"></ul>
                        </nav>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </section>

        <div class="modal fade" id="modal-tambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="form-tambah-barang" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahLabel">Tambah Barang</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="formMode" value="create"> <!-- atau 'edit' -->
                            <input type="hidden" id="idBarang"> <!-- atau 'edit' -->
                            <div class="form-group">
                                <label for="kode_barang">Kode Barang</label>
                                <input type="text" class="form-control" id="kode_barang" name="kode_barang" required>
                            </div>
                            <div class="form-group">
                                <label for="nama_barang">Nama Barang</label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                            </div>
                            <div class="form-group">
                                <label for="select2-jenisBarang">Jenis Barang</label>
                                <select id="select2-jenisBarang" name="select2-jenisBarang"
                                    style="width:100%; height:38px"></select>
                            </div>
                            <div class="form-group">
                                <label for="select2-brand">Brand</label>
                                <select id="select2-brand" name="select2-brand" style="width:100%; height:38px"></select>
                            </div>
                            <div class="form-group">
                                <label for="select2-satuan">Satuan</label>
                                <select id="select2-satuan" name="select2-satuan"
                                    style="width:100%; height:38px"></select>
                            </div>
                            <div class="form-group">
                                <label for="min_stock">Min Stok</label>
                                <input type="number" class="form-control" id="min_stock" name="min_stock" required>
                            </div>
                            <div class="form-group">
                                <label for="gambar">Gambar</label>
                                <input type="file" class="form-control" id="gambar" name="gambar">
                                <input type="hidden" class="form-control" id="gambarLama" name="gambarLama">
                                <img id="previewGambar" src="" alt="Preview Gambar"
                                    style="max-width: 100px; margin-top: 10px; display: none;">
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

                $('#select2-jenisBarang').select2({
                    placeholder: 'Cari data...',
                    dropdownParent: $('#modal-tambah'), // ini sangat penting di modal Bootstrap
                    ajax: {
                        url: "{{ route('getDataJenisBarang') }}", // sesuaikan dengan route kamu
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

                $('#select2-brand').select2({
                    placeholder: 'Cari data...',
                    dropdownParent: $('#modal-tambah'), // ini sangat penting di modal Bootstrap
                    ajax: {
                        url: "{{ route('getBrand') }}", // sesuaikan dengan route kamu
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

                $('#select2-satuan').select2({
                    placeholder: 'Cari data...',
                    dropdownParent: $('#modal-tambah'), // ini sangat penting di modal Bootstrap
                    ajax: {
                        url: "{{ route('getSatuan') }}", // sesuaikan dengan route kamu
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


                $('#tambahData').on('click', function(e) {
                    $('#btnBatal').show();
                    $('#btnSimpan').show();
                    $('#btnUpdate').hide();
                    $('#nama_barang').val('');
                    $('#min_stock').val('');
                    $('#gambar').val('');
                    let newOptionJenis = new Option('', '', true, true);
                    $('#select2-jenisBarang').append(newOptionJenis).trigger('change');

                    let newOptionBrand = new Option('', '', true, true);
                    $('#select2-brand').append(newOptionBrand).trigger('change');

                    let newOptionSatuan = new Option('', '', true, true);
                    $('#select2-satuan').append(newOptionSatuan).trigger('change');
                    $.ajax({
                        url: "{{ url('masterbarang-kode') }}",
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_barang').val(res.kodeBarang);
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
                        url: `masterbarang/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                let min_stock = res.data.min_stock ? res.data.min_stock : 0;
                                const baseUrl = "{{ url('/') }}";

                                $('#kode_barang').val(res.data.kode_barang);
                                $('#nama_barang').val(res.data.nama_barang);
                                $('#min_stock').val(min_stock);
                                $('#gambarLama').val(res.data.gambar);
                                $('#previewGambar').attr('src', baseUrl + '/uploads/barang/' + res
                                    .data
                                    .gambar);
                                if (res.data.gambar) {
                                    $('#previewGambar').show();
                                } else {
                                    $('#previewGambar').hide();
                                }

                                let newOptionJenis = new Option(res.data.jenis_barang.nama_jenis,
                                    res.data.id_jenis_barang, true, true);
                                $('#select2-jenisBarang').append(newOptionJenis).trigger('change');

                                let newOptionBrand = new Option(res.data.brand.nama_brand,
                                    res.data.id_brand, true, true);
                                $('#select2-brand').append(newOptionBrand).trigger('change');

                                let newOptionSatuan = new Option(res.data.satuan.nama_satuan,
                                    res.data.id_satuan, true, true);
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
                                const baseUrl = "{{ url('/') }}";

                                $('#kode_barang').val(res.data.kode_barang);
                                $('#nama_barang').val(res.data.nama_barang);
                                $('#min_stock').val(res.data.min_stock);
                                $('#gambarLama').val(res.data.gambar);
                                $('#previewGambar').attr('src', baseUrl + '/uploads/barang/' + res
                                    .data
                                    .gambar);
                                if (res.data.gambar) {
                                    $('#previewGambar').show();
                                } else {
                                    $('#previewGambar').hide();
                                }

                                let newOptionJenis = new Option(res.data.jenis_barang.nama_jenis,
                                    res.data.id_jenis_barang, true, true);
                                $('#select2-jenisBarang').append(newOptionJenis).trigger('change');

                                let newOptionBrand = new Option(res.data.brand.nama_brand,
                                    res.data.id_brand, true, true);
                                $('#select2-brand').append(newOptionBrand).trigger('change');

                                let newOptionSatuan = new Option(res.data.satuan.nama_satuan,
                                    res.data.id_satuan, true, true);
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

                $('#btnSimpan').on('click', function() {
                    $('#formMode').val('create');
                    $('#form-tambah-barang').submit();
                });

                $('#btnUpdate').on('click', function() {
                    $('#formMode').val('update');
                    $('#form-tambah-barang').submit();
                });


                $('#form-tambah-barang').on('submit', function(e) {
                    e.preventDefault(); // Cegah submit default

                    const form = this;

                    let mode = $('#formMode').val(); // 'create' atau 'edit'

                    // alert(mode);
                    let url = '';
                    let method = 'POST';

                    if (mode === 'create') {
                        url = "{{ route('masterbarang.store') }}";
                    } else if (mode === 'update') {
                        let idBarang = $('#idBarang').val();
                        url = "{{ url('masterbarang') }}/" + idBarang;
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
                            const kodeBarang = $('#kode_barang').val();
                            const namaBarang = $('#nama_barang').val();
                            const id_jenis_barang = $('#select2-jenisBarang').val();
                            const id_brand = $('#select2-brand').val();
                            const id_satuan = $('#select2-satuan').val();
                            const min_stock = $('#min_stock').val();
                            const gambar = $('#gambar').val();

                            if (!kodeBarang && !namaBarang && !id_jenis_barang && !id_brand && !
                                id_satuan && !min_stock && !gambar) {
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
                                formData.append('kode_barang', kodeBarang);
                                formData.append('nama_barang', namaBarang);
                                formData.append('id_jenis_barang', id_jenis_barang);
                                formData.append('id_brand', id_brand);
                                formData.append('id_satuan', id_satuan);
                                formData.append('min_stock', min_stock);

                                let gambar = $('#gambar')[0].files[0];
                                if (gambar) {
                                    formData.append('gambar', gambar);
                                }
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

                                            loadBarang();
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
                        title: 'Hapus Barang?',
                        text: 'Data tidak bisa dikembalikan setelah dihapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `masterbarang/${id}`,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        loadBarang();
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

                function loadBarang(page = 1) {
                    currentPage = page;
                    const search = $('#searchBarang').val();
                    const perPage = $('#perPage').val();

                    $.ajax({
                        url: "{{ url('/masterbarang') }}",
                        method: 'get',
                        data: {
                            page,
                            search,
                            per_page: perPage
                        },
                        beforeSend: function() {
                            $('#tbody-barang').html(
                                '<tr><td colspan="8" class="text-center">Memuat data...</td></tr>');
                        },
                        success: function(res) {
                            renderTable(res.data, res.from);
                            renderPagination(res);
                            $('#infoBarang').text(
                                `Menampilkan ${res.from ?? 0} - ${res.to ?? 0} dari ${res.total} data`);
                        },
                        error: function() {
                            $('#tbody-barang').html(
                                '<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>'
                            );
                        }
                    });
                }

                function renderTable(data, startNumber) {
                    if (!data.length) {
                        $('#tbody-barang').html('<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>');
                        return;
                    }

                    let rows = '';
                    data.forEach((barang, i) => {
                        rows += `
            <tr>
                <td class="text-center">${startNumber + i}</td>
                <td>${barang.kode_barang}</td>
                <td>${barang.nama_barang}</td>
                <td>${barang.jenis_barang_nama}</td>
                <td>${barang.brand_nama}</td>
                <td>${barang.min_stock ?? '-'}</td>
                <td>${barang.satuan_nama}</td>
                <td class="text-center">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button class="dropdown-item btn-view" data-id="${barang.id_encrypted}">View</button>
                            ${!barang.is_used ? `
                                                                                                                                <button class="dropdown-item btn-edit" data-id="${barang.id_encrypted}">Edit</button>
                                                                                                                                <button type="button" class="dropdown-item text-danger btn-delete" data-id="${barang.id}">Delete</button>
                                                                                                                            ` : ''}
                        </div>
                    </div>
                </td>
            </tr>`;
                    });
                    $('#tbody-barang').html(rows);
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

                    $('#pagination-barang').html(links);
                }

                $(document).on('click', '#pagination-barang a', function(e) {
                    e.preventDefault();
                    loadBarang($(this).data('page'));
                });

                $('#searchBarang').on('keyup', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => loadBarang(1), 400);
                });

                $('#perPage').on('change', function() {
                    loadBarang(1);
                });

                // panggil di awal
                loadBarang();

                $('#btnExportExcel').on('click', function() {
                    const search = $('#searchBarang').val();
                    window.location.href = "{{ route('masterbarang.export.excel') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportCsv').on('click', function() {
                    const search = $('#searchBarang').val();
                    window.location.href = "{{ route('masterbarang.export.csv') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnExportPdf').on('click', function() {
                    const search = $('#searchBarang').val();
                    window.location.href = "{{ route('masterbarang.export.pdf') }}?search=" +
                        encodeURIComponent(search);
                });

                $('#btnPrint').on('click', function() {
                    const search = $('#searchBarang').val();
                    window.open("{{ route('masterbarang.print') }}?search=" + encodeURIComponent(search),
                        '_blank');
                });
            });
        </script>
    @endpush

@endsection
