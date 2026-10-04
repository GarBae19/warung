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
                    <table id="dt-cabang" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Cabang</th>
                                <th>Nama Cabang</th>
                                <th>No Telp</th>
                                <th>No Hp</th>
                                <th>Email</th>
                                <th>Fax</th>
                                <th>Alamat Cabang</th>
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
                <form id="form-tambah-cabang">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTambahLabel">Tambah {{ $atribute }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="formMode" value="create"> <!-- atau 'edit' -->
                            <input type="hidden" id="idCabang"> <!-- atau 'edit' -->
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="kode_cabang">Kode Cabang</label>
                                    <input type="text" class="form-control" id="kode_cabang" name="kode_cabang">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="nama_cabang">Nama Cabang</label>
                                    <input type="text" class="form-control" id="nama_cabang" name="nama_cabang">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="no_telp">No Telp</label>
                                    <input type="number" class="form-control" id="no_telp" name="no_telp">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="no_hp">No Hp</label>
                                    <input type="number" class="form-control" id="no_hp" name="no_hp">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="email">Email</label>
                                    <input type="text" class="form-control" id="email" name="email">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="fax">Fax</label>
                                    <input type="text" class="form-control" id="fax" name="fax">
                                </div>
                            </div>


                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="alamat">Alamat Cabang</label>
                                    <textarea type="text" class="form-control" id="alamat" name="alamat"></textarea>
                                </div>
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
                    $('#nama_cabang').val('');
                    $('#no_telp').val('');
                    $('#no_hp').val('');
                    $('#email').val('');
                    $('#fax').val('');
                    $('#alamat').val('');
                    $.ajax({
                        url: "{{ url('mastercabang-kode') }}",
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_cabang').val(res.kodeCabang);
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
                        url: `mastercabang/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_cabang').val(res.data.kode_cabang);
                                $('#nama_cabang').val(res.data.nama_cabang);
                                $('#no_telp').val(res.data.no_telp);
                                $('#no_hp').val(res.data.no_hp);
                                $('#email').val(res.data.email);
                                $('#fax').val(res.data.fax);
                                $('#alamat').val(res.data.alamat);
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
                    $('#idCabang').val(id);
                    $.ajax({
                        url: `mastercabang/${id}`, // langsung aja
                        method: 'get',
                        success: function(res) {
                            if (res.status == 'success') {
                                $('#kode_cabang').val(res.data.kode_cabang);
                                $('#nama_cabang').val(res.data.nama_cabang);
                                $('#no_telp').val(res.data.no_telp);
                                $('#no_hp').val(res.data.no_hp);
                                $('#email').val(res.data.email);
                                $('#fax').val(res.data.fax);
                                $('#alamat').val(res.data.alamat);
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
                    $('#form-tambah-cabang').submit();
                });

                $('#btnUpdate').on('click', function() {
                    $('#formMode').val('update');
                    $('#form-tambah-cabang').submit();
                });


                $('#form-tambah-cabang').on('submit', function(e) {
                    e.preventDefault(); // Cegah submit default

                    const form = this;

                    let mode = $('#formMode').val(); // 'create' atau 'edit'

                    // alert(mode);
                    let url = '';
                    let method = 'POST';

                    if (mode === 'create') {
                        url = "{{ route('mastercabang.store') }}";
                    } else if (mode === 'update') {
                        let idCabang = $('#idCabang').val();
                        url = "{{ url('mastercabang') }}/" + idCabang;
                        // method = 'PUT';
                    }

                    const kodeCabang = $('#kode_cabang').val();
                    const namaCabang = $('#nama_cabang').val();

                    if (!kodeCabang || !namaCabang) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: 'Cabang gagal disimpan. Pastikan Kode dan Nama Cabang diisi.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }


                    const validation = validateFormCabang();

                    if (!validation.valid) {
                        Swal.fire({
                            title: 'Validasi Gagal!',
                            text: validation.message,
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }



                    Swal.fire({
                        title: mode === 'create' ? 'Simpan Cabang?' : 'Update Cabang?',
                        text: 'Pastikan data sudah benar.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: mode === 'create' ? 'Ya, Simpan' : 'Ya, Update',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Di sini kamu bisa kirim data ke server pakai AJAX
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
                                            text: 'Cabang berhasil disimpan.',
                                            icon: 'success',
                                            confirmButtonText: 'OK'
                                        });

                                        // Kalau pakai DataTables, refresh:
                                        $('#dt-cabang').DataTable().ajax
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
                    });
                });

                $(document).on('click', '.btn-delete', function() {
                    const id = $(this).data('id');

                    Swal.fire({
                        title: 'Hapus Cabang?',
                        text: 'Data tidak bisa dikembalikan setelah dihapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `mastercabang/${id}`,
                                type: 'POST',
                                data: {
                                    _method: 'DELETE',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        $('#dt-cabang').DataTable().ajax.reload(null,
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


                var table = $("#dt-cabang").DataTable({
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
                            filename: 'data_cabang',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'excel',
                            filename: 'data_cabang',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'pdf',
                            filename: 'data_cabang',
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
                    ajax: "{{ url('mastercabang') }}",
                    columns: [{
                            data: null,
                            name: 'no',
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                            className: 'text-center',
                        },
                        {
                            data: 'kode_cabang',
                            name: 'kode_cabang'
                        },
                        {
                            data: 'nama_cabang',
                            name: 'nama_cabang'
                        },
                        {
                            data: 'no_telp',
                            name: 'no_telp'
                        },
                        {
                            data: 'no_hp',
                            name: 'no_hp'
                        },
                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'fax',
                            name: 'fax'
                        },
                        {
                            data: 'alamat',
                            name: 'alamat'
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

                table.buttons().container().appendTo('#dt-cabang_wrapper .col-md-6:eq(0)');


                function validatePhone(noTelp) {
                    const phoneRegex = /^0[0-9]{8,14}$/;
                    return phoneRegex.test(noTelp);
                }

                function validateNoHp(noHp) {
                    const phoneRegex = /^0[0-9]{8,14}$/;
                    return phoneRegex.test(noHp);
                }

                // Validasi email
                function validateEmail(email) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return emailRegex.test(email);
                }

                // Validasi lengkap (mengembalikan true/false dan pesan error)
                function validateFormCabang() {
                    const noTelp = $('#no_telp').val().trim();
                    const noHp = $('#no_hp').val().trim();
                    const email = $('#email').val().trim();

                    // Validasi nomor telepon (jika diisi)
                    if (noTelp !== '' && !validatePhone(noTelp)) {
                        return {
                            valid: false,
                            message: 'Format nomor Telp tidak valid. '
                        };
                    }

                    // Validasi nomor telepon (jika diisi)
                    if (noHp !== '' && !validateNoHp(noHp)) {
                        return {
                            valid: false,
                            message: 'Format nomor Hp tidak valid.'
                        };
                    }

                    // Validasi email (jika diisi)
                    if (email !== '' && !validateEmail(email)) {
                        return {
                            valid: false,
                            message: 'Format email tidak valid. Contoh: nama@email.com.'
                        };
                    }

                    return {
                        valid: true
                    };
                }
            });
        </script>
    @endpush

@endsection
