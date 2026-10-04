<?php

namespace App\Http\Controllers;

use App\Models\MasterCabangModel;
use Illuminate\Http\Request;
use App\Models\MasterHeaderStdHargaJualModel;
use App\Models\MasterDetailStdHargaJualModel;
use App\Models\MasterBarangModel;
use App\Models\KonversiSatuanModel;
use Illuminate\Support\Facades\DB;

use Exception;
use Yajra\DataTables\Facades\DataTables;

class RptStdHargaJual extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $hargaJuals = DB::table('master_header_std_harga_jual as a')
                ->join('master_detail_std_harga_jual as b', 'a.id', '=', 'b.id_header_std_harga_jual')
                ->join('master_barang as c', 'a.id_barang', '=', 'c.id')
                ->join('master_satuan as d', 'a.id_satuan', '=', 'd.id')
                ->join('master_cabang as e', 'b.id_cabang', '=', 'e.id')

                ->join('master_header_std_harga_beli as f', function ($join) {
                    $join->on('a.id_barang', '=', 'f.id_barang')
                        ->on('a.id_satuan', '=', 'f.id_satuan');
                })

                ->join('master_detail_std_harga_beli as g', function ($join) {
                    $join->on('f.id', '=', 'g.id_header_std_harga_beli')
                        ->on('b.id_cabang', '=', 'g.id_cabang');
                })

                ->select(
                    'a.kode_std_harga_jual',
                    'c.kode_barang',
                    'c.nama_barang',
                    'd.nama_satuan',
                    'b.harga_jual',
                    'e.nama_cabang',
                    'g.harga_beli'
                )
                ->orderBy('a.kode_std_harga_jual', 'asc')
                ->get();

            $lastKode = null;

            $hargaJuals = $hargaJuals->map(function ($row) use (&$lastKode) {
                $row->show_header = $lastKode !== $row->kode_std_harga_jual;

                $lastKode = $row->kode_std_harga_jual;

                return $row;
            });

            return DataTables::of($hargaJuals)
                ->addColumn('kode_std_harga_jual', function ($row) {
                    return $row->show_header ? $row->kode_std_harga_jual : '';
                })
                ->addColumn('item_kode', function ($row) {
                    return $row->show_header ? $row->kode_barang : '';
                })
                ->addColumn('item_nama', function ($row) {
                    return $row->show_header
                        ? $row->nama_barang . ' (' . ($row->nama_satuan ?? '-') . ')'
                        : '';
                })
                ->addColumn('cabang', function ($row) {
                    return $row->nama_cabang ?? '-';
                })
                ->addColumn('standar_harga_beli', function ($row) {
                    return 'Rp ' . number_format($row->harga_beli, 0, ',', '.');
                })
                ->addColumn('standar_harga_jual', function ($row) {
                    return 'Rp ' . number_format($row->harga_jual, 0, ',', '.');
                })
                ->make(true);
        }
        $atribute =  'Report STD Harga Jual';
        $cabangs = MasterCabangModel::all();
        return view('rptStdHargaJual.index', compact('atribute', 'cabangs')); // Memanggil view home.blade.php
    }
}
