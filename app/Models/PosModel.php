<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PosModel extends Model
{
    use HasFactory;
    protected $table = 'pos';
    protected $guarded = ['id'];
    protected $with = ['barang', 'satuan', 'piutang'];
    function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'id_barang', 'id');
    }

    function satuan()
    {
        return $this->belongsTo(MasterSatuanModel::class, 'id_satuan', 'id');
    }

    function piutang()
    {
        return $this->hasOne(PiutangModel::class, 'id_pos', 'id');
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
