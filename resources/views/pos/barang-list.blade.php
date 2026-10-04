<input class="search form-control" placeholder="Cari barang...">

<div class="row list mt-4" style="max-height: 500px; overflow-y: auto;">


    @foreach ($barang3 as $group)
        <div class="col-md-4 mb-3">
            <div class="card h-100 shadow-sm">
                <img src="{{ asset('uploads/barang/' . ($group['gambar'] ?? 'default.png')) }}" class="card-img-top"
                    style="height:170px; object-fit:cover;">

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
                                    $stok = $barang->headerHargaJual->barang->stockReal->qty;
                                    $stok = floor($stok / $barang->KonversiSatuan->nilai_konversi);

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
                                <input type="radio" name="{{ $barang->headerHargaJual->barang->nama_barang }}"
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
                                Rp.
                                {{ number_format($barang->harga_jual, 0, ',', '.') }} |
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
