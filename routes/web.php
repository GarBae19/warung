<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KonversiSatuan;
use App\Http\Controllers\MasterSatuan;
use App\Http\Controllers\MasterBrand;
use App\Http\Controllers\MasterJenisBarang;
use App\Http\Controllers\MasterBarang;
use App\Http\Controllers\MasterCabang;
use App\Http\Controllers\MasterGudang;
use App\Http\Controllers\MasterDepartemen;

use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\MasterStdHargaBeli;
use App\Http\Controllers\MasterStdHargaJual;
use App\Http\Controllers\PosController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RptStdHargaBeli;
use App\Http\Controllers\RptStdHargaJual;
use App\Http\Controllers\StockCardController;
use App\Http\Controllers\StockCardControllerAcc;
use App\Http\Controllers\PurchaseRequest;
use App\Http\Controllers\LaporanPenjualanController;
use Illuminate\Http\Request;

// Route::get('/', function () {
//     return view('check-client');
// });


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);


Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(
    function () {
        Route::get('/', [HomeController::class, 'index']);
        Route::get('masterbarang/export/excel', [MasterBarang::class, 'exportExcel'])->name('masterbarang.export.excel');
        Route::get('masterbarang/export/csv', [MasterBarang::class, 'exportCsv'])->name('masterbarang.export.csv');
        Route::get('masterbarang/export/pdf', [MasterBarang::class, 'exportPdf'])->name('masterbarang.export.pdf');
        Route::get('masterbarang/print', [MasterBarang::class, 'printPdf'])->name('masterbarang.print');

        Route::get('jenisBarang/export/excel', [MasterJenisBarang::class, 'exportExcel'])->name('jenisBarang.export.excel');
        Route::get('jenisBarang/export/csv', [MasterJenisBarang::class, 'exportCsv'])->name('jenisBarang.export.csv');
        Route::get('jenisBarang/export/pdf', [MasterJenisBarang::class, 'exportPdf'])->name('jenisBarang.export.pdf');
        Route::get('jenisBarang/print', [MasterJenisBarang::class, 'printPdf'])->name('jenisBarang.print');

        Route::get('masterbrand/export/excel', [MasterBrand::class, 'exportExcel'])->name('masterbrand.export.excel');
        Route::get('masterbrand/export/csv', [MasterBrand::class, 'exportCsv'])->name('masterbrand.export.csv');
        Route::get('masterbrand/export/pdf', [MasterBrand::class, 'exportPdf'])->name('masterbrand.export.pdf');
        Route::get('masterbrand/print', [MasterBrand::class, 'printPdf'])->name('masterbrand.print');

        Route::get('mastersatuan/export/excel', [MasterSatuan::class, 'exportExcel'])->name('mastersatuan.export.excel');
        Route::get('mastersatuan/export/csv', [MasterSatuan::class, 'exportCsv'])->name('mastersatuan.export.csv');
        Route::get('mastersatuan/export/pdf', [MasterSatuan::class, 'exportPdf'])->name('mastersatuan.export.pdf');
        Route::get('mastersatuan/print', [MasterSatuan::class, 'printPdf'])->name('mastersatuan.print');

        Route::get('konversi_satuan/export/excel', [KonversiSatuan::class, 'exportExcel'])->name('konversi_satuan.export.excel');
        Route::get('konversi_satuan/export/csv', [KonversiSatuan::class, 'exportCsv'])->name('konversi_satuan.export.csv');
        Route::get('konversi_satuan/export/pdf', [KonversiSatuan::class, 'exportPdf'])->name('konversi_satuan.export.pdf');
        Route::get('konversi_satuan/print', [KonversiSatuan::class, 'printPdf'])->name('konversi_satuan.print');


        // Route::get('/mastersatuan', [MasterSatuan::class, 'index']);
        // Route::get('/masterbrand', [MasterBrand::class, 'index']);
        Route::resource('/masterbrand', MasterBrand::class);
        Route::resource('/mastersatuan', MasterSatuan::class);
        Route::resource('/jenisBarang', MasterJenisBarang::class);
        Route::resource('/masterbarang', MasterBarang::class);
        Route::resource('/mastercabang', MasterCabang::class);
        Route::resource('/mastergudang', MasterGudang::class);
        Route::resource('/konversi_satuan', KonversiSatuan::class);
        Route::resource('/rptStdHargaBeli', RptStdHargaBeli::class);
        Route::resource('/rptStdHargaJual', RptStdHargaJual::class);
        // Route::resource('/masterStdHargaBeli', MasterStdHargaBeli::class);
        // Route::controller(StockOpnameController::class)->prefix('stockOpname')->name('stockOpname.')->group(function () {
        //     Route::get('/', 'index')->name('index');
        //     Route::get('/create', 'create')->name('create');
        //     Route::get('/detail', 'detail')->name('detail');
        //     Route::get('/{id}/edit', 'edit')->name('edit');
        //     Route::get('/{id}', 'show')->name('show');          // <-- ini show

        //     Route::post('/', 'store')->name('store');
        //     Route::post('/', 'scan')->name('scan');
        //     Route::put('/{id}', 'update')->name('update');
        //     Route::delete('/{id}', 'destroy')->name('destroy');
        // });
        // Route::get('/stockOpname/detail', function (Request $request) {
        //     dd($request->all());
        // });
        // Route::resource('/stockOpname', StockOpnameController::class);
        Route::get('/stockOpname', [StockOpnameController::class, 'index'])->name('stockOpname.index');
        Route::get('/stockOpname/create', [StockOpnameController::class, 'create'])->name('stockOpname.create');
        Route::get('/stockOpname/detail', [StockOpnameController::class, 'detail'])->name('stockOpname.detail');

        // ⬇️ Tambahkan di sini, sebelum route dinamis {id}
        Route::get('/stockOpname/export/excel', [StockOpnameController::class, 'exportExcel'])->name('stockOpname.export.excel');
        Route::get('/stockOpname/export/csv', [StockOpnameController::class, 'exportCsv'])->name('stockOpname.export.csv');
        Route::get('/stockOpname/export/pdf', [StockOpnameController::class, 'exportPdf'])->name('stockOpname.export.pdf');
        Route::get('/stockOpname/print', [StockOpnameController::class, 'printPdf'])->name('stockOpname.print');


        Route::post('/stockOpname/store', [StockOpnameController::class, 'store'])->name('stockOpname.store');
        Route::post('/stockOpname/scan', [StockOpnameController::class, 'scan'])->name('stockOpname.scan');
        Route::get('/stockOpname/approve/{id}', [StockOpnameController::class, 'approve'])->name('stockOpname.approve');
        Route::put('/stockOpname/{id}', [StockOpnameController::class, 'update'])->name('stockOpname.update');
        Route::delete('/stockOpname/reset', [StockOpnameController::class, 'reset'])->name('stockOpname.reset');
        Route::delete('/stockOpname/{id}', [StockOpnameController::class, 'destroy'])->name('stockOpname.destroy');

        Route::get('/stockOpname/{id}', [StockOpnameController::class, 'show'])->name('stockOpname.show');

        //masterStdHargaBeli
        Route::get('/masterStdHargaBeli', [MasterStdHargaBeli::class, 'index'])->name('masterStdHargaBeli.index');
        Route::get('/masterStdHargaBeli/create', [MasterStdHargaBeli::class, 'create'])->name('masterStdHargaBeli.create');
        Route::get('/masterStdHargaBeli/show', [MasterStdHargaBeli::class, 'show'])->name('masterStdHargaBeli.show');
        Route::post('/masterStdHargaBeli/store', [MasterStdHargaBeli::class, 'store'])->name('masterStdHargaBeli.store');
        Route::put('/masterStdHargaBeli/{id}', [MasterStdHargaBeli::class, 'update'])->name('masterStdHargaBeli.update');
        Route::delete('/masterStdHargaBeli/{id}', [MasterStdHargaBeli::class, 'destroy'])->name('masterStdHargaBeli.destroy');
        Route::get('/masterStdHargaBeli/getDataBarangStdHarga', [MasterStdHargaBeli::class, 'getDataBarangStdHarga'])->name('getDataBarangStdHarga');

        Route::get('masterStdHargaBeli/export/excel', [MasterStdHargaBeli::class, 'exportExcel'])->name('masterStdHargaBeli.export.excel');
        Route::get('masterStdHargaBeli/export/csv', [MasterStdHargaBeli::class, 'exportCsv'])->name('masterStdHargaBeli.export.csv');
        Route::get('masterStdHargaBeli/export/pdf', [MasterStdHargaBeli::class, 'exportPdf'])->name('masterStdHargaBeli.export.pdf');
        Route::get('masterStdHargaBeli/print', [MasterStdHargaBeli::class, 'print'])->name('masterStdHargaBeli.print');

        //masterStdHargaJual
        Route::get('/masterStdHargaJual', [MasterStdHargaJual::class, 'index'])->name('masterStdHargaJual.index');
        Route::get('/masterStdHargaJual/create', [MasterStdHargaJual::class, 'create'])->name('masterStdHargaJual.create');
        Route::get('/masterStdHargaJual/show', [MasterStdHargaJual::class, 'show'])->name('masterStdHargaJual.show');
        Route::post('/masterStdHargaJual/store', [MasterStdHargaJual::class, 'store'])->name('masterStdHargaJual.store');
        Route::put('/masterStdHargaJual/{id}', [MasterStdHargaJual::class, 'update'])->name('masterStdHargaJual.update');
        Route::delete('/masterStdHargaJual/{id}', [MasterStdHargaJual::class, 'destroy'])->name('masterStdHargaJual.destroy');
        Route::get('/masterStdHargaJual/getDataBarangStdHargaJual', [MasterStdHargaJual::class, 'getDataBarangStdHargaJual'])->name('getDataBarangStdHargaJual');
        Route::get('/masterStdHargaJual/getHargaBeli', [MasterStdHargaJual::class, 'getHargaBeli'])->name('getHargaBeli');

        Route::get('masterStdHargaJual/export/excel', [MasterStdHargaJual::class, 'exportExcel'])->name('masterStdHargaJual.export.excel');
        Route::get('masterStdHargaJual/export/csv', [MasterStdHargaJual::class, 'exportCsv'])->name('masterStdHargaJual.export.csv');
        Route::get('masterStdHargaJual/export/pdf', [MasterStdHargaJual::class, 'exportPdf'])->name('masterStdHargaJual.export.pdf');
        Route::get('masterStdHargaJual/print', [MasterStdHargaJual::class, 'print'])->name('masterStdHargaJual.print');

        //POS point of sale
        Route::get('/pos', [PosController::class, 'index'])->name('PosController.index');
        Route::post('/pos/prosesPembayaran', [PosController::class, 'prosesPembayaran'])->name('pos.prosesPembayaran');
        Route::get('/pos/strukPos', [PosController::class, 'strukPos'])->name('pos.strukPos');
        Route::get('/pos/piutang', [PosController::class, 'piutang'])->name('pos.piutang');
        Route::get('/pos/dataPiutang', [PosController::class, 'dataPiutang'])->name('pos.dataPiutang');
        Route::post('/pos/prosesPembayaranPiutang', [PosController::class, 'prosesPembayaranPiutang'])->name('pos.prosesPembayaranPiutang');

        //Stock Card
        Route::get('/stockCard', [StockCardController::class, 'index'])->name('stockCard');

        //Stock Card Acc
        Route::get('/stockCardAcc', [StockCardControllerAcc::class, 'index'])->name('stockCardAcc');

        Route::get('/purchaseRequest', [PurchaseRequest::class, 'index'])->name('purchaseRequest.index');
        Route::get('/purchaseRequest/create', [PurchaseRequest::class, 'create'])->name('purchaseRequest.create');
        Route::post('purchaseRequest/store', [PurchaseRequest::class, 'store'])->name('purchaseRequest.store');
        Route::post('purchaseRequest/store-detail', [PurchaseRequest::class, 'storeDetail'])->name('purchaseRequest.storeDetail');
        Route::delete('purchaseRequest/delete-detail', [PurchaseRequest::class, 'deleteDetail'])->name('purchaseRequest.deleteDetail');
        Route::get('purchaseRequest/detail', [PurchaseRequest::class, 'detail'])->name('purchaseRequest.detail');
        Route::get('purchaseRequest/barang', [PurchaseRequest::class, 'barangList'])->name('purchaseRequest.barang');

        // BARU
        Route::get('purchaseRequest/show/{id}', [PurchaseRequest::class, 'show'])->name('purchaseRequest.show');
        Route::get('purchaseRequest/edit/{id}', [PurchaseRequest::class, 'edit'])->name('purchaseRequest.edit');
        Route::put('purchaseRequest/update/{id}', [PurchaseRequest::class, 'update'])->name('purchaseRequest.update');
        Route::delete('purchaseRequest/destroy/{id}', [PurchaseRequest::class, 'destroy'])->name('purchaseRequest.destroy');

        //laporan penjualan
        Route::get('/rptSales', [LaporanPenjualanController::class, 'index'])->name('rptSales.index');
        Route::get('/rptSales/export/excel', [LaporanPenjualanController::class, 'exportExcel'])->name('rptSales.export.excel');
        Route::get('/rptSales/export/csv', [LaporanPenjualanController::class, 'exportCsv'])->name('rptSales.export.csv');
        Route::get('/rptSales/export/pdf', [LaporanPenjualanController::class, 'exportPdf'])->name('rptSales.export.pdf');
        Route::get('/rptSales/print', [LaporanPenjualanController::class, 'print'])->name('rptSales.print');



        Route::get('/masterbrand-kode', [MasterBrand::class, 'getKodeBrand']);
        Route::get('/mastersatuan-kode', [MasterSatuan::class, 'getKodeSatuan']);
        Route::post('/master-satuan/import', [MasterSatuan::class, 'importExcel'])
            ->name('masterSatuan.importExcel');
        Route::post('/master-brand/import', [MasterBrand::class, 'importExcel'])
            ->name('masterBrand.importExcel');
        Route::get('/masterjenisbarang-kode', [MasterJenisBarang::class, 'getKodeJenisBarang']);
        Route::get('/masterbarang-kode', [MasterBarang::class, 'getKodeMasterBarang']);
        Route::get('/masterKodeHargaBarang-kode', [MasterStdHargaBeli::class, 'getKodeHargaBeliBarang']);

        Route::get('/getDataJenisBarang', [MasterBarang::class, 'getDataJenisBarang'])->name('getDataJenisBarang');
        Route::get('/getBrand', [MasterBarang::class, 'getBrand'])->name('getBrand');
        Route::get('/getSatuan', [MasterBarang::class, 'getSatuan'])->name('getSatuan');
        Route::get('/getDataBarang', [MasterBarang::class, 'getDataBarang'])->name('getDataBarang');
        Route::get('/getDataBarangKonversiSatuan', [KonversiSatuan::class, 'getDataBarangKonversiSatuan'])->name('getDataBarangKonversiSatuan');
        Route::get('/getSatuanKonversi', [KonversiSatuan::class, 'getSatuanKonversi'])->name('getSatuanKonversi');
        Route::get('/mastercabang-kode', [MasterCabang::class, 'getKodeCabang']);
        Route::get('/mastergudang-kode', [MasterGudang::class, 'getKodeGudang']);
        Route::get('/getCabang', [MasterCabang::class, 'getCabang'])->name('getCabang');
        Route::get('/getGudang', [MasterGudang::class, 'getGudang'])->name('getGudang');
        Route::get('/getDepartemen', [MasterDepartemen::class, 'getDepartemen'])->name('getDepartemen');
        Route::get('/stock-habis', [HomeController::class, 'stockHabis'])->name('stock.habis');

        Route::get('/list_belum_terkonversi', [KonversiSatuan::class, 'listBelumTerkonversi'])->name('konversi_satuan.list_belum_terkonversi');
        Route::get('/countBarangHabis', [HomeController::class, 'countBarangHabis'])->name('countBarangHabis');
        Route::get('/countSales', [HomeController::class, 'countSales'])->name('countSales');
        Route::get('/sumSales', [HomeController::class, 'sumSales'])->name('sumSales');
        Route::get('/sumSalesInfo', [HomeController::class, 'sumSalesInfo'])->name('sumSalesInfo');
        Route::get('/sumPiutang', [HomeController::class, 'sumPiutang'])->name('sumPiutang');
        Route::get('/sumPiutangInfo', [HomeController::class, 'sumPiutangInfo'])->name('sumPiutangInfo');
        Route::get('/salesInfo', [HomeController::class, 'salesInfo'])->name('salesInfo');
        Route::get('/barang-list', [PosController::class, 'getBarangList']);
    }
);
// Route::get('/masterbarang-kode', [MasterBarang::class, 'getKodeBarang']);
