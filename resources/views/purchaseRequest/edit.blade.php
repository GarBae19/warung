@extends('layouts.tamplate')
@section('title', 'Edit Purchase Request')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Edit Purchase Request</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('purchaseRequest.index') }}" class="btn btn-secondary mb-3">Kembali</a>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    @if ($locked)
                        <div class="alert alert-warning">PR ini sudah diproses sehingga tidak bisa diubah.</div>
                    @endif

                    @php
                        $jenisPr = old('jenis_pr', $pr->jenis_pr);
                        $listJenisPr = [
                            'bahan_dagang' => 'Bahan Dagang',
                            'bukan_bahan_dagang' => 'Bukan Bahan Dagang',
                        ];
                    @endphp

                    <form action="{{ route('purchaseRequest.update', encrypt($pr->id)) }}" method="POST">
                        @csrf @method('PUT')
                        <fieldset {{ $locked ? 'disabled' : '' }}>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Kode PR</label>
                                    <input type="text" class="form-control" id="kode_pr" value="{{ $pr->kode_pr }}"
                                        readonly>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control"
                                        value="{{ old('tanggal', $pr->tanggal) }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="departemen">Departemen</label>
                                    <select id="departemen" name="departemen" class="form-control" style="width:100%"
                                        required>
                                        @php $deptId = old('departemen', $pr->departemen); @endphp
                                        @if ($deptId)
                                            <option value="{{ $deptId }}" selected>
                                                {{ $deptId == $pr->departemen ? $pr->departemens->nama_departemen ?? $deptId : $deptId }}
                                            </option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Request By</label>
                                    <input type="text" name="request_by" class="form-control"
                                        value="{{ old('request_by', $pr->request_by) }}" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="jenis_pr">Jenis PR</label>
                                    <select name="jenis_pr" id="jenis_pr"
                                        class="form-control @error('jenis_pr') is-invalid @enderror" required>
                                        <option value="">-- Pilih Jenis PR --</option>
                                        @foreach ($listJenisPr as $value => $label)
                                            <option value="{{ $value }}"
                                                {{ $jenisPr === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jenis_pr')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Hanya tampil jika BUKAN bahan dagang --}}
                                <div class="col-md-6 form-group" id="wrap-kode-wo"
                                    style="{{ $jenisPr === 'bukan_bahan_dagang' ? '' : 'display:none' }}">
                                    <label for="kode_wo">Kode WO</label>
                                    <input type="text" name="kode_wo" id="kode_wo" class="form-control"
                                        value="{{ old('kode_wo', $pr->kode_wo) }}"
                                        {{ $jenisPr === 'bukan_bahan_dagang' ? '' : 'disabled' }}>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $pr->keterangan) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Header</button>
                        </fieldset>
                    </form>

                    <hr>
                    <h5 class="mb-3">Item Barang</h5>

                    @unless ($locked)
                        {{-- Form tambah item (tanpa modal) --}}
                        <div class="form-row">
                            <div class="col-md-5 form-group">
                                <label for="barang_label">Barang</label>
                                <div class="input-group">
                                    <input type="text" id="barang_label" class="form-control"
                                        placeholder="Klik Pilih untuk memilih barang" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-secondary" data-toggle="modal"
                                            data-target="#modal-barang">
                                            <i class="fas fa-search"></i> Pilih
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="id_barang">
                            </div>
                            <div class="col-md-2 form-group">
                                <label for="qty_item">Qty</label>
                                <input type="number" id="qty_item" class="form-control" min="0.01" step="0.01"
                                    value="1">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="notes_item">Notes</label>
                                <input type="text" id="notes_item" class="form-control" placeholder="Opsional">
                            </div>
                            <div class="col-md-2 form-group d-flex align-items-end">
                                <button type="button" id="btn-add-item" class="btn btn-success btn-block">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                    @endunless


                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Satuan</th>
                                    <th>Qty PR</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pr->items as $item)
                                    <tr>
                                        <td>{{ $item->barang->kode_barang ?? '-' }}</td>
                                        <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                                        <td>{{ $item->barang->satuan->nama_satuan ?? '-' }}</td>
                                        <td>{{ $item->qty_pr }}</td>
                                        <td>{{ $item->notes }}</td>
                                        <td>{{ ucfirst($item->status) }}</td>
                                        <td>
                                            @if ($item->status === 'open')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-item"
                                                    data-id="{{ $item->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada item</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('purchaseRequest.index') }}" class="btn btn-primary mt-2">Selesai</a>
                </div>
            </div>
        </section>
    </div>

    @include('purchaseRequest._modal_barang')

    @push('scripts')
        <script>
            $(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                function showAjaxError(xhr) {
                    let msg = 'Terjadi kesalahan server.';
                    if (xhr.status === 419) msg = 'Sesi/CSRF kedaluwarsa. Refresh halaman.';
                    else if (xhr.status === 404) msg = 'Route tidak ditemukan (404).';
                    else if (xhr.status === 405) msg = 'Method tidak diizinkan (405).';
                    else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire('Error ' + xhr.status, msg, 'error');
                }

                // ===== Select2 departemen =====
                $('#departemen').select2({
                    placeholder: '-- Pilih Departemen --',
                    ajax: {
                        url: "{{ route('getDepartemen') }}",
                        dataType: 'json',
                        delay: 250,
                        data: params => ({
                            q: params.term
                        }),
                        processResults: data => ({
                            results: data
                        }),
                        cache: true
                    }
                });

                // ===== Kode WO =====
                function toggleKodeWo() {
                    const isBukanDagang = $('#jenis_pr').val() === 'bukan_bahan_dagang';
                    if (isBukanDagang) {
                        $('#wrap-kode-wo').show();
                        $('#kode_wo').prop('disabled', false);
                    } else {
                        $('#wrap-kode-wo').hide();
                        $('#kode_wo').val('').prop('disabled', true);
                    }
                }
                $('#jenis_pr').on('change', toggleKodeWo);

                // ===== Barang dipilih dari modal =====
                $(document).on('barang:picked', function(e, item) {
                    $('#id_barang').val(item.id);
                    $('#barang_label').val(`${item.kode_barang} - ${item.nama_barang} (${item.satuan})`);
                    $('#qty_item').trigger('focus').select();
                });

                // ===== Tambah item =====
                $('#btn-add-item').on('click', function() {
                    const btn = $(this);
                    const id_barang = $('#id_barang').val();
                    const qty_pr = parseFloat($('#qty_item').val());

                    if (!id_barang || !qty_pr || qty_pr <= 0) {
                        Swal.fire('Perhatian', 'Pilih barang dan isi Qty dengan benar.', 'warning');
                        return;
                    }

                    btn.prop('disabled', true);
                    $.ajax({
                        url: "{{ route('purchaseRequest.storeDetail') }}",
                        method: 'POST',
                        data: {
                            kode_pr: $('#kode_pr').val(),
                            id_barang: id_barang,
                            qty_pr: qty_pr,
                            notes: $('#notes_item').val()
                        },
                        success: function(res) {
                            if (res.status === 'success') location.reload();
                            else Swal.fire('Gagal!', res.message, 'error');
                        },
                        error: showAjaxError,
                        complete: function() {
                            btn.prop('disabled', false);
                        }
                    });
                });

                // ===== Hapus item =====
                $(document).on('click', '.btn-delete-item', function() {
                    const id = $(this).data('id');
                    Swal.fire({
                        title: 'Hapus item?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then(r => {
                        if (!r.isConfirmed) return;
                        $.ajax({
                            url: "{{ route('purchaseRequest.deleteDetail') }}",
                            method: 'POST',
                            data: {
                                _method: 'DELETE',
                                id: id
                            },
                            success: function(res) {
                                if (res.status === 'success') location.reload();
                                else Swal.fire('Gagal!', res.message, 'error');
                            },
                            error: showAjaxError
                        });
                    });
                });
            });
        </script>
    @endpush
@endsection
