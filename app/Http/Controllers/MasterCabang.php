<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterCabangModel;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class MasterCabang extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $cabangs = MasterCabangModel::all();

            return DataTables::of($cabangs)
                ->addColumn('action', function ($cabang) {
                    $id = encrypt($cabang->id);
                    if (!$cabang->is_used) {
                        $buttonAction = '<button class="dropdown-item btn-edit" data-id="' . $id . '">Edit</button>
                                         <button type="button" class="dropdown-item text-danger btn-delete" data-id="' . $cabang->id . '">Delete</button>';
                    } else {
                        $buttonAction = '';
                    }
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $cabang->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
        $atribute =  'Master Cabang';
        return view('masterCabang.index', compact('atribute')); // Memanggil view home.blade.php
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_cabang' => 'required|unique:master_cabang,kode_cabang',
                'nama_cabang' => 'required',
                'no_telp' => 'nullable',
                'no_hp' => 'nullable',
                'email' => 'nullable',
                'fax' => 'nullable',
                'alamat' => 'nullable'
            ]);

            $validated['created_by'] = auth()->user()->name;

            $cabang = MasterCabangModel::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Cabang berhasil disimpan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cabang gagal disimpan' . $e,
            ]);
        }
    }

    function show($id)
    {
        try {
            $id = decrypt($id);
            $data = MasterCabangModel::find($id);
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

    public function update(Request $request, $idCabang)
    {
        $request->validate([
            'nama_cabang' => 'required',
            'kode_cabang' => 'required',
            'no_telp' => 'nullable',
            'no_hp' => 'nullable',
            'email' => 'nullable',
            'fax' => 'nullable',
            'alamat' => 'nullable'
        ]);

        try {
            $idCabang = decrypt($idCabang);
            $cabang = MasterCabangModel::find($idCabang);
            $cabang->update([
                'kode_cabang' => $request->kode_cabang,
                'nama_cabang' => $request->nama_cabang,
                'no_telp' => $request->no_telp,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'fax' => $request->fax,
                'alamat' => $request->alamat,
                'updated_by' => auth()->user()->name
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cabang berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate Cabang: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {

        try {
            $cabang = MasterCabangModel::find($id);
            $cabang->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Cabang berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus cabang: ' . $e->getMessage()
            ]);
        }
    }


    function getKodeCabang()
    {
        try {
            $last = MasterCabangModel::orderBy('kode_cabang', 'desc')->first();

            if ($last) {
                // Ambil angka dari belakang kode_cabang, misal BRD0005 → 5
                $lastNumber = (int) substr($last->kode_cabang, 3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $kodeCabang = 'CB' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'success',
                'kodeCabang' => $kodeCabang,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodeCabang' => $e,
            ]);
        }
    }

    function getCabang(Request $request)
    {
        $search = $request->q;

        $data = MasterCabangModel::select('id', 'nama_cabang')
            ->where('nama_cabang', 'like', "%{$search}%")
            ->orWhere('id', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->nama_cabang
            ];
        }

        return response()->json($result);
    }
}
