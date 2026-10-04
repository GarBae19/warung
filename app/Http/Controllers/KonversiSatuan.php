<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KonversiSatuanModel;
use App\Models\MasterSatuanModel;
use App\Exports\KonversiSatuanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Support\Facades\DB;

class KonversiSatuan extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->search;

            $query = KonversiSatuanModel::with(['barang', 'satuanAsal', 'satuanKonversi']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('barang', function ($qb) use ($search) {
                        $qb->where('kode_barang', 'like', "%{$search}%")
                            ->orWhere('nama_barang', 'like', "%{$search}%");
                    });
                });
            }

            $konversies = $query->orderBy('id', 'desc')
                ->paginate($request->per_page ?? 10);

            $konversies->getCollection()->transform(function ($konversi) {
                $konversi->id_encrypted = encrypt($konversi->id);
                $konversi->kode_barang = $konversi->barang->kode_barang ?? '-';
                $konversi->nama_barang = $konversi->barang->nama_barang ?? '-';
                $konversi->satuan_asal_nama = $konversi->satuanAsal->nama_satuan ?? '-';
                $konversi->satuan_konversi_nama = $konversi->satuanKonversi->nama_satuan ?? '-';

                $konversi->dipakai_sebagai_asal = KonversiSatuanModel::where('id_barang', $konversi->id_barang)
                    ->where('id_satuan_asal', $konversi->id_satuan_konversi)
                    ->exists();

                return $konversi;
            });

            return response()->json($konversies);
        }
        $atribute =  'Konversi Satuan';
        return view('konversiSatuan.index', compact('atribute'));
    }

    function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'id_barang' => 'required',
                'id_satuan_asal' => 'required',
                'id_satuan_konversi' => 'required',
                'nilai_konversi' => 'required'
            ]);

            $validated['id_barang'] = decrypt($validated['id_barang']);
            $validated['id_satuan_asal'] = decrypt($validated['id_satuan_asal']);
            $validated['id_satuan_konversi'] = decrypt($validated['id_satuan_konversi']);

            $validated['created_by'] = auth()->user()->name;

            $konversi_satuan = KonversiSatuanModel::create($validated);


            return response()->json([
                'status' => 'success',
                'message' => 'Konversi berhasil disimpan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Konversi gagal disimpan' . $e,
            ]);
        }
    }

    function show($id)
    {
        try {
            $id = decrypt($id);
            $data = KonversiSatuanModel::find($id);
            $id_satuan_asal = encrypt($data->id_satuan_asal);
            // dd($data);
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'id_satuan_asal' => $id_satuan_asal
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'data' => $e,
            ]);
        }
    }

    public function update(Request $request, $idKonversi)
    {
        try {
            $validated = $request->validate([
                'id_barang' => 'required',
                'id_satuan_asal' => 'required',
                'id_satuan_konversi' => 'required',
                'nilai_konversi' => 'required'
            ]);

            // Dekripsi ID
            // $validated['id_barang'] = decrypt($validated['id_barang']);
            $validated['id_satuan_asal'] = decrypt($validated['id_satuan_asal']);
            $validated['id_satuan_konversi'] = decrypt($validated['id_satuan_konversi']);
            $validated['updated_by'] = auth()->user()->name;

            // Cari data konversi berdasarkan ID yang diberikan
            $idKonversi = decrypt($idKonversi);
            $konversi = KonversiSatuanModel::find($idKonversi);
            if (!$konversi) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data konversi tidak ditemukan.'
                ], 404);
            }

            // Update data
            $konversi->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Konversi berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate konversi: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {

        try {
            $barang = KonversiSatuanModel::find($id);
            $barang->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Konversi berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus Konversi: ' . $e->getMessage()
            ]);
        }
    }


    function getDataBarangKonversiSatuan(Request $request)
    {
        $search = $request->q;

        $latestIds = KonversiSatuanModel::whereHas('barang', function ($q) use ($search) {
            $q->where('nama_barang', 'like', "%{$search}%")
                ->orWhere('id', 'like', "%{$search}%");
        })
            ->groupBy('id_barang')
            ->select(DB::raw('MAX(id) as id'))
            ->pluck('id'); // get the IDs of the latest konversi_satuan per barang

        // Now fetch the full records with relations
        $data = KonversiSatuanModel::with(['barang', 'satuanAsal', 'satuanKonversi'])
            ->whereIn('id', $latestIds)
            ->get();


        $result = [];
        // $satuanArray = [];
        foreach ($data as $item) {
            $barang = encrypt($item->barang->id);
            if ($item->barang->pecah_satuan == 1) {
                $namaSatuan = $item->satuanAsal->nama_satuan;
            } else {
                if ($item->satuanKonversi) {
                    $namaSatuan = $item->satuanKonversi->nama_satuan;
                } else {
                    $namaSatuan = $item->satuanAsal->nama_satuan;
                }
            }

            $dataSatuan = KonversiSatuanModel::where('id_barang', $item->barang->id)
                ->orderBy('id', 'asc')
                ->get();
            $satuanArray = [];
            foreach ($dataSatuan as $satuanItem) {
                if ($item->barang->pecah_satuan == 1) {
                    $satuanArray[] = encrypt($satuanItem->satuanAsal->id);
                } else {
                    if ($satuanItem->satuanKonversi) {
                        $satuanArray[] = encrypt($satuanItem->satuanKonversi->id);
                    } else {
                        $satuanArray[] = encrypt($satuanItem->satuanAsal->id);
                    }
                }
            }

            $result[] = [
                'id' => $barang,
                'text' => $item->barang->nama_barang . ' - ' . $namaSatuan,
                'id_satuan' => implode(',', $satuanArray),
                'id_satuan2' => end($satuanArray)
            ];
        }

        // die();

        return response()->json($result);
    }

    function getSatuanKonversi(Request $request)
    {
        $search = $request->q;
        $id_satuan_asal = explode(',', $request->id_satuan_asal);
        $id_satuan_asal = array_map(function ($id) {
            return decrypt($id);
        }, $id_satuan_asal);


        $data = MasterSatuanModel::select('id', 'nama_satuan')
            ->where('nama_satuan', 'like', "%{$search}%")
            ->whereNotIn('id', $id_satuan_asal)
            ->get();

        $result = [];
        foreach ($data as $item) {
            $id_satuan = encrypt($item->id);
            $result[] = [
                'id' => $id_satuan,
                'text' => $item->nama_satuan
            ];
        }

        return response()->json($result);
    }

    function listBelumTerkonversi(Request $request)
    {
        $search = $request->search;

        $query = KonversiSatuanModel::with(['barang', 'satuanAsal'])
            ->whereNull('id_satuan_konversi')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('konversi_satuan as ks2')
                    ->whereColumn('ks2.id_barang', 'konversi_satuan.id_barang')
                    ->whereColumn('ks2.id_satuan_asal', 'konversi_satuan.id_satuan_asal')
                    ->whereNotNull('ks2.id_satuan_konversi');
            });

        if ($search) {
            $query->whereHas('barang', function ($qb) use ($search) {
                $qb->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        $konversies = $query->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 10);

        $konversies->getCollection()->transform(function ($konversi) {
            $konversi->kode_barang = $konversi->barang->kode_barang ?? '-';
            $konversi->nama_barang = $konversi->barang->nama_barang ?? '-';
            $konversi->satuan_asal_nama = $konversi->satuanAsal->nama_satuan ?? '-';
            return $konversi;
        });

        return response()->json($konversies);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new KonversiSatuanExport($request->search),
            'data_konversi_satuan_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(
            new KonversiSatuanExport($request->search),
            'data_konversi_satuan_' . date('Ymd_His') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf(Request $request)
    {
        $konversies = $this->getFilteredData($request->search);

        $pdf = Pdf::loadView('konversiSatuan.export_pdf', compact('konversies'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('data_konversi_satuan_' . date('Ymd_His') . '.pdf');
    }

    public function printPdf(Request $request)
    {
        $konversies = $this->getFilteredData($request->search);

        $pdf = Pdf::loadView('konversiSatuan.export_pdf', compact('konversies'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('data_konversi_satuan.pdf');
    }

    private function getFilteredData($search = null)
    {
        $query = KonversiSatuanModel::with(['barang', 'satuanAsal', 'satuanKonversi']);

        if ($search) {
            $query->whereHas('barang', function ($qb) use ($search) {
                $qb->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }
}
