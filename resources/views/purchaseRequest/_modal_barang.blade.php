<div class="modal fade" id="modal-barang" tabindex="-1" aria-labelledby="modalBarangLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBarangLabel">Pilih Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" id="barang-search" class="form-control mb-3"
                    placeholder="Cari kode / nama barang..." autocomplete="off">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th class="text-center" style="width:90px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-barang">
                            <tr>
                                <td colspan="4" class="text-center">Memuat...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted" id="barang-info"></small>
                <div>
                    <button type="button" class="btn btn-sm btn-light border" id="barang-prev">&laquo;
                        Sebelumnya</button>
                    <button type="button" class="btn btn-sm btn-light border" id="barang-next">Berikutnya
                        &raquo;</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function() {
            const urlBarang = "{{ route('purchaseRequest.barang') }}";
            let page = 1,
                lastPage = 1,
                rows = [],
                timer = null,
                xhr = null;

            function escB(str) {
                return $('<div>').text(str ?? '').html();
            }

            function loadBarang(p) {
                page = p || 1;
                if (xhr) xhr.abort();

                xhr = $.get(urlBarang, {
                    q: $('#barang-search').val(),
                    page: page
                }).done(function(res) {
                    rows = res.data || [];
                    lastPage = res.last_page;

                    if (!rows.length) {
                        $('#tbody-barang').html(
                            '<tr><td colspan="4" class="text-center">Barang tidak ditemukan</td></tr>');
                    } else {
                        let html = '';
                        rows.forEach(function(r, i) {
                            html += `<tr>
                                <td>${escB(r.kode_barang)}</td>
                                <td>${escB(r.nama_barang)}</td>
                                <td>${escB(r.satuan)}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-primary btn-pick-barang" data-idx="${i}">Pilih</button>
                                </td>
                            </tr>`;
                        });
                        $('#tbody-barang').html(html);
                    }

                    $('#barang-info').text(
                        `Halaman ${res.current_page} dari ${res.last_page} (${res.total} barang)`);
                    $('#barang-prev').prop('disabled', res.current_page <= 1);
                    $('#barang-next').prop('disabled', res.current_page >= res.last_page);
                }).fail(function(jqXHR, status) {
                    if (status === 'abort') return;
                    $('#tbody-barang').html(
                        '<tr><td colspan="4" class="text-center text-danger">Gagal memuat data</td></tr>'
                        );
                });
            }

            $('#modal-barang').on('shown.bs.modal', function() {
                $('#barang-search').val('').focus();
                loadBarang(1);
            });

            $('#barang-search').on('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => loadBarang(1), 300);
            });

            $('#barang-prev').on('click', () => page > 1 && loadBarang(page - 1));
            $('#barang-next').on('click', () => page < lastPage && loadBarang(page + 1));

            // Pilih barang -> kirim event ke halaman yang memanggil
            $(document).on('click', '.btn-pick-barang', function() {
                const item = rows[$(this).data('idx')];
                $(document).trigger('barang:picked', [item]);
                $('#modal-barang').modal('hide');
            });
        });
    </script>
@endpush
