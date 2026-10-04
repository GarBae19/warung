@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')

    <style>
        .card-body {
            display: flex;
            flex-direction: column;
        }

        .card-title {
            min-height: 40px;
        }

        .card-text {
            min-height: 20px;
        }

        .card .btn {
            margin-top: auto;
        }

        .text-clamp {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                {{-- <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">POS (Point Of Sales)</h3>
                </div> --}}

                <!-- /.card-header -->
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/pos') }}">
                                Penjualan
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                Bayar Piutang
                            </a>
                        </li>
                    </ul>
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                                <input type="text" id="daterange" class="form-control"
                                    placeholder="Pilih Rentang Tanggal">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-sm" id="btn-filter">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered" id="piutangTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Nama Pelanggan</th>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Total Piutang</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    {{-- @endforeach --}}
                </div>
            </div>

            <div class="modal fade" id="modal-struk" height="400px">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Print Struk</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <iframe id="strukIframe" style="width:100%; height:100%; border:none;"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button id="btnPrintStruk" class="btn btn-success">
                                Print
                            </button>
                        </div>

                    </div>
                </div>
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

                let piutangTable;

                $('#daterange').daterangepicker({
                    autoUpdateInput: false,
                    opens: 'right',
                    locale: {
                        cancelLabel: 'Batal',
                        applyLabel: 'Terapkan',
                        format: 'DD/MM/YYYY',
                        separator: ' - ',
                        daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ],
                    }
                });

                $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(
                        picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY')
                    );
                });

                $('#btn-clear-date').on('click', function() {
                    $('#daterange').val('');
                    if (piutangTable) piutangTable.ajax.reload();
                });

                $('#btn-filter').on('click', function() {
                    if (!$('#daterange').val()) {
                        Swal.fire('Perhatian', 'Pilih rentang tanggal terlebih dahulu!', 'warning');
                        return;
                    }

                    if ($.fn.DataTable.isDataTable('#piutangTable')) {
                        piutangTable.ajax.reload();
                    } else {
                        initTable();
                    }
                });

                initTable();

                function initTable() {
                    piutangTable = $('#piutangTable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('pos.dataPiutang') }}",
                            type: 'GET',
                            data: function(d) {
                                var picker = $('#daterange').data('daterangepicker');
                                if ($('#daterange').val()) {
                                    d.start_date = picker.startDate.format('YYYY-MM-DD');
                                    d.end_date = picker.endDate.format('YYYY-MM-DD');
                                }
                            }, // ✅ Koma yang hilang sudah ditambahkan
                        },

                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'nama_pelanggan',
                                name: 'nama_pelanggan'
                            },
                            {
                                data: 'tanggal',
                                name: 'tanggal'
                            },
                            {
                                data: 'total_piutang',
                                name: 'total_piutang',
                            },
                            {
                                data: 'aksi',
                                name: 'aksi',
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });

                    piutangTable.buttons().container()
                        .appendTo('#piutangTable_wrapper .col-md-6:eq(0)');
                }

                $('#piutangTable').on('click', '.bayar-piutang', function() {
                    var debitur = $(this).data('debitur');
                    var totalPiutang = $(this).data('total');

                    Swal.fire({
                        title: 'Bayar Piutang',
                        html: `
                            <p>Debitur: <strong>${debitur}</strong></p>
                            <p>Total Piutang: <strong>${totalPiutang}</strong></p>
                            <input type="number" id="jumlahBayar" class="swal2-input" placeholder="Jumlah Bayar">
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Bayar',
                        preConfirm: () => {
                            const jumlahBayar = Swal.getPopup().querySelector('#jumlahBayar').value;
                            if (!jumlahBayar || jumlahBayar <= 0) {
                                Swal.showValidationMessage('Masukkan jumlah bayar yang valid');
                                return false;
                            }
                            return jumlahBayar;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var jumlahBayar = result.value;

                            // Lakukan AJAX untuk memproses pembayaran
                            $.ajax({
                                url: "{{ route('pos.prosesPembayaranPiutang') }}",
                                method: 'POST',
                                data: {
                                    debitur: debitur,
                                    jumlah_bayar: jumlahBayar
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('Sukses', response.message, 'success');
                                        piutangTable.ajax.reload();
                                    } else {
                                        Swal.fire('Error', response.message, 'error');
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire('Error',
                                        'Terjadi kesalahan saat memproses pembayaran.',
                                        'error');
                                }
                            });
                        }
                    });
                });


            });
        </script>
    @endpush
@endsection
