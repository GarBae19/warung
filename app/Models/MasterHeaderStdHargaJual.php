<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MasterHeaderStdHargaJual extends Model
{
    use HasFactory;
    protected $table = 'master_header_std_harga_jual';
    protected $guarded = ['id'];
    protected $with = ['barang', 'satuan'];
    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'id_barang', 'id');
    }
    public function satuan()
    {
        return $this->belongsTo(MasterSatuanModel::class, 'id_satuan', 'id');
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
