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
use App\Http\Controllers\MasterSatuan;
use App\Http\Controllers\MasterBrand;
use App\Http\Controllers\MasterJenisBarang;
use App\Http\Controllers\MasterBarang;
use App\Http\Controllers\StockOpnameController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('check-client');
});

Route::get('/', [HomeController::class, 'index']);
// Route::get('/mastersatuan', [MasterSatuan::class, 'index']);
// Route::get('/masterbrand', [MasterBrand::class, 'index']);
Route::resource('/masterbrand', MasterBrand::class);
Route::resource('/mastersatuan', MasterSatuan::class);
Route::resource('/jenisBarang', MasterJenisBarang::class);
Route::resource('/masterbarang', MasterBarang::class);
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

Route::post('/stockOpname/store', [StockOpnameController::class, 'store'])->name('stockOpname.store');
Route::post('/stockOpname/scan', [StockOpnameController::class, 'scan'])->name('stockOpname.scan');
Route::post('/stockOpname/approve/{id}', [StockOpnameController::class, 'approve'])->name('stockOpname.approve');
Route::put('/stockOpname/{id}', [StockOpnameController::class, 'update'])->name('stockOpname.update');
Route::delete('/stockOpname/reset', [StockOpnameController::class, 'reset'])->name('stockOpname.reset');
Route::delete('/stockOpname/{id}', [StockOpnameController::class, 'destroy'])->name('stockOpname.destroy');

Route::get('/stockOpname/{id}', [StockOpnameController::class, 'show'])->name('stockOpname.show');


Route::get('/masterbrand-kode', [MasterBrand::class, 'getKodeBrand']);
Route::get('/mastersatuan-kode', [MasterSatuan::class, 'getKodeSatuan']);
Route::post('/master-satuan/import', [MasterSatuan::class, 'importExcel'])
    ->name('masterSatuan.importExcel');
Route::post('/master-brand/import', [MasterBrand::class, 'importExcel'])
    ->name('masterBrand.importExcel');
Route::get('/masterjenisbarang-kode', [MasterJenisBarang::class, 'getKodeJenisBarang']);
Route::get('/masterbarang-kode', [MasterBarang::class, 'getKodeMasterBarang']);
Route::get('/getDataJenisBarang', [MasterBarang::class, 'getDataJenisBarang'])->name('getDataJenisBarang');
Route::get('/getBrand', [MasterBarang::class, 'getBrand'])->name('getBrand');
Route::get('/getSatuan', [MasterBarang::class, 'getSatuan'])->name('getSatuan');
Route::get('/getDataBarang', [StockOpnameController::class, 'getDataBarang'])->name('getDataBarang');

// Route::get('/masterbarang-kode', [MasterBarang::class, 'getKodeBarang']);
