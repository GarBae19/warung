@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Master Brand</h3>
                    <div class="ml-auto">
                        <button id="tambahData" type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#modal-tambah">
                            <i class="fas fa-plus"></i> Tambah Data
                        </button>
                    </div>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                    <table id="dt-brand" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Brand</th>
                                <th>Nama Brand</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
        </section>

        <div class="modal fade" id="modal-tambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="form-tambah-brand">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahLabel">Tambah Brand</h5>
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
                            <input type="hidden" id="idBrand"> <!-- atau 'edit' -->
                            <div class="form-group">
                                <label for="kode_brand">Kode Brand</label>
                                <input type="text" class="form-control" id="kode_brand" name="kode_brand" required>
                            </div>
                            <div class="form-group">
                                <label for="nama_brand">Nama Brand</label>
                                <input type="text" class="form-control" id="nama_brand" name="nama_brand" required>
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

        <div class="modal fade" id="modal-import-excel" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="form-import-brand" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalImportLabel">Import Brand</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-end">
                                <a href="{{ asset('template_excel/template_brand.xlsx') }}"
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

                $('#tambahData').on('click', function(e) {
                    $('#btnBatal').show();
                    $('#btnSimpan').show();
                    $('#btnUpdate').hide();
                    $('#nama_brand').val('');
                    $.ajax({
                        url: "{{ url('masterbrand-kode') }}",
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_brand').val(res.kodeBrand);
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
                        url: `/masterbrand/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_brand').val(res.data.kode_brand);
                                $('#nama_brand').val(res.data.nama_brand);
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
                    $('#idBrand').val(id);
                    $.ajax({
                        url: `/masterbrand/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_brand').val(res.data.kode_brand);
                                $('#nama_brand').val(res.data.nama_brand);
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
                    $('#form-tambah-brand').submit();
                });

                $('#btnUpdate').on('click', function() {
                    $('#formMode').val('update');
                    $('#form-tambah-brand').submit();
                });


                $('#form-tambah-brand').on('submit', function(e) {
                    e.preventDefault(); // Cegah submit default

                    const form = this;

                    let mode = $('#formMode').val(); // 'create' atau 'edit'

                    // alert(mode);
                    let url = '';
                    let method = 'POST';

                    if (mode === 'create') {
                        url = "{{ route('masterbrand.store') }}";
                    } else if (mode === 'update') {
                        let idBrand = $('#idBrand').val();
                        url = "{{ url('masterbrand') }}/" + idBrand;
                        // method = 'PUT';
                    }

                    Swal.fire({
                        title: mode === 'create' ? 'Simpan Brand?' : 'Update Brand?',
                        text: 'Pastikan data sudah benar.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: mode === 'create' ? 'Ya, Simpan' : 'Ya, Update',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Di sini kamu bisa kirim data ke server pakai AJAX
                            const kodeBrand = $('#kode_brand').val();
                            const namaBrand = $('#nama_brand').val();

                            if (!kodeBrand && !namaBrand) {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: 'Brand gagal disimpan.',
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
                                    //     kode_brand: kodeBrand,
                                    //     nama_brand: namaBrand
                                    // },
                                    success: function(res) {
                                        if (res.status == 'success') {
                                            $('#modal-tambah').modal('hide');
                                            form.reset();

                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: 'Brand berhasil disimpan.',
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            });

                                            // Kalau pakai DataTables, refresh:
                                            $('#dt-brand').DataTable().ajax
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
                        title: 'Hapus Brand?',
                        text: 'Data tidak bisa dikembalikan setelah dihapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/masterbrand/${id}`,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        $('#dt-brand').DataTable().ajax.reload(null, false);
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

                $('#form-import-brand').on('submit', function(e) {
                    e.preventDefault();

                    let formData = new FormData(this);
                    $('#btnSimpanImport').prop('disabled', true).html(
                        '<i class="fas fa-spinner fa-spin"></i> Mengimpor...');

                    $.ajax({
                        url: "{{ route('masterBrand.importExcel') }}",
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
                                $('#form-import-brand')[0].reset();
                                $('#dt-satuan').DataTable().ajax
                                    .reload(null,
                                        false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: res.message
                                });
                                $('#dt-satuan').DataTable().ajax
                                    .reload(null,
                                        false);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message ||
                                    'Terjadi kesalahan saat import.'
                            });
                            $('#dt-satuan').DataTable().ajax
                                .reload(null,
                                    false);
                        },
                        complete: function() {
                            $('#btnSimpanImport').prop('disabled', false).html('Simpan');
                        }
                    });
                });


                var table = $("#dt-brand").DataTable({
                    dom: 'Bfrtip', // <<< penting!
                    responsive: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    lengthChange: false,
                    autoWidth: false,
                    processing: true, // indikator loading
                    serverSide: true, // aktifkan server-side
                    buttons: [{
                            extend: 'csv',
                            filename: 'data_brand',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'excel',
                            filename: 'data_brand',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'pdf',
                            filename: 'data_brand',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            },
                            customize: function(doc) {
                                var body = doc.content[1].table.body;

                                // Nomor urut + teks center
                                for (var i = 1; i < body.length; i++) {
                                    if (typeof body[i][0] === 'object') {
                                        body[i][0].text = (i).toString();
                                    } else {
                                        body[i][0] = {
                                            text: (i).toString()
                                        };
                                    }

                                    body[i][0].alignment = 'center';
                                }

                                body[0][0].alignment = 'center'; // Header nomor urut center

                                // Atur lebar kolom otomatis
                                doc.content[1].table.widths = Array(body[0].length).fill(
                                    '*');

                                // Tambahkan garis pembatas
                                doc.content[1].layout = {
                                    hLineWidth: function() {
                                        return 0.5;
                                    },
                                    vLineWidth: function() {
                                        return 0.5;
                                    },
                                    hLineColor: function() {
                                        return '#aaa';
                                    },
                                    vLineColor: function() {
                                        return '#aaa';
                                    },
                                };
                            }
                        },
                        {
                            extend: 'print',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                    ],
                    ajax: "{{ url('/masterbrand') }}",
                    columns: [{
                            data: null,
                            name: 'no',
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                            className: 'text-center',
                        },
                        {
                            data: 'kode_brand',
                            name: 'kode_brand'
                        },
                        {
                            data: 'nama_brand',
                            name: 'nama_brand'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-center not-export'
                        }
                    ],
                });

                table.buttons().container().appendTo('#dt-brand_wrapper .col-md-6:eq(0)');
            });
        </script>
    @endpush

@endsection
