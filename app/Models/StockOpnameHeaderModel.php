<?php

namespace App\Models;

use App\Http\Controllers\MasterBarang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StockOpnameHeaderModel extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_header';
    protected $guarded = ['id'];

    protected $with = ['gudang'];

    public function gudang()
    {
        return $this->belongsTo(MasterGudangModel::class, 'id_gudang', 'id');
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
