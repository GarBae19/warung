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
                            <a class="nav-link active" href="#">
                                Penjualan
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/pos/piutang') }}">
                                Bayar Piutang
                            </a>
                        </li>

                    </ul>
                    <div class="row">
                        <div class="col-md-8" style="border-right: 1px solid #ccc; padding-right: 15px;">
                            <div id="barang-list">
                                <input class="search form-control" placeholder="Cari barang...">

                                <div class="row list mt-4" style="max-height: 500px; overflow-y: auto;">


                                    @foreach ($barang3 as $group)
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 shadow-sm">
                                                <img src="{{ asset('uploads/barang/' . ($group['gambar'] ?? 'default.png')) }}"
                                                    class="card-img-top" style="height:170px; object-fit:cover;">

                                                <div class="card-body text-center p-2 d-flex flex-column barang">

                                                    <div style="height: 90px">
                                                        <strong>
                                                            {{ $group['nama_barang'] }}
                                                        </strong>
                                                    </div>

                                                    @foreach ($group['items'] as $barang)
                                                        @if ($barang->headerHargaJual->barang->stockReal != null)
                                                            @if ($barang->headerHargaJual->barang->stockReal->qty > 0)
                                                                @php
                                                                    $stok =
                                                                        $barang->headerHargaJual->barang->stockReal
                                                                            ->qty;
                                                                    $stok = floor(
                                                                        $stok / $barang->KonversiSatuan->nilai_konversi,
                                                                    );

                                                                @endphp
                                                            @else
                                                                @php
                                                                    $stok = 0;
                                                                @endphp
                                                            @endif
                                                        @else
                                                            @php
                                                                $stok = 0;
                                                            @endphp
                                                        @endif

                                                        @php
                                                            $disabled = $stok <= 0 ? 'disabled' : '';
                                                            $warna = $stok <= 0 ? 'danger' : '';
                                                        @endphp

                                                        <div style="text-align: left;">
                                                            <small class="text-{{ $warna }}">
                                                                <input type="radio"
                                                                    name="{{ $barang->headerHargaJual->barang->nama_barang }}"
                                                                    {{ $disabled }}
                                                                    data-idBarang="{{ encrypt($barang->headerHargaJual->id_barang) }}"
                                                                    data-nama="{{ $barang->headerHargaJual->barang->nama_barang }}"
                                                                    data-harga="{{ $barang->harga_jual }}"
                                                                    data-gambar="{{ $barang->headerHargaJual->barang->gambar ?? 'default.png' }}"
                                                                    data-nilaikonversi="{{ $barang->KonversiSatuan->nilai_konversi ?? 1 }}"
                                                                    data-idsatuan="{{ $barang->KonversiSatuan->id_satuan_konversi ?? $barang->KonversiSatuan->id_satuan_asal }}"
                                                                    data-namaSatuan = "{{ $barang->headerHargaJual->satuan->nama_satuan ?? '' }}"
                                                                    data-stock = "{{ $stok }}">

                                                                {{ $barang->headerHargaJual->satuan->nama_satuan }}
                                                                ({{ $barang->KonversiSatuan->nilai_konversi }})
                                                                Rp. {{ number_format($barang->harga_jual, 0, ',', '.') }} |
                                                                Stok: {{ number_format($stok, 0, ',', '.') }}
                                                            </small>
                                                        </div>

                                                        {{-- <small class="text-muted mb-2">
                                                            Stock {{ $stok }}
                                                        </small> --}}
                                                    @endforeach

                                                    <button class="btn btn-primary btn-sm pilihBarang">
                                                        Pilih
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h4 class="mb-3">Keranjang Belanja</h4>

                            <div class="mb-3"
                                style="border: 1px solid #ccc; padding: 10px; border-radius: 5px; height: 350px;">
                                <div style="max-height: 300px; overflow-y: auto;" id='listKeranjang'>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <h5>Total Belanja:</h5>
                                </div>
                                <div class="col-md-4 text-right">
                                    <h5>Rp.</h5>
                                </div>
                                <div class="col-md-4 text-right">
                                    <h5 id="total-belanja">0</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <h5>Piutang:</h5>
                                </div>
                                <div class="col-md-4 text-right">
                                    <input type="checkbox" id="piutang" class="form-check-input">
                                </div>
                            </div>
                            <div class="row" id="rowNamaPiutang" style="display:none">
                                <div class="col-md-4">
                                    <h5>Nama Piutang:</h5>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" id="namaPiutang" class="form-control form-control-sm"
                                        placeholder="Nama Piutang">
                                </div>
                            </div>
                            <div class="row" id="rowBayar">
                                <div class="col-md-4">
                                    <h5>Bayar:</h5>
                                </div>
                                <div class="col-md-4 text-right">
                                    <h5>Rp.</h5>
                                </div>
                                <div class="col-md-4 text-right">
                                    <input type="text" id="bayar" class="form-control form-control-sm text-right"
                                        placeholder="Bayar" readonly>
                                </div>
                            </div>
                            <div class="row" id="rowKembali">
                                <div class="col-md-4">
                                    <h5>Kembali:</h5>
                                </div>
                                <div class="col-md-4 text-right">
                                    <h5>Rp.</h5>
                                </div>
                                <div class="col-md-4 text-right">
                                    <h5 id="kembali">0</h5>
                                </div>
                            </div>
                            <button class="btn btn-success" id="prosesPembayaran">Proses Pembayaran</button>
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

                $('#piutang').change(function() {
                    if ($(this).is(':checked')) {
                        $('#rowBayar').hide();
                        $('#rowKembali').hide();
                        $('#namaPiutang').show();
                        $('#rowNamaPiutang').show();
                    } else {
                        $('#rowBayar').show();
                        $('#rowKembali').show();
                        $('#namaPiutang').hide();
                        $('#rowNamaPiutang').hide();
                    }
                });

                $(document).on('click', '.pilihBarang', function() {
                    // ambil radio yang dipilih dalam group ini
                    let selected = $(this).closest('.card, .modal, div').find('input[type=radio]:checked');

                    if (selected.length === 0) {
                        alert('Pilih barang terlebih dahulu!');
                        return;
                    }
                    var idBarang = selected.data('idbarang');
                    var namaBarang = selected.data('nama');
                    var hargaBarang = selected.data('harga');
                    var gambarBarang = selected.data('gambar');
                    var nilaiKonversi = selected.data('nilaikonversi');
                    var idSatuan = selected.data('idsatuan');
                    var namaSatuan = selected.data('namasatuan');
                    var stockBarang = selected.data('stock');

                    const baseUrl = "{{ url('/') }}";

                    // Cek apakah item sudah ada berdasarkan namaBarang
                    let existingItem = $('#listKeranjang').find(
                        `.item-keranjang[data-idbarang="${idBarang}"]`);

                    if (existingItem.length > 0) {
                        // Jika sudah ada, tambahkan jumlahnya
                        let qtyInput = existingItem.find('.jumlah-barang');
                        let currentQty = parseInt(qtyInput.val());
                        qtyInput.val(currentQty + 1);
                    } else {
                        // Jika belum ada, buat elemen baru
                        let itemBaru = `
                                <div class="card mb-2 shadow-sm item-keranjang" data-idbarang="${idBarang}">
                                    <div class="card-body py-2">
                                        <div class="row align-items-center">
                                            <div class="col-2 text-center">
                                                <img src="${baseUrl}/uploads/barang/${gambarBarang}"
                                                    alt="Gambar Barang"
                                                    class="img-fluid rounded"
                                                    style="max-height: 50px;">
                                            </div>

                                            <div class="col-6">
                                                <strong class="text-clamp">${namaBarang} (${namaSatuan})</strong>
                                                <br>
                                                <small class="text-muted">
                                                    Rp. ${Number(hargaBarang).toLocaleString('id-ID')}
                                                </small>
                                                <small class="text-muted">
                                                    Stok: ${stockBarang}
                                                </small>
                                            </div>

                                            <div class="col-4 d-flex justify-content-end align-items-center gap-2">
                                                <input type="number"
                                                    class="form-control jumlah-barang text-center"
                                                    value="1"
                                                    min="1"
                                                    max="${stockBarang}"
                                                    data-harga="${hargaBarang}"
                                                    style="width: 70px;"
                                                    oninput="validateQuantity(this)">
                                                <input type="hidden" class="nilai-konversi" value="${nilaiKonversi}">
                                                <input type="hidden" class="id-satuan" value="${idSatuan}">
                                                <button class="btn btn-danger btn-sm btn-hapus" style="height: 38px;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            `;
                        $('#listKeranjang').append(itemBaru);
                    }

                    updateTotal(); // Update total setiap kali item ditambah
                });

                function validateQuantity(input) {
                    let val = parseInt($(input).val());
                    let max = parseInt($(input).attr('max'));
                    if (isNaN(val) || val < 1) {
                        $(input).val(1);
                    } else if (val > max) {
                        $(input).val(max);
                    }
                }

                // Event untuk tombol hapus
                $(document).on('click', '.btn-hapus', function() {
                    $(this).closest('.item-keranjang').remove();
                    updateTotal();
                });

                // Event untuk update total saat jumlah barang diubah
                $(document).on('input', '.jumlah-barang', function() {
                    validateQuantity(this);
                    updateTotal();
                });

                // Fungsi update total belanja
                function updateTotal() {
                    let total = 0;
                    $('#listKeranjang .item-keranjang').each(function() {
                        let harga = parseInt($(this).find('.jumlah-barang').data('harga'));
                        let qty = parseInt($(this).find('.jumlah-barang').val());
                        total += harga * qty;
                    });
                    $('#total-belanja').text(total.toLocaleString('id-ID'));
                    $('#bayar').attr('readonly', false);
                }



                $('#bayar').on('input', function() {
                    // Ambil input dan hapus semua non-digit
                    let bayarRaw = $(this).val().replace(/\D/g, '');

                    // Simpan sebagai string untuk formatting
                    let bayarStr = bayarRaw === '' ? '0' : bayarRaw;

                    // Format ribuan
                    $(this).val(Number(bayarStr).toLocaleString('id-ID'));

                    // Hitung total belanja
                    let total = 0;
                    $('#listKeranjang .item-keranjang').each(function() {
                        let harga = parseInt($(this).find('.jumlah-barang').data('harga')) || 0;
                        let qty = parseInt($(this).find('.jumlah-barang').val()) || 0;
                        total += harga * qty;
                    });

                    // Hitung kembalian
                    let kembali = BigInt(bayarStr) - BigInt(total);
                    $('#kembali').text(Number(kembali).toLocaleString('id-ID'));
                });

                $('#prosesPembayaran').on('click', function() {
                    let totalBelanja = 0;
                    let items = [];

                    $('#listKeranjang .item-keranjang').each(function() {
                        let idBarang = $(this).data('idbarang');
                        let harga = parseInt($(this).find('.jumlah-barang').data('harga'));
                        let qty = parseInt($(this).find('.jumlah-barang').val());
                        let nilaiKonversi = parseInt($(this).find('.nilai-konversi').val());
                        let idSatuan = parseInt($(this).find('.id-satuan').val());
                        totalBelanja += harga * qty;

                        items.push({
                            idBarang: idBarang,
                            harga: harga,
                            qty: qty,
                            nilaiKonversi: nilaiKonversi,
                            idSatuan: idSatuan
                        });
                    });

                    let bayarRaw = $('#bayar').val().replace(/\D/g, '');
                    let bayar = BigInt(bayarRaw);

                    if (items.length === 0) {
                        alert('Keranjang belanja kosong!');
                        return;
                    }

                    if (!$('#piutang').is(':checked') && bayar < BigInt(totalBelanja)) {
                        alert('Jumlah bayar tidak mencukupi!');
                        return;
                    }

                    if ($('#piutang').is(':checked') && $('#namaPiutang').val().trim() === '') {
                        alert('Nama piutang harus diisi!');
                        return;
                    }
                    // if (bayar < BigInt(totalBelanja)) {
                    //     alert('Jumlah bayar tidak mencukupi!');
                    //     return;
                    // }

                    // Proses pembayaran (misalnya simpan ke database)
                    let piutang = $('#piutang').is(':checked') ? 1 : 0
                    let namaPiutang = $('#namaPiutang').val().trim();
                    // alert(piutang);
                    swal.fire({
                        title: 'Proses Pembayaran',
                        text: 'Loading...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            swal.showLoading();
                        }
                    })

                    $.ajax({
                        url: "{{ route('pos.prosesPembayaran') }}",
                        method: 'POST',
                        data: {
                            items: items,
                            total: totalBelanja,
                            bayar: bayar.toString(),
                            piutang: piutang,
                            namaPiutang: namaPiutang
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                // alert(response.message);
                                swal.fire({
                                    title: 'Pembayaran Berhasil',
                                    text: response.message,
                                    icon: 'success'
                                });

                                // Tampilkan struk di modal
                                let kode_pos = response.kode_pos;
                                let kembali = bayar - BigInt(totalBelanja);
                                let pdfUrl = "{{ url('pos/strukPos') }}" + "?kode_pos=" +
                                    kode_pos +
                                    "&bayar=" + bayar.toString() + "&total=" + totalBelanja +
                                    '&kembali=' + kembali.toString();
                                $('#strukIframe').attr('src', pdfUrl);
                                const iframe = document.getElementById('strukIframe');

                                iframe.contentWindow.focus();
                                iframe.contentWindow.print();
                                $('#modal-struk').modal('show');

                                // Reset keranjang
                                $('#listKeranjang').empty();
                                updateTotal();
                                $('#bayar').val('');
                                $('#bayar').attr('readonly', true);
                                $('#kembali').text('0');

                                $('#barang-list').load("{{ url('/barang-list') }}", function() {

                                    var options = {
                                        valueNames: ['barang']
                                    };

                                    var barangList = new List('barang-list', options);

                                });

                            } else {
                                // alert('Pembayaran gagal: ' + response.message);
                                swal.fire({
                                    title: 'Pembayaran Gagal',
                                    text: response.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            // alert('Terjadi kesalahan saat memproses pembayaran.');
                            swal.fire({
                                title: 'Error',
                                text: 'Terjadi kesalahan saat memproses pembayaran.',
                                icon: 'error'
                            });
                        }
                    });
                });

                $('#btnPrintStruk').on('click', function() {
                    document.getElementById('strukIframe')
                        .contentWindow.print();
                });


            });

            document.addEventListener("DOMContentLoaded", function() {
                var options = {
                    valueNames: ['barang']
                };
                var barangList = new List('barang-list', options);
            });
        </script>
    @endpush
@endsection
