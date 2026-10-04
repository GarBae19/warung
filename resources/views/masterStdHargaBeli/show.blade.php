@extends('layouts.tamplate')

@section('title', 'Home')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Master Std Harga Beli</h3>
                </div>

                <!-- /.card-header -->
                <div class="card-body">
                    @if ($mode == 'view')
                        <a href="{{ route('masterStdHargaBeli.index') }}" id="kembali"
                            class="btn btn-secondary mb-3">Kembali</a>
                    @endif

                    <form action="{{ route('masterStdHargaBeli.update', $idEncpted) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_std_harga_beli">Kode STD Harga Beli</label>
                                    <input type="text" id="kode_std_harga_beli" name="kode_std_harga_beli"
                                        class="form-control" value="{{ $dataHeader->kode_std_harga_beli ?? '' }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_barang">Barang</label>
                                    <select id="id_barang" name="id_barang" style="width:100%; height:38px" required
                                        {{ $mode == 'view' ? 'readonly' : '' }}></select>

                                    <input type="hidden" id="id_satuan" name="id_satuan"
                                        value="{{ $dataHeader->id_satuan }}">
                                    <input type="hidden" id="id_barang2" name="id_barang2"
                                        value="{{ $dataHeader->id_barang }}">
                                </div>
                            </div>
                        </div>
                        <table id="table-std-harga-beli" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="70%" style="text-align: center">Cabang</th>
                                    <th style="text-align: center">Harga Beli</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cabangs as $cabang)
                                    <tr>
                                        <td>{{ $cabang->nama_cabang }}</td>
                                        <td>
                                            <input type="text" class="form-control" style="text-align:right"
                                                name="harga_beli[{{ $cabang->id }}]"
                                                value="{{ old('harga_beli.' . $cabang->id, number_format($dataDetail[$cabang->id]['harga_beli'] ?? 0, 0, ',', '.')) }}"
                                                required {{ $mode == 'view' ? 'readonly' : '' }}
                                                oninput="formatInputRibuan(this)">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>


                        <div class="d-grid gap-2 mt-3" id="button_submit_header"
                            style="{{ $mode == 'view' ? 'display:none' : '' }}">
                            <a href="{{ route('masterStdHargaBeli.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>


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

                $('#id_barang').select2({
                    placeholder: 'Cari data...',
                    ajax: {
                        url: "{{ route('getDataBarangStdHarga') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term
                            };
                        },
                        processResults: function(data) {
                            // Sertakan 'satuan' di object select2
                            return {
                                results: data.map(function(item) {
                                    return {
                                        id: item.id + '_' + item.id_satuan,
                                        text: item.text,
                                        id_barang: item.id_barang,
                                        id_satuan: item.id_satuan
                                    };
                                })
                            };
                        },
                        cache: true
                    }
                });

                // Event saat barang dipilih
                $('#id_barang').on('select2:select', function(e) {
                    var data = e.params.data; // data yang dipilih
                    $('#id_satuan').val(data.id_satuan); // masukkan satuan ke input
                    $('#id_barang2').val(data.id_barang); // masukkan id barang ke input
                });

                @if (!empty($dataHeader->id_barang))
                    // Set default value saat edit/detail

                    var barangId = "{{ $dataHeader->id_barang }}" +
                        "_{{ $dataHeader->id_satuan ?? '' }}"; // gabungkan id_barang dan id_satuan
                    var barangNama = "{{ $dataHeader->barang->nama_barang ?? '' }}" +
                        " - {{ $dataHeader->satuan->nama_satuan ?? '' }}"; // pakai relasi kalau ada

                    var option = new Option(barangNama, barangId, true, true);

                    $('#id_barang').append(option).trigger('change');
                @endif

                @if ($mode == 'view')
                    $('#id_barang').prop('disabled', true);
                @elseif ($mode == 'edit')
                    $('#id_barang').prop('disabled', false);
                @endif
            });
        </script>



        @if (session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: "{{ session('success') }}",
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // redirect ke halaman detail, ganti URL sesuai kebutuhan
                        window.location.href =
                            "{{ url('/masterStdHargaBeli') }}";
                    }
                });
            </script>
        @endif


        @if (session('error'))
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    confirmButtonText: 'OK'
                });
            </script>
        @endif
    @endpush

@endsection
