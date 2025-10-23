<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterBrandModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class MasterBrand extends Controller
{
    function index(Request $request)
    {
        if ($request->ajax()) {
            $brands = MasterBrandModel::all();

            return DataTables::of($brands)
                ->addColumn('action', function ($brand) {
                    $id = encrypt($brand->id);
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $brand->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $id . '">
                                    <button class="dropdown-item btn-view" data-id="' . $id . '">View</button>
                                    <button class="dropdown-item btn-edit" data-id="' . $id . '">Edit</button>
                                    <button type="button" class="dropdown-item text-danger btn-delete" data-id="' . $brand->id . '">Delete</button>
                                </div>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $atribute =  'Master Brand';
        return view('masterBrand.index', compact('atribute')); // Memanggil view home.blade.php
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_brand' => 'required|unique:master_brand,kode_brand',
                'nama_brand' => 'required'
            ]);

            $brand = MasterBrandModel::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Brand berhasil disimpan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Brand gagal disimpan' . $e,
            ]);
        }
    }

    function show($id)
    {
        try {
            $id = decrypt($id);
            $data = MasterBrandModel::find($id);
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

    public function update(Request $request, $idBrand)
    {
        $request->validate([
            'nama_brand' => 'required|unique:master_brand,nama_brand',
        ]);

        try {
            $idBrand = decrypt($idBrand);
            $brand = MasterBrandModel::find($idBrand);
            $brand->update([
                'kode_brand' => $request->kode_brand,
                'nama_brand' => $request->nama_brand,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Brand berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate brand: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {

        try {
            $brand = MasterBrandModel::find($id);
            $brand->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Brand berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus brand: ' . $e->getMessage()
            ]);
        }
    }

    private function generateKodeBrand()
    {
        $last = MasterBrandModel::orderBy('kode_brand', 'desc')->first();

        if ($last) {
            $lastNumber = (int) substr($last->kode_brand, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'BRD' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    function getKodeBrand()
    {
        try {

            $kodeBrand = $this->generateKodeBrand();

            return response()->json([
                'status' => 'success',
                'kodeBrand' => $kodeBrand,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodeBrand' => $e,
            ]);
        }
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file_excel');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();



            // Loop mulai dari baris ke-2 (karena baris 1 = header)
            $err = [];
            for ($row = 2; $row <= $highestRow; $row++) {

                // Ambil berdasarkan kolom (A, B, C, D)
                $kode_brand = $this->generateKodeBrand();
                $nama_brand = trim($sheet->getCell("A{$row}")->getValue());

                // Lewati kalau kosong
                if (empty($kode_brand) || empty($nama_brand)) {
                    $err[] = 'Baris ' . $row . ' dilewati karena ada kolom yang kosong.';
                    if ($row >= 2) break;
                } else {
                    MasterBrandModel::updateOrCreate(
                        ['kode_brand' => $kode_brand],
                        [
                            'nama_brand' => $nama_brand
                        ]
                    );
                }
            }

            if (count($err) > 0) {
                $errorMessage = implode('<br>', $err);
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Import selesai dengan beberapa peringatan: ' . $errorMessage,
                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Satuan berhasil disimpan',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Satuan gagal disimpan' . $e,
            ]);
        }
    }
}
