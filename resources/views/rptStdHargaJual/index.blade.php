@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Report Std Harga Jual</h3>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                    <table id="dt-std-harga-jual" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode Std Harga Jual</th>
                                <th>Item Kode</th>
                                <th>Item Nama</th>
                                <th>Cabang</th>
                                <th>Standar Harga Beli</th>
                                <th>Standar Harga Jual</th>
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
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });




                var table = $("#dt-std-harga-jual").DataTable({
                    dom: 'Bfrtip', // <<< penting!
                    responsive: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    lengthChange: false,
                    autoWidth: false,
                    processing: true, // indikator loading
                    // serverSide: true, // aktifkan server-side
                    buttons: [{
                            extend: 'csv',
                            filename: 'data_std_harga_jual',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'excel',
                            filename: 'data_std_harga_jual',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            }
                        },
                        {
                            extend: 'pdf',
                            filename: 'data_std_harga_jual',
                            exportOptions: {
                                columns: ':not(.not-export)'
                            },
                            customize: function(doc) {
                                doc.footer = function(currentPage, pageCount) {
                                    return {
                                        text: 'Halaman ' + currentPage + ' dari ' + pageCount,
                                        alignment: 'center',
                                        margin: [0, 10, 0, 0]
                                    };
                                };


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
                    ajax: "{{ url('rptStdHargaJual') }}",
                    columns: [{
                            data: null,
                            name: 'no',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            },
                            className: 'text-center',
                        },
                        {
                            data: 'kode_std_harga_jual',
                            name: 'kode_std_harga_jual'
                        },
                        {
                            data: 'item_kode',
                            name: 'item_kode'
                        },
                        {
                            data: 'item_nama',
                            name: 'item_nama'
                        },
                        {
                            data: 'cabang',
                            name: 'cabang'
                        },
                        {
                            data: 'standar_harga_beli',
                            name: 'standar_harga_beli'
                        },
                        {
                            data: 'standar_harga_jual',
                            name: 'standar_harga_jual'
                        }
                    ],
                });

                table.buttons().container().appendTo('#dt-std-harga-jual_wrapper .col-md-6:eq(0)');
            });
        </script>
    @endpush

@endsection
