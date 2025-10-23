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
                    <h3 class="card-title mb-0">Master Stock</h3>
                    <div class="ml-auto">
                        <a href="{{ route('stockOpname.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Data
                        </a>
                    </div>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                    <table id="dt-stock" class="table table-bordered table-striped">
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
                    </table>
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
                        url: `/masterbarang/${id}`, // langsung aja
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
                        url: `/masterbarang/${id}`, // langsung aja
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
                                        $('#dt-stock').DataTable().ajax.reload(null,
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
                                type: 'POST',
                                data: {
                                    _method: 'POST',
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire('Berhasil', res.message, 'success');
                                        $('#dt-stock').DataTable().ajax.reload(null,
                                            false);
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


                var table = $("#dt-stock").DataTable({
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
                            filename: 'data_stock',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'excel',
                            filename: 'data_stock',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'pdf',
                            filename: 'data_stock',
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
                    ajax: "{{ route('stockOpname.index') }}",
                    columns: [{
                            data: null,
                            name: 'no',
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                            className: 'text-center',
                        },
                        {
                            data: 'kode_stock_opname',
                            name: 'kode_stock_opname'
                        },
                        {
                            data: 'periode',
                            name: 'periode'
                        },
                        {
                            data: 'approve_by',
                            name: 'approve_by'
                        },
                        {
                            data: 'approve_at',
                            name: 'approve_at'
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

                table.buttons().container().appendTo('#dt-stock_wrapper .col-md-6:eq(0)');
            });
        </script>
    @endpush

@endsection
