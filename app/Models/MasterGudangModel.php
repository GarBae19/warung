<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // <- pastikan ini ada
use Illuminate\Support\Facades\Auth;

class MasterGudangModel extends Model
{
    use HasFactory;

    protected $table = 'master_gudang';
    protected $guarded = ['id'];
    protected $with = ['cabang'];

    protected $appends = ['is_used'];

    function cabang()
    {
        return $this->belongsTo(MasterCabangModel::class, 'id_cabang', 'id');
    }


    public function getIsUsedAttribute()
    {
        $tables = [
            'stock_opname_header',
        ];

        foreach ($tables as $table) {
            if (DB::table($table)->where('id_gudang', $this->id)->exists()) {
                return true;
            }
        }
        return false;
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
