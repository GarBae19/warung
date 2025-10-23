<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeBarangModel extends Model
{
    use HasFactory;
    protected $table = 'barcode_barang';
    protected $guarded = ['id'];
}
