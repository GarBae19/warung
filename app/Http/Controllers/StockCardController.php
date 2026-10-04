<?php

namespace App\Http\Controllers;

use App\Models\KonversiSatuanModel;
use App\Models\MutasiBarangModel;
use Illuminate\Http\Request;
use App\Models\MasterBarangModel;
use Illuminate\Support\Facades\DB;

class StockCardController extends Controller
{
    function index(Request $request)
    {
        $atribute = 'Stock Card';
        // $idBarang = null;
        $param = $request->query('id_barang');



        if (!empty($param)) {
            $idBarang = $param;
            // $idSatuan = $param[1];

            $idBarang = decrypt($idBarang);
            $tanggal = $request->query('tanggal');
            // $idSatuan = decrypt($idSatuan);

            $stockCards = MutasiBarangModel::where('id_barang', $idBarang)
                ->whereYear('tanggal', date('Y', strtotime($tanggal)))
                ->whereMonth('tanggal', date('m', strtotime($tanggal)))
                ->orderBy('tanggal', 'asc')->get();
            $saldoAwal = MutasiBarangModel::where('id_barang', $idBarang)
                ->where('tanggal', '<', date('Y-m-01', strtotime($tanggal)))
                ->get();
            $saldoAwal = $saldoAwal->sum('qty');
        } else {
            $stockCards = null;
            $saldoAwal = 0;
        }
        $barang = MasterBarangModel::orderBy('nama_barang')->get();

        return view('stockCard.index', compact('atribute', 'stockCards', 'barang', 'saldoAwal')); // Memanggil view home.blade.php
    }
}
