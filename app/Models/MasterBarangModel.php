<?php

namespace App\Models;

use App\Http\Controllers\MasterSatuan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBarangModel extends Model
{
    use HasFactory;

    protected $table = 'master_barang';
    protected $guarded = ['id'];

    protected $with = ['jenis_barang', 'brand', 'satuan', 'stockOpnameDetail', 'stockReal'];


    public function jenis_barang()
    {
        return $this->belongsTo(MasterJenisBarangModel::class, 'kode_jenis_barang', 'kode_jenis');
    }

    public function brand()
    {
        return $this->belongsTo(MasterBrandModel::class, 'kode_brand', 'kode_brand');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuanModel::class, 'kode_satuan', 'kode_satuan');
    }

    public function stockOpnameDetail()
    {
        return $this->hasMany(StockOpnameDetailModel::class, 'kode_barang', 'kode_barang');
    }

    public function stockReal()
    {
        return $this->hasOne(StockModel::class, 'kode_barang', 'kode_barang');
    }
}
