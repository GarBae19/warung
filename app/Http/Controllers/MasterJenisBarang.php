<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterJenisBarangModel;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class MasterJenisBarang extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $jenises = MasterJenisBarangModel::all();

            return DataTables::of($jenises)
                ->addColumn('action', function ($jenis) {
                    $id = encrypt($jenis->id);
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $jenis->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $id . '">
                                    <button class="dropdown-item btn-view" data-id="' . $id . '">View</button>
                                    <button class="dropdown-item btn-edit" data-id="' . $id . '">Edit</button>
                                    <button type="button" class="dropdown-item text-danger btn-delete" data-id="' . $jenis->id . '">Delete</button>
                                </div>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $atribute =  'Master Jenis Barang';
        return view('masterJenis.index', compact('atribute')); // Memanggil view home.blade.php
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_jenis' => 'required|unique:master_jenis,kode_jenis',
                'nama_jenis' => 'required'
            ]);

            $jenis = MasterJenisBarangModel::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Jenis berhasil disimpan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jenis gagal disimpan' . $e,
            ]);
        }
    }

    function show($id)
    {
        try {
            $id = decrypt($id);
            $data = MasterJenisBarangModel::find($id);
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

    public function update(Request $request, $idJenis)
    {
        $request->validate([
            'nama_jenis' => 'required|unique:master_jenis,nama_jenis',
        ]);

        try {
            $idJenis = decrypt($idJenis);
            $jenis = MasterJenisBarangModel::find($idJenis);
            $jenis->update([
                'kode_jenis' => $request->kode_jenis,
                'nama_jenis' => $request->nama_jenis,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Jenis berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate jenis: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {

        try {
            $jenis = MasterJenisBarangModel::find($id);
            $jenis->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Jenis berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus jenis: ' . $e->getMessage()
            ]);
        }
    }


    function getKodeJenisBarang()
    {
        try {
            $last = MasterJenisBarangModel::orderBy('kode_jenis', 'desc')->first();

            if ($last) {
                // Ambil angka dari belakang kode_jenis, misal BRD0005 → 5
                $lastNumber = (int) substr($last->kode_jenis, 3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $kodeJenis = 'JNS' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'success',
                'kodeJenis' => $kodeJenis,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodeJenis' => $e,
            ]);
        }
    }
}
