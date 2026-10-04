<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrequestModel;
use Yajra\DataTables\Facades\DataTables;
use App\Models\MasterBarangModel;
use App\Models\preqitemModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PurchaseRequest extends Controller
{
    function index(Request $request)
    {
        $search  = $request->search;
        $dari    = $request->tanggal_dari;
        $sampai  = $request->tanggal_sampai;
        $dept    = $request->departemen;

        $dataPr = PrequestModel::with(['cabang', 'departemens'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('kode_pr', 'like', "%{$search}%")
                        ->orWhere('request_by', 'like', "%{$search}%");
                });
            })
            ->when($dari, fn($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->when($dept, fn($q) => $q->where('departemen', $dept))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // Request AJAX: kembalikan tabel saja
        if ($request->ajax()) {
            return view('purchaseRequest._table', compact('dataPr'));
        }

        $atribute = 'Purchase Request';
        return view('purchaseRequest.index', compact('atribute', 'dataPr'));
    }

    function show($id)
    {
        $pr = PrequestModel::with(['cabang', 'departemens', 'items.barang.satuan'])
            ->findOrFail(decrypt($id));

        $atribute = 'Purchase Request';
        return view('purchaseRequest.show', compact('atribute', 'pr'));
    }

    function edit($id)
    {
        $pr = PrequestModel::with(['departemens', 'items.barang.satuan'])->findOrFail(decrypt($id));

        // Item hanya bisa diubah jika semua item masih 'open'
        $locked = $pr->items->contains(fn($i) => $i->status !== 'open');

        $barangs = MasterBarangModel::with('satuan')->get();

        $atribute = 'Purchase Request';
        return view('purchaseRequest.edit', compact('atribute', 'pr', 'locked', 'barangs'));
    }

    function create()
    {
        $atribute = 'Purchase Request';
        $kode_pr = 'PR/' . date('m') . '/' . date('Y') . '/' . str_pad(PrequestModel::count() + 1, 5, '0', STR_PAD_LEFT);
        return view('purchaseRequest.create', compact('atribute', 'kode_pr'));
    }

    function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_pr'    => 'required',   // unique dihapus, karena updateOrCreate yang menangani duplikat
            'tanggal'    => 'required|date',
            'departemen' => 'required',
            'request_by' => 'required',
            'jenis_pr'   => 'required|in:bahan_dagang,bukan_bahan_dagang',
            'kode_wo'    => 'nullable|required_if:jenis_pr,bukan_bahan_dagang',
            'keterangan' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first()
            ]);
        }

        // Jika PR sudah diproses (ada item non-open), tolak perubahan
        $existing = PrequestModel::with('items')->where('kode_pr', $request->kode_pr)->first();
        if ($existing && $existing->items->contains(fn($i) => $i->status !== 'open')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'PR sudah diproses, tidak bisa diubah.'
            ]);
        }

        // Ganti blok updateOrCreate + if wasRecentlyCreated dengan ini:
        $pr = PrequestModel::firstOrNew(['kode_pr' => $request->kode_pr]);

        $pr->fill([
            'tanggal'    => $request->tanggal,
            'departemen' => $request->departemen,
            'request_by' => $request->request_by,
            'jenis_pr'   => $request->jenis_pr,
            'kode_wo'    => $request->jenis_pr === 'bahan_dagang' ? null : $request->kode_wo,
            'keterangan' => $request->keterangan,
        ]);

        if (!$pr->exists) {
            $user = auth()->user();
            $pr->created_by = $user->username ?? $user->name ?? $user->id;
        }

        $isNew = !$pr->exists;
        $pr->save();

        return response()->json([
            'status'  => 'success',
            'message' => $isNew
                ? 'Header Purchase Request berhasil disimpan.'
                : 'Header Purchase Request berhasil diperbarui.',
            'data'    => $pr
        ]);
    }

    function update(Request $request, $id)
    {
        $pr = PrequestModel::with('items')->findOrFail(decrypt($id));

        if ($pr->items->contains(fn($i) => $i->status !== 'open')) {
            return back()->with('error', 'PR sudah diproses, tidak bisa diubah.');
        }

        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'departemen' => 'required',
            'request_by' => 'required',
            'jenis_pr'   => 'required|in:bahan_dagang,bukan_bahan_dagang',
            'kode_wo'    => 'nullable|required_if:jenis_pr,bukan_bahan_dagang',
            'keterangan' => 'nullable',
        ]);

        if ($validated['jenis_pr'] === 'bahan_dagang') {
            $validated['kode_wo'] = null;
        }

        $pr->update($validated);

        return redirect()->route('purchaseRequest.index')
            ->with('success', 'Purchase Request berhasil diperbarui.');
    }

    function destroy($id)
    {
        $pr = PrequestModel::with('items')->findOrFail(decrypt($id));

        if ($pr->items->contains(fn($i) => $i->status !== 'open')) {
            return back()->with('error', 'PR sudah diproses, tidak bisa dihapus.');
        }

        DB::transaction(function () use ($pr) {
            preqitemModel::where('kode_pr', $pr->kode_pr)->delete();
            $pr->delete();
        });

        return redirect()->route('purchaseRequest.index')
            ->with('success', 'Purchase Request berhasil dihapus.' . $pr->kode_pr);
    }

    function detail(Request $request)
    {
        $items = preqitemModel::with('barang.satuan')
            ->where('kode_pr', $request->kode_pr)
            ->get()
            ->map(fn($row) => [
                'id'          => $row->id,
                'kode_barang' => $row->barang->kode_barang ?? '-',
                'nama_barang' => $row->barang->nama_barang ?? '-',
                'nama_satuan' => $row->barang->satuan->nama_satuan ?? '-',
                'qty_pr'      => $row->qty_pr,
                'notes'       => $row->notes,
                'status'      => $row->status,
            ]);

        return response()->json(['status' => 'success', 'data' => $items]);
    }

    function storeDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_pr'   => 'required|exists:prequest,kode_pr',
            'id_barang' => 'required|exists:master_barang,id',
            'qty_pr'    => 'required|numeric|min:0.0001',
            'notes'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        try {
            $exists = preqitemModel::where('kode_pr', $request->kode_pr)
                ->where('id_barang', $request->id_barang)->exists();

            if ($exists) {
                return response()->json(['status' => 'error', 'message' => 'Barang ini sudah ada di daftar item PR.']);
            }

            preqitemModel::create([
                'kode_pr'   => $request->kode_pr,
                'id_barang' => $request->id_barang,
                'qty_pr'    => $request->qty_pr,
                'qty_po'    => 0,
                'qty_outs'  => $request->qty_pr,
                'notes'     => $request->notes,
                'status'    => 'open',
            ]);

            return response()->json(['status' => 'success', 'message' => 'Item berhasil ditambahkan.']);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    function deleteDetail(Request $request)
    {
        try {
            $detail = preqitemModel::find($request->id);

            if (!$detail) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
            }
            if ($detail->status !== 'open') {
                return response()->json(['status' => 'error', 'message' => 'Item yang sudah diproses tidak bisa dihapus.']);
            }

            $detail->delete();
            return response()->json(['status' => 'success', 'message' => 'Item berhasil dihapus.']);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    function barangList(Request $request)
    {
        $q = $request->q;

        $paginator = MasterBarangModel::with('satuan')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('kode_barang', 'like', "%{$q}%")
                        ->orWhere('nama_barang', 'like', "%{$q}%");
                });
            })
            ->orderBy('kode_barang', 'asc')
            ->orderBy('nama_barang', 'asc')
            ->paginate(10);

        return response()->json([
            'data' => collect($paginator->items())->map(fn($b) => [
                'id'          => $b->id,
                'kode_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'satuan'      => $b->satuan->nama_satuan ?? '-',
            ])->values(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'total'        => $paginator->total(),
        ]);
    }
}
