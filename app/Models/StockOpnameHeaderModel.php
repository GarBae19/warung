<?php

namespace App\Models;

use App\Http\Controllers\MasterBarang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameHeaderModel extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_header';
    protected $guarded = ['id'];

    // protected $with = ['barang'];

    // public function barang()
    // {
    //     return $this->belongsTo(MasterBarangModel::class, 'kode_barang', 'kode_barang');
    // }
}
