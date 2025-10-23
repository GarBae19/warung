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
use Exception;
use Yajra\DataTables\Facades\DataTables;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $stock = StockOpnameHeaderModel::all();

            return DataTables::of($stock)

                ->addColumn('action', function ($stock) {
                    $id = encrypt($stock->id);
                    $kode_stock_opname = encrypt($stock->kode_stock_opname);

                    $buttons = '
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cog"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $id . '">
                                <a href="' . route('stockOpname.show', $id) . '" class="dropdown-item text-info">
                                    <i class="fas fa-eye"></i> Show
                                </a>';

                    if ($stock->approve_by == null) {
                        $buttons .= '
                                <button type="button" class="dropdown-item text-success btn-approve" data-id="' . $kode_stock_opname . '">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="button" class="dropdown-item text-danger btn-delete" data-id="' . $kode_stock_opname . '">
                                    <i class="fas fa-trash"></i> Delete
                                </button>';
                    }

                    $buttons .= '
                            </div>
                        </div>';

                    return $buttons;
                })

                ->rawColumns(['action'])
                ->make(true);
        }
        $atribute =  'Stock Opname';
        return view('stockOpname.index', compact('atribute')); // Memanggil view home.blade.php
    }

    function getDataBarang(Request $request)
    {
        $search = $request->q;

        $data = MasterBarangModel::select('kode_barang', 'nama_barang')
            ->where('nama_barang', 'like', "%{$search}%")
            ->orWhere('kode_barang', 'like', "%{$search}%")
            ->get();

        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'id' => $item->kode_barang,
                'text' => $item->nama_barang
            ];
        }

        return response()->json($result);
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
            ]);



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
        return view('stockOpname.show', compact('stockOpname', 'atribute'));
    }

    function detail(Request $request)
    {
        if ($request->ajax()) {
            $kode_stock_opname = $request->kode_stock_opname;
            $stockOpnameHeader = StockOpnameHeaderModel::where('kode_stock_opname', $kode_stock_opname)->first();
            $barangs = MasterBarangModel::with(['stockOpnameDetail' => function ($q) use ($kode_stock_opname) {
                $q->where('kode_stock_opname', $kode_stock_opname);
            }])->get();

            return DataTables::of($barangs)
                ->addColumn('qty_stock', function ($barang) {
                    // karena hasMany, hasilnya collection
                    return $barang->stockReal ? $barang->stockReal->qty : 0;
                })
                ->addColumn('qty_real', function ($barang) {
                    $detail = $barang->stockOpnameDetail->first();
                    return $detail ? $detail->qty : 0;
                    // pastikan relasi stock ada
                })

                ->addColumn('action', function ($barangs)  use ($stockOpnameHeader) {
                    $buttonDetail = '
                        <button class="btn btn-sm btn-primary btn-scan" data-id="' . $barangs->kode_barang . '">Scan</button>
                        <button class="btn btn-sm btn-danger btn-reset" data-id="' . $barangs->kode_barang . '">Reset</button>
                        ';
                    if ($stockOpnameHeader->approve_by != null) {
                        $buttonDetail = '';
                    }
                    return $buttonDetail;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    function scan(Request $request)
    {
        $validated = $request->validate([
            'kode_stock_opname' => 'required',
            'kode_barang' => 'required',
            'qty' => 'required|integer|min:1',
        ]);

        try {
            $stockOpnameDetail = StockOpnameDetailModel::updateOrCreate(
                [
                    'kode_stock_opname' => $validated['kode_stock_opname'],
                    'kode_barang' => $validated['kode_barang'],
                ],
                ['qty' => $validated['qty']]
            );
            $barcodes = explode(',', $request->barcode);
            foreach ($barcodes as $barcode) {
                $barcode_barang = BarcodeBarangModel::create([
                    'barcode' => $barcode,
                    'kode_barang' => $validated['kode_barang'],
                ]);
            }


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
            $kode_barang = $request->kode_barang;

            StockOpnameDetailModel::where('kode_stock_opname', $kode_stock_opname)
                ->where('kode_barang', $kode_barang)
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

            $stockOpnameDetail = StockOpnameDetailModel::where('kode_stock_opname', $id)->first();
            $stockOpnameDetail->delete();
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
            $stockOpnameDetail = StockOpnameDetailModel::where('kode_stock_opname', $id)->get();
            if ($stockOpnameDetail->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada data untuk diapprove'], 400);
            }

            foreach ($stockOpnameDetail as $data) {
                $stock = StockModel::where('kode_barang', $data->kode_barang)->first();

                if ($stock) {
                    // simpan stok lama sebelum diupdate
                    $stokLama = $stock->qty;

                    // update ke qty hasil opname
                    $stock->qty = $data->qty;
                    $stock->save();
                } else {
                    // jika belum ada, buat baru
                    $stokLama = 0;

                    $stock = StockModel::create([
                        'kode_barang' => $data->kode_barang,
                        'qty' => $data->qty,
                    ]);
                }

                // hitung selisih
                $qty_mutasi = $data->qty - $stokLama;

                // simpan mutasi
                $mutasi = MutasiBarangModel::create([
                    'kode_transaksi' => $id,
                    'kode_barang'    => $data->kode_barang,
                    'qty'            => $qty_mutasi,
                    'keterangan'     => 'Stock Opname',
                    'tanggal'        => now(),
                ]);

                if (!$mutasi) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Gagal mencatat mutasi untuk barang dengan kode ' . $data->kode_barang
                    ], 500);
                }
            }

            $stockOpname = StockOpnameHeaderModel::where('kode_stock_opname', $id)->first();

            if (!$stockOpname) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }

            $stockOpname->approve_by = 'admin'; // misalnya ada kolom status
            $stockOpname->approve_at = now();
            $stockOpname->save();

            return response()->json(['status' => 'success', 'message' => 'Stock opname berhasil diapprove']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal approve stock opname: ' . $e->getMessage()], 500);
        }
    }
}
