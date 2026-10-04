<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class preqitemModel extends Model
{
    use HasFactory;

    protected $table = 'preqitem';
    protected $guarded = ['id'];
    public $timestamps = false;


    public function prequest()
    {
        return $this->belongsTo(PrequestModel::class, 'id_prequest', 'id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarangModel::class, 'id_barang', 'id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\preqitemModel::class, 'kode_pr', 'kode_pr');
    }
}
