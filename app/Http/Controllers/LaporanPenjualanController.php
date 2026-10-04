<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Exports\LaporanPenjualanExport;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;


class LaporanPenjualanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search  = $request->search;
            $perPage = $request->per_page ?? 10;

            [$base, $grandTotalBeli, $grandTotalJual, $dailyTotals] = $this->buildLaporanPenjualanQuery($request, $search);

            $base->orderBy('p.created_at', 'desc');

            if ($perPage === 'all') {
                $collection = $base->get()->map(function ($row) {
                    $row->tanggal    = $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y') : '-';
                    $row->item_nama  = $row->nama_barang ?? '-';
                    $row->satuan     = $row->nama_satuan ?? '-';
                    $row->total_beli = $row->harga_beli * $row->qty;
                    $row->total_jual = $row->harga_jual * $row->qty;
                    return $row;
                });

                return response()->json([
                    'data'             => $collection->values(),
                    'current_page'     => 1,
                    'last_page'        => 1,
                    'from'             => $collection->isEmpty() ? 0 : 1,
                    'to'               => $collection->count(),
                    'total'            => $collection->count(),
                    'prev_page_url'    => null,
                    'next_page_url'    => null,
                    'grand_total_beli' => $grandTotalBeli,
                    'grand_total_jual' => $grandTotalJual,
                    'grand_profit'     => $grandTotalJual - $grandTotalBeli,
                    'daily_totals'     => $dailyTotals,
                ]);
            }

            $data = $base->paginate((int) $perPage)->through(function ($row) {
                $row->tanggal    = $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y') : '-';
                $row->item_nama  = $row->nama_barang ?? '-';
                $row->satuan     = $row->nama_satuan ?? '-';
                $row->total_beli = $row->harga_beli * $row->qty;
                $row->total_jual = $row->harga_jual * $row->qty;
                return $row;
            });

            return response()->json([
                'data'             => $data->items(),
                'current_page'     => $data->currentPage(),
                'last_page'        => $data->lastPage(),
                'from'             => $data->firstItem(),
                'to'               => $data->lastItem(),
                'total'            => $data->total(),
                'prev_page_url'    => $data->previousPageUrl(),
                'next_page_url'    => $data->nextPageUrl(),
                'grand_total_beli' => $grandTotalBeli,
                'grand_total_jual' => $grandTotalJual,
                'grand_profit'     => $grandTotalJual - $grandTotalBeli,
                'daily_totals'     => $dailyTotals,
            ]);
        }

        $atribute = 'Laporan Penjualan';
        return view('laporanPenjualan.index', compact('atribute'));
    }

    /**
     * Query dasar laporan penjualan: pos + harga jual/beli terbaru per barang-satuan.
     * Pakai leftJoin (bukan inner join) supaya transaksi tanpa master harga tetap tampil,
     * dan subquery detail supaya tidak ada duplikasi baris kalau 1 header punya banyak baris detail.
     */
    private function buildLaporanPenjualanQuery(Request $request, $search)
    {
        $hargaJualDetail = DB::table('master_detail_std_harga_jual as d1')
            ->select('d1.id_header_std_harga_jual', 'd1.harga_jual')
            ->whereRaw('d1.id = (
            select max(d2.id) from master_detail_std_harga_jual d2
            where d2.id_header_std_harga_jual = d1.id_header_std_harga_jual
        )');

        $hargaBeliDetail = DB::table('master_detail_std_harga_beli as b1')
            ->select('b1.id_header_std_harga_beli', 'b1.harga_beli')
            ->whereRaw('b1.id = (
            select max(b2.id) from master_detail_std_harga_beli b2
            where b2.id_header_std_harga_beli = b1.id_header_std_harga_beli
        )');

        $base = DB::table('pos as p')
            ->leftJoin('master_header_std_harga_jual as hjh', function ($join) {
                $join->on('p.id_barang', '=', 'hjh.id_barang')
                    ->on('p.id_satuan', '=', 'hjh.id_satuan');
            })
            ->leftJoinSub($hargaJualDetail, 'hjd', function ($join) {
                $join->on('hjh.id', '=', 'hjd.id_header_std_harga_jual');
            })
            ->leftJoin('master_header_std_harga_beli as hbh', function ($join) {
                $join->on('p.id_barang', '=', 'hbh.id_barang')
                    ->on('p.id_satuan', '=', 'hbh.id_satuan');
            })
            ->leftJoinSub($hargaBeliDetail, 'hbd', function ($join) {
                $join->on('hbh.id', '=', 'hbd.id_header_std_harga_beli');
            })
            ->join('master_barang as mb', 'p.id_barang', '=', 'mb.id')
            ->join('master_satuan as ms', 'p.id_satuan', '=', 'ms.id')
            ->where('p.piutang', 0);

        if ($request->start_date && $request->end_date) {
            $base->whereBetween(DB::raw('DATE(p.created_at)'), [$request->start_date, $request->end_date]);
        } else {
            $base->whereDate('p.created_at', now());
        }

        if ($search) {
            $base->where(function ($q) use ($search) {
                $q->where('p.kode_pos', 'like', "%{$search}%")
                    ->orWhere('mb.nama_barang', 'like', "%{$search}%")
                    ->orWhere('ms.nama_satuan', 'like', "%{$search}%");
            });
        }

        // Grand total keseluruhan (clone, sebelum select kolom & sebelum paginate)
        $grandTotals = (clone $base)->selectRaw('
            COALESCE(SUM(COALESCE(hbd.harga_beli, 0) * p.qty), 0) as grand_total_beli,
            COALESCE(SUM(COALESCE(hjd.harga_jual, 0) * p.qty), 0) as grand_total_jual
        ')->first();

        // Total per hari (clone terpisah, group by tanggal) — independen dari pagination
        $dailyTotals = (clone $base)
            ->selectRaw('
                DATE(p.created_at) as tanggal,
                COALESCE(SUM(COALESCE(hbd.harga_beli, 0) * p.qty), 0) as total_beli,
                COALESCE(SUM(COALESCE(hjd.harga_jual, 0) * p.qty), 0) as total_jual,
                SUM(p.qty) as total_qty,
                COUNT(*) as jumlah_transaksi
            ')
            ->groupBy(DB::raw('DATE(p.created_at)'))
            ->orderBy(DB::raw('DATE(p.created_at)'), 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'tanggal_raw'      => $row->tanggal, // Y-m-d, dipakai untuk filter klik
                    'tanggal'          => Carbon::parse($row->tanggal)->format('d/m/Y'),
                    'total_beli'       => (float) $row->total_beli,
                    'total_jual'       => (float) $row->total_jual,
                    'total_qty'        => (int) $row->total_qty,
                    'jumlah_transaksi' => (int) $row->jumlah_transaksi,
                    'profit'           => (float) $row->total_jual - (float) $row->total_beli,
                ];
            })
            ->values();

        $base->select([
            'p.id',
            'p.kode_pos',
            'p.qty',
            'p.created_at',
            'mb.nama_barang',
            'ms.nama_satuan',
            DB::raw('COALESCE(hbd.harga_beli, 0) as harga_beli'),
            DB::raw('COALESCE(hjd.harga_jual, 0) as harga_jual'),
        ]);

        return [$base, (float) $grandTotals->grand_total_beli, (float) $grandTotals->grand_total_jual, $dailyTotals];
    }

    /**
     * Ambil seluruh baris (tanpa paginate) sesuai filter yang aktif,
     * dipakai bersama oleh export Excel/CSV/PDF/Print.
     */
    private function getExportData(Request $request)
    {
        $search = $request->search;

        [$base, $grandTotalBeli, $grandTotalJual] = $this->buildLaporanPenjualanQuery($request, $search);

        $rows = $base->orderBy('p.created_at', 'desc')->get();

        return [$rows, $grandTotalBeli, $grandTotalJual];
    }

    public function exportExcel(Request $request)
    {
        [$rows, $grandTotalBeli, $grandTotalJual] = $this->getExportData($request);

        $filename = 'laporan_penjualan_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new LaporanPenjualanExport($rows, $grandTotalBeli, $grandTotalJual),
            $filename
        );
    }

    public function exportCsv(Request $request)
    {
        [$rows, $grandTotalBeli, $grandTotalJual] = $this->getExportData($request);

        $filename = 'laporan_penjualan_' . now()->format('Ymd_His') . '.csv';

        return Excel::download(
            new LaporanPenjualanExport($rows, $grandTotalBeli, $grandTotalJual),
            $filename,
            \Maatwebsite\Excel\Excel::CSV
        );
    }

    public function exportPdf(Request $request)
    {
        [$rows, $grandTotalBeli, $grandTotalJual] = $this->getExportData($request);

        $data = [
            'rows'           => $rows,
            'grandTotalBeli' => $grandTotalBeli,
            'grandTotalJual' => $grandTotalJual,
            'grandProfit'    => $grandTotalJual - $grandTotalBeli,
            'startDate'      => $request->start_date,
            'endDate'        => $request->end_date,
        ];

        $pdf = Pdf::loadView('laporanPenjualan.pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download('laporan_penjualan_' . now()->format('Ymd_His') . '.pdf');
    }

    public function print(Request $request)
    {
        [$rows, $grandTotalBeli, $grandTotalJual] = $this->getExportData($request);

        return view('laporanPenjualan.print', [
            'rows'           => $rows,
            'grandTotalBeli' => $grandTotalBeli,
            'grandTotalJual' => $grandTotalJual,
            'grandProfit'    => $grandTotalJual - $grandTotalBeli,
            'startDate'      => $request->start_date,
            'endDate'        => $request->end_date,
        ]);
    }
}
