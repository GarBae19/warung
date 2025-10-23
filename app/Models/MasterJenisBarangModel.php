<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJenisBarangModel extends Model
{
    use HasFactory;

    protected $table = 'master_jenis';
    protected $guarded = ['id'];
}
