<?php

namespace App\Http\Controllers;

use App\Models\MasterBarangModel;
use App\Models\PosModel;
use App\Models\StockModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
// use App\Models\MenusModel;

class HomeController extends Controller
{
    public function index()
    {
        // $stock = MasterBarangModel::with('stockReal')
        //     ->whereHas('stockReal', function ($query) {
        //         $query->whereColumn('qty', '<', 'master_barang.min_stock');
        //     })
        //     ->count();
        $stock = MasterBarangModel::with('stockReal')
            ->where(function ($q) {

                // ada stock_real tapi qty < min_stock
                $q->whereHas('stockReal', function ($query) {
                    $query->whereColumn('qty', '<', 'master_barang.min_stock');
                })

                    // tidak ada data stock_real sama sekali
                    ->orWhereDoesntHave('stockReal');
            })
            ->where('active', 1)
            ->count();
        $sales = PosModel::whereDate('created_at', now())->count();
        $sales = PosModel::whereDate('created_at', now())->count();
        $sumsales = DB::table('pos as p')

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

            ->whereDate('p.created_at', now())
            ->where('p.piutang', 0)
            ->selectRaw('
            SUM(
                p.amount -
                CASE
                    WHEN p.amount_harga_beli = 0
                        THEN hbd.harga_beli * p.qty
                    ELSE p.amount_harga_beli
                END
            ) as total_keuntungan
        ')
            ->first();

        $sumpiutang = DB::table('pos as p')

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

            ->whereDate('p.created_at', now())
            ->where('p.piutang', 1)

            ->selectRaw('
              SUM(
                p.amount 
            ) as total_piutang
        ')
            ->first();
        $sumsales = $sumsales->total_keuntungan ?? 0;
        $sumpiutang = $sumpiutang->total_piutang ?? 0;
        $atribute =  'Dashboard';
        return view('home.index', compact('atribute', 'stock', 'sales', 'sumsales', 'sumpiutang')); // Memanggil view home.blade.php
    }

    function stockHabis(Request $request)
    {

        if ($request->ajax()) {

            // $stockHabis = MasterBarangModel::with('stockReal')
            //     ->whereHas('stockReal', function ($query) {
            //         $query->whereColumn('qty', '<', 'master_barang.min_stock');
            //     })
            //     ->get();
            $stockHabis = MasterBarangModel::with('stockReal')
                ->where(function ($q) {

                    // ada stock_real tapi qty < min_stock
                    $q->whereHas('stockReal', function ($query) {
                        $query->whereColumn('qty', '<', 'master_barang.min_stock');
                    })

                        // tidak ada data stock_real sama sekali
                        ->orWhereDoesntHave('stockReal');
                })
                ->where('active', 1)
                ->get();

            return DataTables::of($stockHabis)
                ->addColumn('item_kode', function ($stockHabis) {
                    return $stockHabis->kode_barang ?? '-';
                })
                ->addColumn('item_nama', function ($stockHabis) {
                    return $stockHabis->nama_barang ?? '-';
                })
                ->addColumn('min_stock', function ($stockHabis) {
                    return $stockHabis->min_stock ?? '-';
                })
                ->addColumn('stock', function ($stockHabis) {
                    return $stockHabis->stockReal->qty ?? '0';
                })
                ->make(true);
        }
    }

    function salesInfo(Request $request)
    {

        if ($request->ajax()) {
            $sales = PosModel::whereDate('created_at', now())->get();


            return DataTables::of($sales)
                ->addColumn('item_kode', function ($sales) {
                    return $sales->id_barang ?? '-';
                })
                ->addColumn('item_nama', function ($sales) {
                    return $sales->barang->nama_barang ?? '-';
                })
                ->addColumn('qty', function ($sales) {
                    return $sales->qty ?? '-';
                })
                ->make(true);
        }
    }

    function sumSalesInfo(Request $request)
    {
        if ($request->ajax()) {

            $sumsales = DB::table('pos as p')

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

                ->whereDate('p.created_at', now())
                ->where('p.piutang', 0)

                ->selectRaw('
                        p.id_barang,
                        p.id_satuan,
                        case
                            when p.harga_beli > 0 then p.harga_beli
                            else hbd.harga_beli
                        end as harga_beli,
                        case
                            when p.harga > 0 then p.harga
                            else hjd.harga_jual
                        end as harga_jual,
                        mb.nama_barang,
                        ms.nama_satuan,
                        p.qty
                    ')

                ->get();
            $grandTotalBeli = $sumsales->sum(function ($row) {
                return $row->harga_beli * $row->qty;
            });

            $grandTotalJual = $sumsales->sum(function ($row) {
                return $row->harga_jual * $row->qty;
            });
            return DataTables::of($sumsales)
                ->addColumn('item_nama', function ($row) {
                    return $row->nama_barang ?? '-';
                })
                ->addColumn('satuan', function ($row) {
                    return $row->nama_satuan ?? '-';
                })
                ->addColumn('qty', function ($row) {
                    return $row->qty ?? '-';
                })
                ->addColumn('harga_beli', function ($row) {
                    return $row->harga_beli ?? '-';
                })
                ->addColumn('harga_jual', function ($row) {
                    return $row->harga_jual ?? '-';
                })

                ->addcolumn('total_beli', function ($row) {
                    $total_beli = $row->harga_beli * $row->qty;
                    return 'Rp ' . number_format($total_beli, 0, ',', '.');
                })
                ->addcolumn('total_jual', function ($row) {
                    $total_jual = $row->harga_jual * $row->qty;
                    return 'Rp ' . number_format($total_jual, 0, ',', '.');
                })
                ->with([
                    'grand_total_beli' => $grandTotalBeli,
                    'grand_total_jual' => $grandTotalJual,
                    'grand_profit'     => $grandTotalJual - $grandTotalBeli,

                ])

                ->make(true);
        }
    }

    function sumPiutangInfo(Request $request)
    {
        if ($request->ajax()) {
            $sumpiutang = DB::table('pos as p')

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

                ->whereDate('p.created_at', now())
                ->where('p.piutang', 1)

                ->selectRaw('
                        p.id_barang,
                        p.id_satuan,
                        case
                            when p.harga_beli > 0 then p.harga_beli
                            else hbd.harga_beli
                        end as harga_beli,
                        case
                            when p.harga > 0 then p.harga
                            else hjd.harga_jual
                        end as harga_jual,
                        mb.nama_barang,
                        ms.nama_satuan,
                        p.qty
                    ')

                ->get();

            $grandTotalBeli = $sumpiutang->sum(function ($row) {
                return $row->harga_beli * $row->qty;
            });

            $grandTotalJual = $sumpiutang->sum(function ($row) {
                return $row->harga_jual * $row->qty;
            });
            return DataTables::of($sumpiutang)
                ->addColumn('item_nama', function ($row) {
                    return $row->nama_barang ?? '-';
                })
                ->addColumn('satuan', function ($row) {
                    return $row->nama_satuan ?? '-';
                })
                ->addColumn('qty', function ($row) {
                    return $row->qty ?? '-';
                })
                ->addColumn('harga_beli', function ($row) {
                    return $row->harga_beli ?? '-';
                })
                ->addColumn('harga_jual', function ($row) {
                    return $row->harga_jual ?? '-';
                })

                ->addcolumn('total_beli', function ($row) {
                    $total_beli = $row->harga_beli * $row->qty;
                    return 'Rp ' . number_format($total_beli, 0, ',', '.');
                })
                ->addcolumn('total_jual', function ($row) {
                    $total_jual = $row->harga_jual * $row->qty;
                    return 'Rp ' . number_format($total_jual, 0, ',', '.');
                })
                ->with([
                    'grand_total_beli' => $grandTotalBeli,
                    'grand_total_jual' => $grandTotalJual,
                    'grand_piutang'     => $grandTotalJual,

                ])

                ->make(true);
        }
    }

    function countBarangHabis()
    {
        // $stockHabis = MasterBarangModel::with('stockReal')
        //     ->whereHas('stockReal', function ($query) {
        //         $query->whereColumn('qty', '<', 'master_barang.min_stock');
        //     })
        //     ->count();
        $stockHabis = MasterBarangModel::with('stockReal')
            ->where(function ($q) {

                // ada stock_real tapi qty < min_stock
                $q->whereHas('stockReal', function ($query) {
                    $query->whereColumn('qty', '<', 'master_barang.min_stock');
                })

                    // tidak ada data stock_real sama sekali
                    ->orWhereDoesntHave('stockReal');
            })
            ->where('active', 1)
            ->count();

        return response()->json([
            'stockHabis' => $stockHabis
        ]);
    }

    function countSales()
    {
        $sales = PosModel::whereDate('created_at', now())->count();
        return response()->json([
            'sales' => $sales
        ]);
    }

    function sumSales()
    {
        $sumsales = DB::table('pos as p')

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

            ->whereDate('p.created_at', now())
            ->where('p.piutang', 0)

            ->selectRaw('
            SUM(p.amount) as total_penjualan,
            SUM(
                p.amount -
                CASE
                    WHEN p.amount_harga_beli = 0
                        THEN hbd.harga_beli * p.qty
                    ELSE p.amount_harga_beli
                END
            ) as total_keuntungan
        ')

            ->first();
        $sumsales = $sumsales->total_keuntungan ?? 0;

        return response()->json([
            'sumsales' => $sumsales
        ]);
    }

    function sumPiutang()
    {
        $sumpiutang = DB::table('pos as p')

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

            ->whereDate('p.created_at', now())
            ->where('p.piutang', 1)

            ->selectRaw('
            SUM(
                p.amount 
            ) as total_piutang
        ')

            ->first();
        $sumpiutang = $sumpiutang->total_piutang ?? 0;

        return response()->json([
            'sumpiutang' => $sumpiutang
        ]);
    }
}
