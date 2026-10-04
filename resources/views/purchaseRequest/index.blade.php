@extends('layouts.tamplate')
@section('title', 'Purchase Request')

@section('content')
    <div class="content-wrapper mt-3">
        <section class="content">
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title mb-0">{{ $atribute }}</h3>
                </div>
                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <a href="{{ route('purchaseRequest.create') }}" class="btn btn-primary">Tambah PR</a>
                    </div>

                    {{-- Filter --}}
                    <div class="row" id="filter-bar">
                        <div class="col-md-3 form-group">
                            <label for="search">Cari</label>
                            <input type="text" id="search" class="form-control" placeholder="Kode PR / peminta"
                                autocomplete="off">
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="tanggal_dari">Tanggal Dari</label>
                            <input type="date" id="tanggal_dari" class="form-control">
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="tanggal_sampai">Tanggal Sampai</label>
                            <input type="date" id="tanggal_sampai" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="filter_departemen">Departemen</label>
                            <select id="filter_departemen" class="form-control" style="width:100%"></select>
                        </div>
                        <div class="col-md-2 form-group d-flex align-items-end">
                            <button type="button" id="btn-reset" class="btn btn-light border btn-block">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div id="table-wrapper">
                        @include('purchaseRequest._table')
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            $(function() {
                const indexUrl = "{{ route('purchaseRequest.index') }}";
                let xhr = null;
                let timer = null;

                // ===== Select2 filter departemen =====
                $('#filter_departemen').select2({
                    placeholder: 'Semua Departemen',
                    allowClear: true,
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

                // ===== Muat tabel =====
                function loadTable(url, params) {
                    if (xhr) xhr.abort(); // batalkan request sebelumnya

                    $('#table-wrapper').css('opacity', 0.5);
                    xhr = $.ajax({
                        url: url || indexUrl,
                        data: params || {},
                        method: 'GET'
                    }).done(function(html) {
                        $('#table-wrapper').html(html);
                    }).fail(function(jqXHR, status) {
                        if (status === 'abort') return;
                        Swal.fire('Error ' + jqXHR.status, 'Gagal memuat data.', 'error');
                    }).always(function(_, status) {
                        if (status !== 'abort') $('#table-wrapper').css('opacity', 1);
                    });
                }

                function currentFilters() {
                    return {
                        search: $('#search').val(),
                        tanggal_dari: $('#tanggal_dari').val(),
                        tanggal_sampai: $('#tanggal_sampai').val(),
                        departemen: $('#filter_departemen').val()
                    };
                }

                function applyFilters() {
                    loadTable(indexUrl, currentFilters());
                }

                // Ketik: tunggu 300 ms setelah berhenti mengetik
                $('#search').on('input', function() {
                    clearTimeout(timer);
                    timer = setTimeout(applyFilters, 300);
                });

                // Tanggal & departemen: langsung
                $('#tanggal_dari, #tanggal_sampai').on('change', applyFilters);
                $('#filter_departemen').on('change', applyFilters);

                // Reset
                $('#btn-reset').on('click', function() {
                    $('#search, #tanggal_dari, #tanggal_sampai').val('');
                    $('#filter_departemen').val(null).trigger('change.select2');
                    applyFilters();
                });

                // Pagination via AJAX (link sudah membawa filter)
                $(document).on('click', '#table-wrapper .pagination a', function(e) {
                    e.preventDefault();
                    loadTable($(this).attr('href'));
                });

                // Konfirmasi hapus
                $(document).on('submit', '.form-delete', function(e) {
                    e.preventDefault();
                    const form = this;
                    Swal.fire({
                        title: 'Yakin?',
                        text: 'PR beserta itemnya akan dihapus!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then(r => {
                        if (r.isConfirmed) form.submit();
                    });
                });
            });
        </script>
    @endpush
@endsection
