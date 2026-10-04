<?php

namespace App\Models;

use App\Http\Controllers\MasterSatuan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // <- pastikan ini ada
use Illuminate\Support\Facades\Auth;


class MasterBarangModel extends Model
{
    use HasFactory;

    protected $table = 'master_barang';
    protected $guarded = ['id'];

    protected $with = ['jenis_barang', 'brand', 'satuan', 'stockOpnameDetail', 'stockReal'];

    protected $appends = ['is_used'];



    public function jenis_barang()
    {
        return $this->belongsTo(MasterJenisBarangModel::class, 'id_jenis_barang', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(MasterBrandModel::class, 'id_brand', 'id');
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuanModel::class, 'id_satuan', 'id');
    }

    public function stockOpnameDetail()
    {
        return $this->hasMany(StockOpnameDetailModel::class, 'id_barang', 'id');
    }

    public function stockReal()
    {
        return $this->hasOne(StockModel::class, 'id_barang', 'id');
    }

    public function getIsUsedAttribute()
    {
        $tables = [
            'stock_opname_detail',
            'barcode_barang',
            'master_header_std_harga_beli',
            'master_header_std_harga_jual',
            'stock',
            // tambahkan tabel transaksi lain
        ];

        foreach ($tables as $table) {
            if (DB::table($table)->where('id_barang', $this->id)->exists()) {
                return true;
            }
        }
        return false;
    }

    public function konversiSatuan()
    {
        return $this->hasMany(KonversiSatuanModel::class, 'id_barang', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::user()->name;
                $model->updated_by = Auth::user()->name;
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::user()->name;
            }
        });
    }
}
