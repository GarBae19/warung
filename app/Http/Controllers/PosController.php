<?php

namespace App\Http\Controllers;

// use App\Models\MasterBarangModel;

use App\Models\KonversiSatuanModel;
use App\Models\MasterBarangModel;
use App\Models\MasterDetailStdHargaJual;
use App\Models\MasterHeaderStdHargaBeliModel;
use App\Models\PiutangModel;
use App\Models\MasterDetailStdHargaBeliModel;
use App\Models\MutasiBarangModel;
use App\Models\PosModel;
use App\Models\StockModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Mpdf\Mpdf;
use Exception;

class PosController extends Controller
{
    function index()
    {
        $atribute = 'POS (Point Of Sales)';
        $id_cabang = 1;

        // Eager load lebih dalam supaya Blade tidak lazy-load stockReal & satuan (hilangkan N+1 di view)
        $barangs = MasterDetailStdHargaJual::with([
            'headerHargaJual.barang.stockReal',
            'headerHargaJual.satuan',
        ])
            ->join('master_header_std_harga_jual as h', 'h.id', '=', 'master_detail_std_harga_jual.id_header_std_harga_jual')
            ->join('master_barang as b', 'b.id', '=', 'h.id_barang')
            ->where('id_cabang', $id_cabang)
            ->orderBy('b.nama_barang')
            ->select('master_detail_std_harga_jual.*', 'h.id_barang')
            ->get();

        // Ambil SEMUA konversi satuan yang relevan dalam 1 query (bukan per-item)
        $idBarangList = $barangs->pluck('id_barang')->unique()->values();
        $konversiAll = KonversiSatuanModel::whereIn('id_barang', $idBarangList)->get();

        // Bangun lookup: "id_barang-id_satuan" => row konversi
        $konversiLookup = [];
        foreach ($konversiAll as $k) {
            $satuanKey = $k->id_satuan_konversi ?? $k->id_satuan_asal;
            $konversiLookup[$k->id_barang . '-' . $satuanKey] = $k;
        }

        foreach ($barangs as $barang) {
            $key = $barang->id_barang . '-' . $barang->headerHargaJual->id_satuan;
            $barang->KonversiSatuan = $konversiLookup[$key] ?? null;
        }

        $barang2 = MasterBarangModel::where('active', 1)->orderBy('nama_barang', 'asc')->get();

        // Group sekali saja, bukan filter berulang di dalam loop
        $groupedBarangs = $barangs->groupBy('id_barang');

        $barang3 = [];
        foreach ($barang2 as $brg) {
            $barang3[$brg->id] = [
                'nama_barang' => $brg->nama_barang,
                'items' => ($groupedBarangs[$brg->id] ?? collect())->values(),
                'gambar' => $brg->gambar
            ];
        }

        return view('pos.index', compact('atribute', 'barang3'));
    }

    function getBarangList()
    {
        $id_cabang = 1;

        $barangs = MasterDetailStdHargaJual::with([
            'headerHargaJual.barang.stockReal',
            'headerHargaJual.satuan',
        ])
            ->join('master_header_std_harga_jual as h', 'h.id', '=', 'master_detail_std_harga_jual.id_header_std_harga_jual')
            ->join('master_barang as b', 'b.id', '=', 'h.id_barang')
            ->where('id_cabang', $id_cabang)
            ->orderBy('b.nama_barang')
            ->select('master_detail_std_harga_jual.*', 'h.id_barang')
            ->get();

        $idBarangList = $barangs->pluck('id_barang')->unique()->values();
        $konversiAll = KonversiSatuanModel::whereIn('id_barang', $idBarangList)->get();

        $konversiLookup = [];
        foreach ($konversiAll as $k) {
            $satuanKey = $k->id_satuan_konversi ?? $k->id_satuan_asal;
            $konversiLookup[$k->id_barang . '-' . $satuanKey] = $k;
        }

        foreach ($barangs as $barang) {
            $key = $barang->id_barang . '-' . $barang->headerHargaJual->id_satuan;
            $barang->KonversiSatuan = $konversiLookup[$key] ?? null;
        }

        $barang2 = MasterBarangModel::all();
        $groupedBarangs = $barangs->groupBy('id_barang');

        $barang3 = [];
        foreach ($barang2 as $brg) {
            $barang3[$brg->id] = [
                'nama_barang' => $brg->nama_barang,
                'items' => ($groupedBarangs[$brg->id] ?? collect())->values(),
                'gambar' => $brg->gambar
            ];
        }

        return view('pos.barang-list', compact('barang3'));
    }

    function prosesPembayaran(Request $request)
    {
        try {
            $kode_pos = $this->getKodePos();
            $kode_pos = $kode_pos->original['kodeTransaksi'];
            $items = $request->input('items');
            $piutang = $request->input('piutang');
            $namaPiutang = $request->input('namaPiutang');
            // dd($piutang);
            foreach ($items as $item) {
                $idBarang = decrypt($item['idBarang']);
                $amount = $item['qty'] * $item['harga'];
                $harga_beli = MasterDetailStdHargaBeliModel::where('id_cabang', 1)
                    ->whereHas('headerStdHargaBeli', function ($query) use ($idBarang, $item) {
                        $query->where('id_barang', $idBarang)->where('id_satuan', $item['idSatuan']);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
                // dd($harga_beli);
                $amount_harga_beli = $item['qty'] * ($harga_beli ? $harga_beli->harga_beli : 0);
                // dd($amount_harga_beli);




                $pos = PosModel::create([
                    'kode_pos' => $kode_pos,
                    'id_barang' => $idBarang,
                    'id_satuan' => $item['idSatuan'],
                    'id_cabang' => 1,
                    'qty' => (int) $item['qty'],
                    'nilai_konversi' => (int) $item['nilaiKonversi'],
                    'qty_konversi' => (int) $item['qty'] * (int) $item['nilaiKonversi'],
                    'harga' => (int) $item['harga'],
                    'amount' => $amount,
                    'created_by' => auth()->user()->name,
                    'piutang' => $piutang,
                    'harga_beli' => $harga_beli ? $harga_beli->harga_beli : 0,
                    'amount_harga_beli' => $amount_harga_beli
                ]);

                // dd($pos->id);

                if ($piutang == 1) {
                    PiutangModel::create([
                        'id_pos' => $pos->id,
                        'debitur' => $namaPiutang,
                    ]);
                }

                $qtyMutasi = $item['qty'] * $item['nilaiKonversi'];
                MutasiBarangModel::create([
                    'kode_transaksi' => $kode_pos,
                    'id_barang' => $idBarang,
                    'qty' => $qtyMutasi * -1,
                    'keterangan' => 'Penjualan POS - ' . $kode_pos,
                    'tanggal' => now(),
                ]);

                $qtyStock = StockModel::where('id_barang', $idBarang)->first();
                $newQty = $qtyStock->qty - $qtyMutasi;
                StockModel::where('id_barang', $idBarang)->update(['qty' => $newQty]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil diproses!',
                'kode_pos' => $kode_pos
            ]);
            // return response()->json(['message' => 'Pembayaran berhasil diproses!']);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembayaran gagal diproses!' . $e->getMessage()
            ], 500);
        }
    }


    function getKodePos()
    {

        try {
            $bulan = date('m');
            $tahun = date('Y');

            $last = PosModel::where('kode_pos', 'like', "TS/$bulan/$tahun/%")
                ->orderBy('id', 'desc')
                ->first();

            if ($last) {
                $parts = explode('/', $last->kode_pos);
                $lastNumber = (int) end($parts);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $kodeTransaksi = 'TS/' . $bulan . '/' . $tahun . '/' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            return response()->json([
                'status' => 'success',
                'kodeTransaksi' => $kodeTransaksi,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'kodeTransaksi' => $e,
            ]);
        }
    }


    function strukPos(Request $request)
    {
        $transaksi = PosModel::where('kode_pos', $request->kode_pos)->get();

        $total = $request->total;
        $bayar = $request->bayar;
        $kembali = $request->kembali;
        $kode = $request->kode_pos;

        $html = view('pos.struk', compact('transaksi', 'total', 'bayar', 'kembali', 'kode'))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [58, 200], // struk 80mm
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
        ]);

        $mpdf->WriteHTML($html);

        // TAMPIL DI IFRAME
        return response($mpdf->Output('', 'I'))
            ->header('Content-Type', 'application/pdf');
    }

    function piutang()
    {
        $atribute = 'POS (Point Of Sales)';
        // $piutangs = PiutangModel::with('pos.headerHargaJual.barang')->get();

        return view('pos.piutang', compact('atribute'));
    }

    function dataPiutang(Request $request)
    {
        if (request()->ajax()) {
            $dataPenjualan = DB::table('pos as p')

                // HEADER HARGA JUAL
                ->join('master_header_std_harga_jual as hjh', function ($join) {
                    $join->on('p.id_barang', '=', 'hjh.id_barang')
                        ->on('p.id_satuan', '=', 'hjh.id_satuan');
                })

                // DETAIL HARGA JUAL
                ->join('master_detail_std_harga_jual as hjd', function ($join) {
                    $join->on('hjh.id', '=', 'hjd.id_header_std_harga_jual');
                })

                // HEADER HARGA BELI
                ->join('master_header_std_harga_beli as hbh', function ($join) {
                    $join->on('p.id_barang', '=', 'hbh.id_barang')
                        ->on('p.id_satuan', '=', 'hbh.id_satuan');
                })

                // DETAIL HARGA BELI
                ->join('master_detail_std_harga_beli as hbd', function ($join) {
                    $join->on('hbh.id', '=', 'hbd.id_header_std_harga_beli');
                })

                ->join('master_barang as mb', 'p.id_barang', '=', 'mb.id')
                ->join('master_satuan as ms', 'p.id_satuan', '=', 'ms.id')

                ->join('piutang as pi', 'pi.id_pos', '=', 'p.id')

                ->where('p.piutang', 1)

                ->selectRaw('
                        pi.debitur,
                        sum(amount) as total_piutang,
                        MAX(pi.created_at) as created_at
                    ')
                ->groupBy('pi.debitur');
            if ($request->start_date && $request->end_date) {
                $dataPenjualan->whereBetween(DB::raw('DATE(pi.created_at)'), [
                    $request->start_date,
                    $request->end_date,
                ]);
            } else {
                // Default: hari ini jika tidak ada filter
                $dataPenjualan->whereDate('pi.created_at', now());
            }

            $dataPenjualan = $dataPenjualan->orderBy('pi.created_at', 'desc')->get();

            // dd($dataPenjualan);


            return DataTables::of($dataPenjualan)
                ->addIndexColumn()

                ->addColumn('nama_pelanggan', function ($row) {
                    return $row->debitur ?? '-';
                })
                ->addColumn('tanggal', function ($row) {
                    return date('d-m-Y', strtotime($row->created_at));
                })
                ->addColumn('total_piutang', function ($row) {
                    return 'Rp. ' . number_format($row->total_piutang, 0, ',', '.');
                })
                ->addColumn('aksi', function ($row) {
                    return '<button class="btn btn-sm btn-primary bayar-piutang" data-debitur="' . $row->debitur . '" data-total="' . $row->total_piutang . '">Bayar</button>';
                })

                ->rawColumns(['aksi'])

                ->make(true);
        }
    }

    public function prosesPembayaranPiutang(Request $request)
    {
        try {
            $debitur = $request->input('debitur');
            $totalPiutang = $request->input('totalPiutang');

            // Lakukan proses pembayaran piutang sesuai kebutuhan Anda
            // Misalnya, update status piutang, catat pembayaran, dll.
            PosModel::whereHas('piutang', function ($query) use ($debitur) {
                $query->where('debitur', $debitur);
            })->update(['piutang' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran piutang berhasil diproses untuk debitur: ' . $debitur . ' dengan total: Rp. ' . number_format($totalPiutang, 0, ',', '.'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran piutang gagal diproses: ' . $e->getMessage(),
            ], 500);
        }
    }
}
