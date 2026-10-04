@extends('layouts.tamplate')
@section('title', 'Tambah Purchase Request')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">Tambah Purchase Request</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('purchaseRequest.index') }}" class="btn btn-secondary mb-3">Kembali</a>

                    <form id="form-purchaseRequest">
                        <fieldset id="fieldset-header">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="kode_pr">Kode PR</label>
                                    <input type="text" id="kode_pr" name="kode_pr" class="form-control"
                                        value="{{ $kode_pr ?? '' }}" readonly>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="tanggal">Tanggal</label>
                                    <input type="date" id="tanggal" name="tanggal" class="form-control"
                                        value="{{ date('Y-m-d') }}" required readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="departemen">Departemen</label>
                                    <select id="departemen" name="departemen" class="form-control" style="width:100%"
                                        required></select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="request_by">Request By</label>
                                    <input type="text" id="request_by" name="request_by" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="jenis_pr">Jenis PR</label>
                                    <select id="jenis_pr" name="jenis_pr" class="form-control" required>
                                        <option value="">-- Pilih Jenis PR --</option>
                                        <option value="bahan_dagang">Bahan Dagang</option>
                                        <option value="bukan_bahan_dagang">Bukan Bahan Dagang</option>
                                    </select>
                                </div>

                                {{-- Hanya tampil jika BUKAN bahan dagang --}}
                                <div class="col-md-6 form-group" id="wrap-kode-wo" style="display:none">
                                    <label for="kode_wo">Kode WO</label>
                                    <input type="text" id="kode_wo" name="kode_wo" class="form-control" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" id="btn-save-header">Simpan Header</button>
                        </fieldset>
                    </form>

                    {{-- Bagian item: muncul setelah header tersimpan --}}
                    <div id="item-section" style="display:none;">
                        <hr>
                        <h5 class="mb-3">Item Barang</h5>

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

                        <div class="table-responsive">
                            <table class="table table-bordered" id="table-pr-detail">
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
                                <tbody id="tbody-items">
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada item</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <a href="{{ route('purchaseRequest.index') }}" class="btn btn-primary mt-2">Selesai</a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Modal tambah item --}}
    @include('purchaseRequest._modal_barang')

    @push('scripts')
        <script>
            $(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                let currentKodePr = null;

                function showAjaxError(xhr) {
                    let msg = 'Terjadi kesalahan server.';
                    if (xhr.status === 419) msg = 'Sesi/CSRF kedaluwarsa. Refresh halaman.';
                    else if (xhr.status === 404) msg = 'Route tidak ditemukan (404).';
                    else if (xhr.status === 405) msg = 'Method tidak diizinkan (405).';
                    else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire('Error ' + xhr.status, msg, 'error');
                }

                function esc(str) {
                    return $('<div>').text(str ?? '').html();
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
                $('#jenis_pr').on('change', function() {
                    if (this.value === 'bukan_bahan_dagang') {
                        $('#wrap-kode-wo').show();
                        $('#kode_wo').prop('disabled', false);
                    } else {
                        $('#wrap-kode-wo').hide();
                        $('#kode_wo').val('').prop('disabled', true);
                    }
                });

                // ===== Simpan header =====
                $('#form-purchaseRequest').on('submit', function(e) {
                    e.preventDefault();
                    const btn = $('#btn-save-header');
                    // btn.prop('disabled', true);

                    $.ajax({
                        url: "{{ route('purchaseRequest.store') }}",
                        method: 'POST',
                        data: {
                            kode_pr: $('#kode_pr').val(),
                            tanggal: $('#tanggal').val(),
                            departemen: $('#departemen').val(),
                            request_by: $('#request_by').val(),
                            jenis_pr: $('#jenis_pr').val(),
                            kode_wo: $('#kode_wo').val(),
                            keterangan: $('#keterangan').val()
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                currentKodePr = res.data.kode_pr;
                                Swal.fire('Berhasil!', res.message, 'success');
                                // $('#fieldset-header').prop('disabled', true);
                                // btn.hide();
                                $('#item-section').show();
                                loadItems();
                            } else {
                                btn.prop('disabled', false);
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false);
                            showAjaxError(xhr);
                        }
                    });
                });

                // ===== Daftar item =====
                function loadItems() {
                    $.get("{{ route('purchaseRequest.detail') }}", {
                        kode_pr: currentKodePr
                    }).done(function(res) {
                        const rows = res.data || [];
                        if (!rows.length) {
                            $('#tbody-items').html(
                                '<tr><td colspan="7" class="text-center">Belum ada item</td></tr>');
                            return;
                        }

                        let html = '';
                        rows.forEach(function(r) {
                            const status = r.status ? r.status.charAt(0).toUpperCase() + r.status.slice(
                                1) : '';
                            const btn = r.status === 'open' ?
                                `<button type="button" class="btn btn-sm btn-danger btn-delete-item" data-id="${r.id}">
                                <i class="fas fa-trash"></i>
                            </button>` : '';
                            html += `<tr>
                            <td>${esc(r.kode_barang)}</td>
                            <td>${esc(r.nama_barang)}</td>
                            <td>${esc(r.nama_satuan)}</td>
                            <td>${esc(r.qty_pr)}</td>
                            <td>${esc(r.notes)}</td>
                            <td>${esc(status)}</td>
                            <td>${btn}</td>
                        </tr>`;
                        });
                        $('#tbody-items').html(html);
                    }).fail(showAjaxError);
                }

                // ===== Barang dipilih dari modal =====
                $(document).on('barang:picked', function(e, item) {
                    $('#id_barang').val(item.id);
                    $('#barang_label').val(`${item.kode_barang} - ${item.nama_barang} (${item.satuan})`);
                    $('#qty_item').trigger('focus').select();
                });

                function resetItemForm() {
                    $('#id_barang').val('');
                    $('#barang_label').val('');
                    $('#qty_item').val(1);
                    $('#notes_item').val('');
                }

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
                            kode_pr: currentKodePr,
                            id_barang: id_barang,
                            qty_pr: qty_pr,
                            notes: $('#notes_item').val()
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                resetItemForm();
                                loadItems();
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
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
                                if (res.status === 'success') loadItems();
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
