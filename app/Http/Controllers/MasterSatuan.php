<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterSatuanModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\MasterSatuanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class MasterSatuan extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->search;

            $query = MasterSatuanModel::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_satuan', 'like', "%{$search}%")
                        ->orWhere('nama_satuan', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
                });
            }

            $satuans = $query->orderBy('id', 'desc')
                ->paginate($request->per_page ?? 10);

            $satuans->getCollection()->transform(function ($satuan) {
                $satuan->id_encrypted = encrypt($satuan->id);
                return $satuan;
            });

            return response()->json($satuans);
        }

        $atribute = 'Master Satuan';
        return view('masterSatuan.index', compact('atribute'));
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_satuan' => 'required|unique:master_satuan,kode_satuan',
                'nama_satuan' => 'required',
                'keterangan' => 'nullable'
            ]);

            $validated['created_by'] = auth()->user()->name;

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
                    // MasterSatuanModel::updateOrCreate(
                    //     ['kode_satuan' => $kode_satuan],
                    //     [
                    //         'nama_satuan' => $nama_satuan,
                    //         'keterangan' => $keterangan,
                    //         'created_by' => auth()->user()->name,
                    //         'updated_by' => auth()->user()->name
                    //     ]
                    // );
                    $user = auth()->user();

                    MasterSatuanModel::updateOrCreate(
                        ['kode_satuan' => $kode_satuan],
                        [
                            'nama_satuan' => $nama_satuan,
                            'keterangan'  => $keterangan,
                            'updated_by'  => $user ? $user->name : 'system',
                        ]
                    );

                    // Set created_by hanya jika data baru dibuat
                    $model = MasterSatuanModel::where('kode_satuan', $kode_satuan)->first();

                    if ($model->wasRecentlyCreated) {
                        $model->created_by = $user ? $user->name : 'system';
                        $model->save();
                    }
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
            'keterangan' => 'nullable'
        ]);

        try {
            $idSatuan = decrypt($idSatuan);
            $satuan = MasterSatuanModel::find($idSatuan);
            $satuan->update([
                'kode_satuan' => $request->kode_satuan,
                'nama_satuan' => $request->nama_satuan,
                'keterangan' => $request->keterangan,
                'updated_by' => auth()->user()->name
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

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new MasterSatuanExport($request->search),
            'data_satuan_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(
            new MasterSatuanExport($request->search),
            'data_satuan_' . date('Ymd_His') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf(Request $request)
    {
        $satuans = $this->getFilteredData($request->search);

        $pdf = Pdf::loadView('masterSatuan.export_pdf', compact('satuans'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('data_satuan_' . date('Ymd_His') . '.pdf');
    }

    public function printPdf(Request $request)
    {
        $satuans = $this->getFilteredData($request->search);

        $pdf = Pdf::loadView('masterSatuan.export_pdf', compact('satuans'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('data_satuan.pdf');
    }
}
