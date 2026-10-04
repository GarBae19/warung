<?php

namespace App\Http\Controllers;

use App\Models\MasterCabangModel;
use Illuminate\Http\Request;
use App\Models\MasterGudangModel;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class MasterGudang extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $gudangs = MasterGudangModel::all();

            return DataTables::of($gudangs)
                ->addColumn('cabang', function ($gudang) {
                    return $gudang->cabang->nama_cabang ?? '-';
                })
                ->addColumn('action', function ($gudang) {
                    $id = encrypt($gudang->id);
                    if (!$gudang->is_used) {
                        $buttonAction = '<button class="dropdown-item btn-edit" data-id="' . $id . '">Edit</button>
                                         <button type="button" class="dropdown-item text-danger btn-delete" data-id="' . $gudang->id . '">Delete</button>';
                    } else {
                        $buttonAction = '';
                    }
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $gudang->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $id . '">
                                    <button class="dropdown-item btn-view" data-id="' . $id . '">View</button>
                                    ' . $buttonAction . '
                                </div>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $atribute =  'Master Gudang';
        return view('masterGudang.index', compact('atribute')); // Memanggil view home.blade.php
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_gudang' => 'required|unique:master_gudang,kode_gudang',
                'nama_gudang' => 'required',
                'id_cabang' => 'required'
            ]);

            $validated['created_by'] = auth()->user()->name;

            $gudang = MasterGudangModel::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Gudang berhasil disimpan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gudang gagal disimpan' . $e,
            ]);
        }
    }

    function show($id)
    {
        try {
            $id = decrypt($id);
            $data = MasterGudangModel::find($id);
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

    public function update(Request $request, $idGudang)
    {
        $request->validate([
            'nama_gudang' => 'required',
            'kode_gudang' => 'required'
        ]);

        try {
            $idGudang = decrypt($idGudang);
            $jenis = MasterGudangModel::find($idGudang);
            $jenis->update([
                'kode_gudang' => $request->kode_gudang,
                'nama_gudang' => $request->nama_gudang,
                'id_cabang' => $request->id_cabang,
                'updated_by' => auth()->user()->name
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Gudang berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate Gudang: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {

        try {
            $gudang = MasterGudangModel::find($id);
            $gudang->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Gudang berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus gudang: ' . $e->getMessage()
            ]);
        }
    }


    function getKodeGudang()
    {
        try {
            $last = MasterGudangModel::orderBy('kode_gudang', 'desc')->first();

            if ($last) {
                // Ambil angka dari belakang kode_gudang, misal BRD0005 → 5
                $lastNumber = (int) substr($last->kode_gudang, 3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $kodeGudang = 'GD' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'success',
                'kodeGudang' => $kodeGudang,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodeGudang' => $e,
            ]);
        }
    }

    function getGudang(Request $request)
    {
        $search = $request->q;

        $data = MasterGudangModel::select('id', 'nama_gudang')
            ->where('nama_gudang', 'like', "%{$search}%")
            ->orWhere('id', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->nama_gudang
            ];
        }

        return response()->json($result);
    }
}
