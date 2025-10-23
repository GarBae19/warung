<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockModel extends Model
{
    use HasFactory;

    protected $table = 'stock';
    protected $guarded = ['id'];
}
