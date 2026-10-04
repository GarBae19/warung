<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode PR</th>
                <th>Cabang</th>
                <th>Tanggal</th>
                <th>Departemen</th>
                <th>Request By</th>
                <th style="width:200px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dataPr as $i => $row)
                @php $eid = encrypt($row->id); @endphp
                <tr>
                    <td>{{ $dataPr->firstItem() + $i }}</td>
                    <td>{{ $row->kode_pr }}</td>
                    <td>{{ $row->cabang->nama_cabang ?? '' }}</td>
                    <td>{{ $row->tanggal ? date('d-m-Y', strtotime($row->tanggal)) : '' }}</td>
                    <td>{{ $row->departemens->nama_departemen ?? '' }}</td>
                    <td>{{ $row->request_by }}</td>
                    <td>
                        <a href="{{ route('purchaseRequest.show', $eid) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('purchaseRequest.edit', $eid) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('purchaseRequest.destroy', $eid) }}" method="POST"
                            class="d-inline form-delete">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Data tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted">
        Menampilkan {{ $dataPr->firstItem() ?? 0 }} - {{ $dataPr->lastItem() ?? 0 }}
        dari {{ $dataPr->total() }} data
    </small>
    {{ $dataPr->links('pagination::bootstrap-4') }}
</div>
