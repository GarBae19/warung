<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBrandModel extends Model
{
    use HasFactory;

    protected $table = 'master_brand';
    protected $guarded = ['id'];
}
