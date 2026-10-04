<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterJenisBarangModel;
use App\Exports\MasterJenisBarangExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class MasterJenisBarang extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->search;

            $query = MasterJenisBarangModel::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_jenis', 'like', "%{$search}%")
                        ->orWhere('nama_jenis', 'like', "%{$search}%");
                });
            }

            $jenises = $query->orderBy('id', 'desc')
                ->paginate($request->per_page ?? 10);

            $jenises->getCollection()->transform(function ($jenis) {
                $jenis->id_encrypted = encrypt($jenis->id);
                return $jenis;
            });

            return response()->json($jenises);
        }

        $atribute = 'Master Jenis Barang';
        return view('masterJenis.index', compact('atribute'));
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_jenis' => 'required|unique:master_jenis,kode_jenis',
                'nama_jenis' => 'required'
            ]);

            $validated['created_by'] = auth()->user()->name;

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
                'updated_by' => auth()->user()->name
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

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new MasterJenisBarangExport($request->search),
            'data_jenis_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(
            new MasterJenisBarangExport($request->search),
            'data_jenis_' . date('Ymd_His') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf(Request $request)
    {
        $jenises = $this->getFilteredData($request->search);

        $pdf = Pdf::loadView('masterJenis.export_pdf', compact('jenises'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('data_jenis_' . date('Ymd_His') . '.pdf');
    }

    public function printPdf(Request $request)
    {
        $jenises = $this->getFilteredData($request->search);

        $pdf = Pdf::loadView('masterJenis.export_pdf', compact('jenises'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('data_jenis.pdf');
    }

    private function getFilteredData($search = null)
    {
        $query = MasterJenisBarangModel::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_jenis', 'like', "%{$search}%")
                    ->orWhere('nama_jenis', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }
}
