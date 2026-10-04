@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">{{ $atribute }}</h3>
                    <div class="ml-auto">
                        <button id="tambahData" type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#modal-tambah">
                            <i class="fas fa-plus"></i> Tambah Data
                        </button>
                    </div>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                    <table id="dt-gudang" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Gudang</th>
                                <th>Nama Gudang</th>
                                <th>Cabang</th>
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
            <div class="modal-dialog modal-xl" role="document">
                <form id="form-tambah-gudang">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahLabel">Tambah {{ $atribute }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="formMode" value="create"> <!-- atau 'edit' -->
                            <input type="hidden" id="idGudang"> <!-- atau 'edit' -->
                            <div class="form-group">
                                <label for="kode_gudang">Kode Gudang</label>
                                <input type="text" class="form-control" id="kode_gudang" name="kode_gudang">
                            </div>
                            <div class="form-group">
                                <label for="nama_gudang">Nama Gudang</label>
                                <input type="text" class="form-control" id="nama_gudang" name="nama_gudang">
                            </div>
                            <div class="form-group">
                                <label for="id_cabang">Cabang</label>
                                <select id="id_cabang" name="id_cabang" style="width:100%; height:38px"></select>
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

                $('#id_cabang').select2({
                    placeholder: 'Cari data...',
                    dropdownParent: $('#modal-tambah'), // ini sangat penting di modal Bootstrap
                    ajax: {
                        url: "{{ route('getCabang') }}", // sesuaikan dengan route kamu
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
                    $('#nama_gudang').val('');
                    $.ajax({
                        url: "{{ url('mastergudang-kode') }}",
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_gudang').val(res.kodeGudang);
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
                        url: `mastergudang/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_gudang').val(res.data.kode_gudang);
                                $('#nama_gudang').val(res.data.nama_gudang);
                                let newOptionCabang = new Option(res.data.cabang.nama_cabang,
                                    res.data.id_cabang, true, true);
                                $('#id_cabang').append(newOptionCabang).trigger('change');
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
                    $('#idGudang').val(id);
                    $.ajax({
                        url: `mastergudang/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_gudang').val(res.data.kode_gudang);
                                $('#nama_gudang').val(res.data.nama_gudang);
                                let newOptionCabang = new Option(res.data.cabang.nama_cabang,
                                    res.data.id_cabang, true, true);
                                $('#id_cabang').append(newOptionCabang).trigger('change');

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
                    $('#form-tambah-gudang').submit();
                });

                $('#btnUpdate').on('click', function() {
                    $('#formMode').val('update');
                    $('#form-tambah-gudang').submit();
                });


                $('#form-tambah-gudang').on('submit', function(e) {
                    e.preventDefault(); // Cegah submit default

                    const form = this;

                    let mode = $('#formMode').val(); // 'create' atau 'edit'

                    // alert(mode);
                    let url = '';
                    let method = 'POST';

                    if (mode === 'create') {
                        url = "{{ route('mastergudang.store') }}";
                    } else if (mode === 'update') {
                        let idGudang = $('#idGudang').val();
                        url = "{{ url('mastergudang') }}/" + idGudang;
                        // method = 'PUT';
                    }


                    Swal.fire({
                        title: mode === 'create' ? 'Simpan Gudang?' : 'Update Gudang?',
                        text: 'Pastikan data sudah benar.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: mode === 'create' ? 'Ya, Simpan' : 'Ya, Update',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Di sini kamu bisa kirim data ke server pakai AJAX
                            const kodeGudang = $('#kode_gudang').val();
                            const namaGudang = $('#nama_gudang').val();

                            if (!kodeGudang || !namaGudang) {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: 'Gudang gagal disimpan. Pastikan Kode dan Nama Gudang diisi.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                                return;
                            } else {
                                $.ajax({
                                    url: url,
                                    method: method,
                                    data: $(form).serialize() + (mode === 'update' ?
                                        '&_method=PUT' : ''),
                                    // data: {
                                    //     kode_jenis: kodeJenis,
                                    //     nama_jenis: namaSatuan
                                    // },
                                    success: function(res) {
                                        if (res.status == 'success') {
                                            $('#modal-tambah').modal('hide');
                                            form.reset();

                                            Swal.fire({
                                                title: 'Berhasil!',
                                                text: 'Gudang berhasil disimpan.',
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            });

                                            // Kalau pakai DataTables, refresh:
                                            $('#dt-gudang').DataTable().ajax
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
                        title: 'Hapus Gudang?',
                        text: 'Data tidak bisa dikembalikan setelah dihapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `mastergudang/${id}`,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        $('#dt-gudang').DataTable().ajax.reload(null,
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


                var table = $("#dt-gudang").DataTable({
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
                            filename: 'data_gudang',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'excel',
                            filename: 'data_gudang',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'pdf',
                            filename: 'data_gudang',
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
                    ajax: "{{ url('/mastergudang') }}",
                    columns: [{
                            data: null,
                            name: 'no',
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                            className: 'text-center',
                        },
                        {
                            data: 'kode_gudang',
                            name: 'kode_gudang'
                        },
                        {
                            data: 'nama_gudang',
                            name: 'nama_gudang'
                        },
                        {
                            data: 'cabang',
                            name: 'cabang'
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

                table.buttons().container().appendTo('#dt-gudang_wrapper .col-md-6:eq(0)');

            });
        </script>
    @endpush

@endsection
