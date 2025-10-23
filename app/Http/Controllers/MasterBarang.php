<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterJenisBarangModel;
use App\Models\MasterSatuanModel;
use App\Models\MasterBrandModel;
use App\Models\MasterBarangModel;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class MasterBarang extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $barangs = MasterBarangModel::all();

            return DataTables::of($barangs)
                ->addColumn('jenis_barang', function ($barang) {
                    return $barang->jenis_barang->nama_jenis ?? '-';
                })
                ->addColumn('brand', function ($barang) {
                    return $barang->brand->nama_brand ?? '-';
                })
                ->addColumn('satuan', function ($barang) {
                    return $barang->satuan->nama_satuan ?? '-';
                })
                ->addColumn('action', function ($barang) {
                    $id = encrypt($barang->id);
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $barang->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $id . '">
                                    <button class="dropdown-item btn-view" data-id="' . $id . '">View</button>
                                    <button class="dropdown-item btn-edit" data-id="' . $id . '">Edit</button>
                                    <button type="button" class="dropdown-item text-danger btn-delete" data-id="' . $barang->id . '">Delete</button>
                                </div>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $atribute =  'Master Barang';
        return view('masterBarang.index', compact('atribute')); // Memanggil view home.blade.php
    }

    function getDataJenisBarang(Request $request)
    {
        $search = $request->q;

        $data = MasterJenisBarangModel::select('kode_jenis', 'nama_jenis')
            ->where('nama_jenis', 'like', "%{$search}%")
            ->orWhere('kode_jenis', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->kode_jenis,
                'text' => $item->nama_jenis
            ];
        }

        return response()->json($result);
    }

    function getBrand(Request $request)
    {
        $search = $request->q;

        $data = MasterBrandModel::select('kode_brand', 'nama_brand')
            ->where('nama_brand', 'like', "%{$search}%")
            ->orWhere('kode_brand', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->kode_brand,
                'text' => $item->nama_brand
            ];
        }

        return response()->json($result);
    }

    function getSatuan(Request $request)
    {
        $search = $request->q;

        $data = MasterSatuanModel::select('kode_satuan', 'nama_satuan')
            ->where('nama_satuan', 'like', "%{$search}%")
            ->orWhere('kode_satuan', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->kode_satuan,
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
                'kode_jenis_barang' => 'required',
                'kode_brand' => 'required',
                'kode_satuan' => 'required'
            ]);



            $barang = MasterBarangModel::create($validated);

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
        $request->validate([
            'nama_barang' => 'required|unique:master_barang,nama_barang',
        ]);

        try {
            $idBarang = decrypt($idBarang);
            $barang = MasterBarangModel::find($idBarang);
            $barang->update([
                'kode_barang' => $request->kode_barang,
                'nama_barang' => $request->nama_barang,
                'kode_jenis_barang' => $request->kode_jenis_barang,
                'kode_brand' => $request->kode_brand,
                'kode_satuan' => $request->kode_satuan,
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
}
