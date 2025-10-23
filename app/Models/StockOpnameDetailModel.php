<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetailModel extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_detail';
    protected $guarded = [];

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'kode_barang', 'kode_barang');
    }
}
