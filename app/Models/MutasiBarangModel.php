<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class MutasiBarangModel extends Model
{
    use HasFactory;
    protected $table = 'mutasi_barang';
    protected $guarded = ['id'];
    protected $with = ['barang'];

    function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'id_barang', 'id');
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
