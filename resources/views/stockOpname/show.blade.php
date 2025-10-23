@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <style>
        #scanner video,
        #scanner canvas {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            /* biar memenuhi div tanpa gepeng */
        }
    </style>
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Tambah Stock Opname</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('stockOpname.index') }}" id="kembali" class="btn btn-secondary mb-3"
                        style="displa:none">Kembali</a>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kode_stock_opname">Kode Stock Opname</label>
                                <input type="text" id="kode_stock_opname" name="kode_stock_opname" class="form-control"
                                    value="{{ $stockOpname->kode_stock_opname ?? '' }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="periode">Periode</label>
                                <input type="date" id="periode" name="periode" class="form-control" required
                                    value="{{ $stockOpname->periode ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>

                    <table id="table-stockOpname-detail" class="table table-bordered" style="margin-top: 10px; width:100%">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Qty Stock</th>
                                <th>Qty Real</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>

                    <div class="modal fade" id="modal-scan" tabindex="-1" aria-labelledby="modalScanLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalScanLabel">Scan Barcode</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div id="scanner" style="width:100%; height:100px; background:#000;"></div>
                                    <!-- hasil scan -->
                                    <div class="form-group mt-3">
                                        <label for="barcode_result">Hasil Scan Barcode</label>
                                        <input type="text" id="barcode_result" class="form-control" readonly>
                                    </div>

                                    <!-- input qty -->
                                    <div class="form-group mt-3">
                                        <label for="qty_scan">Qty</label>
                                        <input type="number" id="qty_scan" class="form-control" min="0"
                                            value="0">
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                        <button type="button" class="btn btn-primary" id="btn-save-scan">Simpan</button>
                                    </div>
                                </div>

                            </div>
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

                $(document).on('click', '.btn-reset', function() {
                    let kode_barang = $(this).data('id');
                    let kode_stock_opname = $('#kode_stock_opname')
                        .val(); // pastikan ada hidden input atau variabel kode opname

                    Swal.fire({
                        title: 'Yakin?',
                        text: "Data stock opname detail akan dihapus!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('stockOpname.reset') }}",
                                type: "DELETE",
                                data: {
                                    kode_barang: kode_barang,
                                    kode_stock_opname: kode_stock_opname,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire(
                                            'Terhapus!',
                                            res.message,
                                            'success'
                                        );
                                        $('#table-stockOpname-detail').DataTable().ajax
                                            .reload();
                                    } else {
                                        Swal.fire(
                                            'Gagal!',
                                            'Data gagal dihapus.',
                                            'error'
                                        );
                                    }
                                },
                                error: function() {
                                    Swal.fire(
                                        'Error!',
                                        'Terjadi kesalahan server.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });


                // ketika tombol scan di klik
                $(document).on('click', '.btn-scan', function() {
                    let id = $(this).data('id');
                    let kode_barang = $(this).data('id'); // ambil dari attribute data-id
                    $('#modal-scan').modal('show');
                    $('#barcode_result').val('');
                    $('#qty_scan').val('');

                    let qty_scan = 0;
                    let scannedBarcodes = new Set(); // simpan hanya barcode unik


                    // aktifkan scanner
                    Quagga.init({
                            inputStream: {
                                name: "Live",
                                type: "LiveStream",
                                target: document.querySelector('#scanner'),
                                constraints: {
                                    width: 1280, // resolusi asli kamera
                                    height: 720,
                                    facingMode: "environment"
                                }
                            },
                            decoder: {
                                readers: ["ean_reader"]

                            }
                        },
                        function(err) {
                            if (err) {
                                console.log(err);
                                return;
                            }
                            Quagga.start();
                        }
                    );

                    // isi input ketika barcode terbaca
                    Quagga.onDetected(function(result) {
                        let code = result.codeResult.code;


                        // cek apakah barcode sudah pernah discan
                        if (!scannedBarcodes.has(code)) {
                            scannedBarcodes.add(code); // simpan barcode baru
                            qty_scan++; // increment hanya kalau baru
                            $('#qty_scan').val(qty_scan);
                            let current = $('#barcode_result').val();
                            if (current) {
                                $('#barcode_result').val(current + ',' + code);
                            } else {
                                $('#barcode_result').val(code);
                            }
                            // stop scanner
                            Quagga.stop();

                            // tunggu 5 detik lalu init ulang
                            setTimeout(() => {
                                Quagga.init({
                                    inputStream: {
                                        name: "Live",
                                        type: "LiveStream",
                                        target: document.querySelector(
                                            '#scanner'),
                                        constraints: {
                                            width: 1280,
                                            height: 720,
                                            facingMode: "environment"
                                        }
                                    },
                                    decoder: {
                                        readers: ["ean_reader"]
                                    }
                                }, function(err) {
                                    if (err) {
                                        console.log("Re-init error: ", err);
                                        return;
                                    }
                                    Quagga.start();
                                });
                            }, 5000);
                        }

                    });

                    // simpan hasil scan + qty
                    $('#btn-save-scan').off('click').on('click', function() {
                        let barcode = $('#barcode_result').val();
                        let qty = $('#qty_scan').val();
                        let kode_stock_opname = $('#kode_stock_opname').val();

                        if (barcode === "") {
                            Swal.fire('Oops!', 'Barcode belum terbaca.', 'warning');
                            return;
                        }

                        // TODO: kirim ke server
                        $.ajax({
                            url: "{{ route('stockOpname.scan') }}", // bikin route di controller
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                id: id,
                                barcode: barcode,
                                qty: qty,
                                kode_stock_opname: kode_stock_opname,
                                kode_barang: kode_barang
                            },
                            success: function(res) {
                                if (res.status == 'success') {
                                    Swal.fire('Berhasil!',
                                        'Data berhasil disimpan',
                                        'success');
                                    $('#modal-scan').modal('hide');
                                    $('#table-stockOpname-detail').DataTable()
                                        .ajax
                                        .reload();
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
                                    text: 'Terjadi kesalahan saat menyimpan data.' +
                                        error,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    });
                });

                // stop scanner kalau modal ditutup manual
                $('#modal-scan').on('hidden.bs.modal', function() {
                    if (Quagga.running) {
                        Quagga.stop();
                    }
                });

                if (!$.fn.DataTable.isDataTable(
                        '#table-stockOpname-detail')) {
                    let kode_stock_opname = $('#kode_stock_opname').val();
                    $('#table-stockOpname-detail').DataTable({
                        processing: true,
                        serverSide: true,
                        scrollX: true, // 🔥 ini penting untuk scroll horizontal
                        autowidth: false,
                        ajax: {
                            url: "{{ route('stockOpname.detail') }}",
                            data: function(d) {
                                d.kode_stock_opname =
                                    kode_stock_opname
                            }
                        },
                        columns: [{
                                data: 'kode_barang',
                                name: 'kode_barang'
                            },
                            {
                                data: 'nama_barang',
                                name: 'nama_barang'
                            },
                            {
                                data: 'qty_stock',
                                name: 'qty_stock'
                            },
                            {
                                data: 'qty_real',
                                name: 'qty_real'
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });
                } else {
                    $('#table-stockOpname-detail').DataTable().ajax
                        .reload();
                }




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






            });
        </script>
    @endpush

@endsection
