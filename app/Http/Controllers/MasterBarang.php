<?php

namespace App\Http\Controllers;

use App\Models\KonversiSatuanModel;
use Illuminate\Http\Request;
use App\Models\MasterJenisBarangModel;
use App\Models\MasterSatuanModel;
use App\Models\MasterBrandModel;
use App\Models\MasterBarangModel;
use Exception;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\MasterBarangExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class MasterBarang extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->search;

            $query = MasterBarangModel::with(['jenis_barang', 'brand', 'satuan']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            }

            $barangs = $query->orderBy('id', 'desc')
                ->paginate($request->per_page ?? 10);

            $barangs->getCollection()->transform(function ($barang) {
                $id = encrypt($barang->id);
                $barang->jenis_barang_nama = $barang->jenis_barang->nama_jenis ?? '-';
                $barang->brand_nama = $barang->brand->nama_brand ?? '-';
                $barang->satuan_nama = $barang->satuan->nama_satuan ?? '-';
                $barang->id_encrypted = $id;
                return $barang;
            });

            return response()->json($barangs);
        }

        $atribute = 'Master Barang';
        return view('masterBarang.index', compact('atribute'));
    }

    function getDataJenisBarang(Request $request)
    {
        $search = $request->q;

        $data = MasterJenisBarangModel::select('id', 'nama_jenis')
            ->where('nama_jenis', 'like', "%{$search}%")
            ->orWhere('id', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->nama_jenis
            ];
        }

        return response()->json($result);
    }

    function getBrand(Request $request)
    {
        $search = $request->q;

        $data = MasterBrandModel::select('id', 'nama_brand')
            ->where('nama_brand', 'like', "%{$search}%")
            ->orWhere('id', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->nama_brand
            ];
        }

        return response()->json($result);
    }

    function getSatuan(Request $request)
    {
        $search = $request->q;

        $data = MasterSatuanModel::select('id', 'nama_satuan')
            ->where('nama_satuan', 'like', "%{$search}%")
            ->orWhere('id', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->nama_satuan
            ];
        }

        return response()->json($result);
    }

    function getKodeMasterBarang()
    {
        try {
            $last = MasterBarangModel::orderBy('kode_barang', 'desc')->first();

            if ($last) {
                // Ambil angka dari belakang kode_jenis, misal BRD0005 → 5
                $lastNumber = (int) substr($last->kode_barang, 3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $kodeBarang = 'BRG' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'success',
                'kodeBarang' => $kodeBarang,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodeBarang' => $e,
            ]);
        }
    }

    function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'kode_barang' => 'required|unique:master_barang,kode_barang',
                'nama_barang' => 'required',
                'id_jenis_barang' => 'required',
                'id_brand' => 'required',
                'id_satuan' => 'required',
                'min_stock' => 'nullable|integer',
                'gambar' => 'nullable|image|file|max:2048',
            ]);

            // Handle file upload
            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $nama = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/barang'), $nama);
                $validated['gambar'] = $nama;
            }

            $validated['pecah_satuan'] = 0;
            $validated['active'] = 1;

            $validated['created_by'] = auth()->user()->name;
            $validated['bahan_baku'] = 1;
            $barang = MasterBarangModel::create($validated);

            KonversiSatuanModel::create([
                'id_barang' => $barang->id,
                'id_satuan_asal' => $barang->id_satuan,
                'nilai_konversi' => 1
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil disimpan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang gagal disimpan' . $e,
            ]);
        }
    }

    function show($id)
    {
        try {
            $id = decrypt($id);
            $data = MasterBarangModel::find($id);
            // dd($data);
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'data' => $e,
            ]);
        }
    }

    public function update(Request $request, $idBarang)
    {

        try {
            $request->validate([
                'nama_barang' => 'required',
                'min_stock' => 'nullable|integer',
                'gambar' => 'nullable|image|file|max:2048',
            ]);

            if ($request->hasFile('gambar')) {
                $file = $request->file('gambar');
                $nama = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/barang'), $nama);
            }

            $idBarang = decrypt($idBarang);
            $barang = MasterBarangModel::find($idBarang);
            $barang->update([
                'kode_barang' => $request->kode_barang,
                'nama_barang' => $request->nama_barang,
                'id_jenis_barang' => $request->id_jenis_barang,
                'id_brand' => $request->id_brand,
                'id_satuan' => $request->id_satuan,
                'min_stock' => $request->min_stock,
                'gambar' => $request->hasFile('gambar') ? $nama : $barang->gambar,
                'updated_by' => auth()->user()->name,
                'pecah_satuan' => $request->pecah_satuan,
                'bahan_baku' => 1
            ]);

            $konversi_satuan = KonversiSatuanModel::where('id_barang', $idBarang)->first();
            $konversi_satuan->update([
                'id_barang' => $barang->id,
                'id_satuan_asal' => $barang->id_satuan,
                'nilai_konversi' => 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate barang: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {

        try {
            $barang = MasterBarangModel::find($id);
            $barang->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus barang: ' . $e->getMessage()
            ]);
        }
    }

    function getDataBarang(Request $request)
    {
        $search = $request->q;

        $data = MasterBarangModel::select('id', 'nama_barang')
            ->where('nama_barang', 'like', "%{$search}%")
            ->orWhere('id', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->nama_barang
            ];
        }

        return response()->json($result);
    }



    public function exportExcel(Request $request)
    {
        return Excel::download(
            new MasterBarangExport($request->search),
            'data_barang_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(
            new MasterBarangExport($request->search),
            'data_barang_' . date('Ymd_His') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf(Request $request)
    {
        $search = $request->search;

        $query = MasterBarangModel::with(['jenis_barang', 'brand', 'satuan']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        $barangs = $query->orderBy('id', 'desc')->get();

        $pdf = Pdf::loadView('masterBarang.export_pdf', compact('barangs'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('data_barang_' . date('Ymd_His') . '.pdf');
    }

    public function printPdf(Request $request)
    {
        $search = $request->search;

        $query = MasterBarangModel::with(['jenis_barang', 'brand', 'satuan']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        $barangs = $query->orderBy('id', 'desc')->get();

        $pdf = Pdf::loadView('masterBarang.export_pdf', compact('barangs'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('data_barang.pdf'); // buka di tab baru untuk print
    }
}
