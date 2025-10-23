<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterSatuanModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class MasterSatuan extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $satuans = MasterSatuanModel::all();

            return DataTables::of($satuans)
                ->addColumn('action', function ($satuan) {
                    $id = encrypt($satuan->id);
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $satuan->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $id . '">
                                    <button class="dropdown-item btn-view" data-id="' . $id . '">View</button>
                                    <button class="dropdown-item btn-edit" data-id="' . $id . '">Edit</button>
                                    <button type="button" class="dropdown-item text-danger btn-delete" data-id="' . $satuan->id . '">Delete</button>
                                </div>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $atribute =  'Master Satuan';
        return view('masterSatuan.index', compact('atribute')); // Memanggil view home.blade.php
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_satuan' => 'required|unique:master_satuan,kode_satuan',
                'nama_satuan' => 'required'
            ]);

            $satuan = MasterSatuanModel::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Satuan berhasil disimpan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Satuan gagal disimpan' . $e,
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
                $kode_satuan = $this->generateKodeSatuan();
                $nama_satuan = trim($sheet->getCell("A{$row}")->getValue());
                $keterangan = trim($sheet->getCell("B{$row}")->getValue());

                // Lewati kalau kosong
                if (!$kode_satuan || !$nama_satuan || !$keterangan) {
                    $err[] = 'Baris ' . $row . ' dilewati karena ada kolom yang kosong.';
                    if ($row >= 2) break;
                } else {
                    MasterSatuanModel::updateOrCreate(
                        ['kode_satuan' => $kode_satuan],
                        [
                            'nama_satuan' => $nama_satuan,
                            'keterangan' => $keterangan
                        ]
                    );
                }
            }
            if (count($err) > 0) {
                $errorMessage = implode('\n', $err);
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

    function show($id)
    {
        try {
            $id = decrypt($id);
            $data = MasterSatuanModel::find($id);
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

    public function update(Request $request, $idSatuan)
    {
        $request->validate([
            'nama_satuan' => 'required|unique:master_satuan,nama_satuan',
        ]);

        try {
            $idSatuan = decrypt($idSatuan);
            $satuan = MasterSatuanModel::find($idSatuan);
            $satuan->update([
                'kode_satuan' => $request->kode_satuan,
                'nama_satuan' => $request->nama_satuan,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Satuan berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate satuan: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {

        try {
            $satuan = MasterSatuanModel::find($id);
            $satuan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Satuan berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus satuan: ' . $e->getMessage()
            ]);
        }
    }

    private function generateKodeSatuan()
    {
        $last = MasterSatuanModel::orderBy('kode_satuan', 'desc')->first();

        if ($last) {
            $lastNumber = (int) substr($last->kode_satuan, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'STN' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    function getKodeSatuan()
    {
        try {

            $kodeSatuan = $this->generateKodeSatuan();

            return response()->json([
                'status' => 'success',
                'kodeSatuan' => $kodeSatuan,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodeSatuan' => $e,
            ]);
        }
    }
}
