<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterJenisBarangModel;
use App\Models\MasterSatuanModel;
use App\Models\MasterBrandModel;
use App\Models\MasterBarangModel;
use App\Models\StockOpnameHeaderModel;
use App\Models\StockOpnameDetailModel;
use App\Models\BarcodeBarangModel;
use App\Models\MutasiBarangModel;
use App\Models\StockModel;
use App\Models\KonversiSatuanModel;
use Exception;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Exports\StockOpnameExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;


class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = $request->search;

            $query = StockOpnameHeaderModel::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_stock_opname', 'like', "%{$search}%")
                        ->orWhere('periode', 'like', "%{$search}%")
                        ->orWhere('approve_by', 'like', "%{$search}%");
                });
            }

            $stocks = $query->orderBy('id', 'desc')
                ->paginate($request->per_page ?? 10);

            $stocks->getCollection()->transform(function ($stock) {
                $stock->id_encrypted = encrypt($stock->id);
                $stock->kode_encrypted = encrypt($stock->kode_stock_opname);
                return $stock;
            });

            return response()->json($stocks);
        }
        $atribute =  'Stock Opname';
        return view('stockOpname.index', compact('atribute'));
    }



    function getKodeStockOpname()
    {
        try {
            $last = StockOpnameHeaderModel::orderBy('kode_stock_opname', 'desc')->first();

            if ($last) {
                // Ambil angka dari belakang kode_jenis, misal BRD0005 → 5
                $lastNumber = (int) substr($last->kode_stock_opname, 3);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $kodeStockOpname = 'STO' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'success',
                'kodeStockOpname' => $kodeStockOpname,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodeStockOpname' => $e,
            ]);
        }
    }

    function create()
    {
        // dd('Create Stock');
        $kodeStockOpname = $this->getKodeStockOpname();
        $kodeStockOpname = json_decode($kodeStockOpname->getContent(), true);
        if ($kodeStockOpname['status'] == 'success') {
            $kodeStockOpname = $kodeStockOpname['kodeStockOpname'];
            $atribute =  'Stock Opname';
            return view('stockOpname.create', compact('kodeStockOpname', 'atribute'));
        } else {
            return redirect()->back()->with('error', 'Gagal mendapatkan kode stock opname');
        }
    }

    function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_stock_opname' => 'required|unique:stock_opname_header,kode_stock_opname',
                'periode' => 'required',
                'id_gudang' => 'required',
            ]);

            $validated['created_by'] = auth()->user()->name;

            $stockOpnameHeader = StockOpnameHeaderModel::create($validated);

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
        $id = decrypt($id); // karena di encrypt di index
        $stockOpname = StockOpnameHeaderModel::findOrFail($id);
        $atribute =  'Stock Opname';
        $gudang = $stockOpname->gudang->nama_gudang;
        return view('stockOpname.show', compact('stockOpname', 'atribute', 'gudang'));
    }

    function detail(Request $request)
    {
        if ($request->ajax()) {
            $kode_stock_opname = $request->kode_stock_opname;
            $search = is_string($request->search) ? $request->search : null; // ⬅️ guard

            $stockOpnameHeader = StockOpnameHeaderModel::where('kode_stock_opname', $kode_stock_opname)->first();

            $query = KonversiSatuanModel::with(['barang', 'satuanAsal', 'satuanKonversi']);

            if ($search) {
                $query->whereHas('barang', function ($q) use ($search) {
                    $q->where('kode_barang', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            }

            $barangs = $query->orderBy('id', 'desc')
                ->paginate($request->per_page ?? 10);

            $barangs->getCollection()->transform(function ($barang) use ($stockOpnameHeader, $kode_stock_opname) {
                $barang->kode_barang = $barang->barang->kode_barang;
                $barang->nama_barang = $barang->barang->nama_barang;
                $barang->nama_satuan = $barang->satuanKonversi->nama_satuan ?? $barang->satuanAsal->nama_satuan;
                $barang->qty_stock = $this->hitungQtyStock($barang);
                $barang->qty_real = $this->hitungQtyReal($barang, $kode_stock_opname);

                if ($barang->satuanKonversi) {
                    $barang->satuan_encrypted = encrypt($barang->satuanKonversi->id);
                } else {
                    $barang->satuan_encrypted = encrypt($barang->satuanAsal->id);
                }
                $barang->barang_encrypted = encrypt($barang->barang->id);
                $barang->can_edit = $stockOpnameHeader->approve_by == null;

                return $barang;
            });

            return response()->json($barangs);
        }
    }

    private function hitungQtyStock($barang)
    {
        // 1. stok dasar
        $stokDasar = $barang->barang->stockReal->qty ?? 0;

        if ($stokDasar <= 0) {
            return 0;
        }

        // 2. ambil konversi
        $konversi = $barang->barang->konversiSatuan()
            ->whereNotNull('id_satuan_konversi')
            ->with(['satuanAsal', 'satuanKonversi'])
            ->get();

        if ($konversi->isEmpty()) {
            return $stokDasar;
        }

        // 3. bangun graph satuan
        $map = [];
        $asalIds = [];
        $tujuanIds = [];

        foreach ($konversi as $k) {
            $map[$k->id_satuan_asal] = $k;
            $asalIds[] = $k->id_satuan_asal;
            $tujuanIds[] = $k->id_satuan_konversi;
        }

        $satuanDasarId = array_values(array_diff($asalIds, $tujuanIds))[0] ?? null;

        if (!$satuanDasarId) {
            return $stokDasar;
        }

        // 4. susun urutan dasar → terbesar
        $urutan = [];
        $current = $satuanDasarId;

        while (isset($map[$current])) {
            $urutan[] = $map[$current];
            $current = $map[$current]->id_satuan_konversi;
        }

        // 5. hitung nilai kumulatif ke satuan dasar
        $nilaiKeDasar = [];
        $nilaiKeDasar[$satuanDasarId] = 1;

        foreach ($urutan as $k) {
            $nilaiKeDasar[$k->id_satuan_konversi] =
                $nilaiKeDasar[$k->id_satuan_asal] * $k->nilai_konversi;
        }

        // 6. hitung stok dari terbesar → terkecil
        $sisa = $stokDasar;
        $output = [];

        foreach (array_reverse($urutan) as $k) {
            $idSatuan = $k->id_satuan_konversi;
            $nilaiDasar = $nilaiKeDasar[$idSatuan];

            $qty = intdiv($sisa, $nilaiDasar);
            $sisa = $sisa % $nilaiDasar;

            $output[$k->satuanKonversi->nama_satuan] = $qty;
        }

        $namaDasar = optional($urutan[0]->satuanAsal)->nama_satuan
            ?? $barang->barang->satuan?->nama_satuan
            ?? '-';

        $output[$namaDasar] = $sisa;

        $namaSatuanFinal = $barang->satuanKonversi->nama_satuan ?? $barang->satuanAsal->nama_satuan;

        return $output[$namaSatuanFinal] ?? 0;
    }

    private function hitungQtyReal($barang, $kode_stock_opname)
    {
        $satuan = $barang->satuanKonversi->id ?? $barang->satuanAsal->id;

        $qtyReal = StockOpnameDetailModel::where('kode_stock_opname', $kode_stock_opname)
            ->where('id_barang', $barang->id_barang)
            ->where('id_satuan', $satuan)
            ->first();

        return $qtyReal->qty ?? 0;
    }

    function scan(Request $request)
    {
        $validated = $request->validate([
            'kode_stock_opname' => 'required',
            'id_barang' => 'required',
            'qty' => 'required|integer|min:1',
            'id_satuan' => 'required'
        ]);

        try {
            $stockOpnameDetail = StockOpnameDetailModel::updateOrCreate(
                [
                    'kode_stock_opname' => $validated['kode_stock_opname'],
                    'id_barang' => decrypt($validated['id_barang']),
                    'id_satuan' => decrypt($validated['id_satuan'])
                ],
                ['qty' => $validated['qty']]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data gagal disimpan: ' . $e->getMessage(),
            ]);
        }
    }

    public function reset(Request $request)
    {
        try {
            $kode_stock_opname = $request->kode_stock_opname;
            $id_barang = decrypt($request->id_barang);
            $id_satuan = decrypt($request->id_satuan);

            StockOpnameDetailModel::where('kode_stock_opname', $kode_stock_opname)
                ->where('id_barang', $id_barang)
                ->where('id_satuan', $id_satuan)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Barang stock opname berhasil direset'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang stock opname gagal di reset: ' . $e->getMessage(),
            ]);
        }
    }

    function destroy($id)
    {
        try {
            $id = decrypt($id);
            $stockOpnameHeader = StockOpnameHeaderModel::where('kode_stock_opname', $id)->first();
            $stockOpnameHeader->delete();

            $stockOpnameDetail = StockOpnameDetailModel::where('kode_stock_opname', $id)->count();
            if ($stockOpnameDetail > 0) {
                $stockOpnameDetail = StockOpnameDetailModel::where('kode_stock_opname', $id)->first();
                $stockOpnameDetail->delete();
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Stock opname berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stock opname gagal dihapus: ' . $e->getMessage(),
            ]);
        }
    }

    function approve($id)
    {

        try {
            $id = decrypt($id);

            $details = StockOpnameDetailModel::where('kode_stock_opname', $id)->get();
            if ($details->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data untuk diapprove'
                ], 400);
            }

            foreach ($details as $data) {

                // 🔹 konversi ke satuan dasar
                $qtyDasar = $this->convertToBase(
                    $data->id_barang,
                    $data->qty,
                    $data->id_satuan
                );

                $stock = StockModel::where('id_barang', $data->id_barang)->lockForUpdate()->first();

                if ($stock) {
                    $stokLama = $stock->qty;
                    $stock->qty = $qtyDasar + $stokLama; // ✅ PAKAI QTY DASAR
                    $stock->save();
                } else {
                    $stokLama = 0;
                    StockModel::create([
                        'id_barang' => $data->id_barang,
                        'qty'       => $qtyDasar + $stokLama, // ✅
                    ]);
                }

                // 🔹 selisih DALAM SATUAN DASAR
                $qty_mutasi = $qtyDasar;

                MutasiBarangModel::create([
                    'kode_transaksi' => $id,
                    'id_barang'      => $data->id_barang,
                    'qty'            => $qty_mutasi, // ✅
                    'keterangan'     => 'Stock Opname',
                    'tanggal'        => now(),
                ]);
            }

            StockOpnameHeaderModel::where('kode_stock_opname', $id)->update([
                'approve_by' => auth()->user()->name,
                'approve_at' => now(),
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'Stock opname berhasil diapprove'
            ]);
        } catch (Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal approve stock opname: ' . $e->getMessage()
            ], 500);
        }
    }


    function convertToBase($id_barang, $qty, $current_satuan_id)
    {
        $barang = MasterBarangModel::find($id_barang);
        if (!$barang) {
            throw new Exception("Barang tidak ditemukan");
        }

        $baseUnitId = $barang->id_satuan;

        // Jika sudah satuan dasar, langsung return
        if ($current_satuan_id == $baseUnitId) {
            return $qty;
        }

        $total = $qty;
        $current = $current_satuan_id;
        $visited = [];

        while (true) {
            // 🔹 Proteksi loop
            if (in_array($current, $visited)) {
                throw new Exception("Loop konversi satuan terdeteksi: " . implode(' -> ', $visited) . " -> $current");
            }
            $visited[] = $current;

            // 🔹 Jika sudah di satuan dasar, selesai
            if ($current == $baseUnitId) {
                break;
            }

            // 🔹 Ambil konversi
            $konversi = KonversiSatuanModel::where('id_barang', $id_barang)
                ->where('id_satuan_konversi', $current)
                ->first();

            if (!$konversi) {
                throw new Exception("Konversi satuan $current tidak ditemukan");
            }

            $total *= $konversi->nilai_konversi;

            // pindah ke parent
            $current = $konversi->id_satuan_asal;
        }

        return $total;
    }

    // ==== Export & Print ====

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new StockOpnameExport($request->search),
            'data_stock_opname_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(
            new StockOpnameExport($request->search),
            'data_stock_opname_' . date('Ymd_His') . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf(Request $request)
    {
        $stocks = $this->getFilteredData($request->search);

        $pdf = Pdf::loadView('stockOpname.export_pdf', compact('stocks'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('data_stock_opname_' . date('Ymd_His') . '.pdf');
    }

    public function printPdf(Request $request)
    {
        $stocks = $this->getFilteredData($request->search);

        $pdf = Pdf::loadView('stockOpname.export_pdf', compact('stocks'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('data_stock_opname.pdf');
    }

    private function getFilteredData($search = null)
    {
        $query = StockOpnameHeaderModel::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_stock_opname', 'like', "%{$search}%")
                    ->orWhere('periode', 'like', "%{$search}%")
                    ->orWhere('approve_by', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }
}
