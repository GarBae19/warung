<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // <- pastikan ini ada
use Illuminate\Support\Facades\Auth;

class MasterCabangModel extends Model
{
    use HasFactory;

    protected $table = 'master_cabang';
    protected $guarded = ['id'];

    protected $appends = ['is_used'];

    public function getIsUsedAttribute()
    {
        $tables = [
            'master_gudang',
            'master_detail_std_harga_beli',
            'master_detail_std_harga_jual',
        ];

        foreach ($tables as $table) {
            if (DB::table($table)->where('id_cabang', $this->id)->exists()) {
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
