<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiBarangModel extends Model
{
    use HasFactory;
    protected $table = 'mutasi_barang';
    protected $guarded = ['id'];
}
