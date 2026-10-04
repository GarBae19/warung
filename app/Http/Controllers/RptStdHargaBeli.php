<?php

namespace App\Http\Controllers;

use App\Models\MasterCabangModel;
use Illuminate\Http\Request;
use App\Models\MasterHeaderStdHargaBeliModel;
use App\Models\MasterDetailStdHargaBeliModel;
use App\Models\MasterBarangModel;
use App\Models\KonversiSatuanModel;
use Illuminate\Support\Facades\DB;

use Exception;
use Yajra\DataTables\Facades\DataTables;

class RptStdHargaBeli extends Controller
{
    public function index(Request $request)
    {
        // $hargaBelis = MasterDetailStdHargaBeliModel::limit(10)->get();
        // $hargaBelis = MasterDetailStdHargaBeliModel::order_by('id_header_std_harga_beli', 'asc')->limit(10)->get();

        // dd($data);
        if ($request->ajax()) {

            // $hargaBelis = MasterDetailStdHargaBeliModel::order_by('id_header_std_harga_beli', 'asc')->get();

            // $hargaBelis = DB::select("SELECT * FROM master_header_std_harga_beli a
            // JOIN master_detail_std_harga_beli b ON a.id = b.id_header_std_harga_beli 
            // JOIN master_barang c ON a.id_barang = c.id
            // JOIN master_satuan d ON a.id_satuan = d.id
            // LIMIT 10 ");

            // return DataTables::of($hargaBelis)
            //     ->addColumn('kode_std_harga_beli', function ($hargaBeli) {
            //         return $hargaBeli->headerStdHargaBeli->kode_std_harga_beli ?? '-';
            //     })
            //     ->addColumn('item_kode', function ($hargaBeli) {
            //         return $hargaBeli->headerStdHargaBeli->barang->kode_barang ?? '-';
            //     })
            //     ->addColumn('item_nama', function ($hargaBeli) {
            //         return $hargaBeli->headerStdHargaBeli->barang->nama_barang . ' (' . ($hargaBeli->headerStdHargaBeli->satuan->nama_satuan ?? '-') . ')';
            //     })
            //     ->addColumn('cabang', function ($hargaBeli) {
            //         return $hargaBeli->cabang->nama_cabang ?? '-';
            //     })
            //     ->addColumn('standar_harga_beli', function ($hargaBeli) {
            //         return 'Rp ' . number_format($hargaBeli->harga_beli, 0, ',', '.');
            //     })
            //     ->make(true);


            $hargaBelis = DB::table('master_header_std_harga_beli as a')
                ->join('master_detail_std_harga_beli as b', 'a.id', '=', 'b.id_header_std_harga_beli')
                ->join('master_barang as c', 'a.id_barang', '=', 'c.id')
                ->join('master_satuan as d', 'a.id_satuan', '=', 'd.id')
                ->join('master_cabang as e', 'b.id_cabang', '=', 'e.id')
                ->select(
                    'a.kode_std_harga_beli',
                    'c.kode_barang',
                    'c.nama_barang',
                    'd.nama_satuan',
                    'b.harga_beli',
                    'e.nama_cabang'
                )
                ->orderBy('a.kode_std_harga_beli', 'asc')->get();

            $lastKode = null;

            $hargaBelis = $hargaBelis->map(function ($row) use (&$lastKode) {
                $row->show_header = $lastKode !== $row->kode_std_harga_beli;

                $lastKode = $row->kode_std_harga_beli;

                return $row;
            });

            return DataTables::of($hargaBelis)
                ->addColumn('kode_std_harga_beli', function ($row) {
                    return $row->show_header ? $row->kode_std_harga_beli : '';
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
                ->make(true);
        }
        $atribute =  'Report STD Harga Beli';
        $cabangs = MasterCabangModel::all();
        return view('rptStdHargaBeli.index', compact('atribute', 'cabangs')); // Memanggil view home.blade.php
    }
}
