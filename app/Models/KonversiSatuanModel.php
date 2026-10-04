<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class KonversiSatuanModel extends Model
{
    use HasFactory;

    protected $table = 'konversi_satuan';
    protected $guarded = ['id'];

    protected $with = ['barang', 'satuanAsal', 'satuanKonversi'];

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'id_barang', 'id');
    }

    public function satuanAsal()
    {
        return $this->belongsTo(MasterSatuanModel::class, 'id_satuan_asal');
    }

    public function satuanKonversi()
    {
        return $this->belongsTo(MasterSatuanModel::class, 'id_satuan_konversi');
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
