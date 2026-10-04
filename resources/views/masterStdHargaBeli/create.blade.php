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
                    <form id="form-stdHargaBeli" method="POST" action="{{ route('masterStdHargaBeli.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_std_harga_beli">Kode STD Harga Beli</label>
                                    <input type="text" id="kode_std_harga_beli" name="kode_std_harga_beli"
                                        class="form-control" value="{{ $kodehargaBeli ?? '' }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_barang">Barang</label>
                                    <select id="id_barang" name="id_barang" style="width:100%; height:38px"
                                        value="{{ old('id_barang') }}" required></select>
                                    <input type="hidden" id="id_satuan" name="id_satuan">
                                    <input type="hidden" id="id_barang2" name="id_barang2">
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
                                            {{-- <input type="number" class="form-control" style="text-align:right"
                                                name="harga_beli[{{ $cabang->id }}]"
                                                value="{{ old('harga_beli.' . $cabang->id) }}" required> --}}
                                            <input type="text" class="form-control harga-beli" style="text-align:right"
                                                name="harga_beli[{{ $cabang->id }}]"
                                                value="{{ old('harga_beli.' . $cabang->id) }}"
                                                oninput="formatInputRibuan(this)" required>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>


                        <div class="d-grid gap-2 mt-3" id="button_submit_header">
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
