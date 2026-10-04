<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterDepartemenModel; // tambahkan ini
use App\Models\MasterCabangModel; // tambahkan ini
use Illuminate\Support\Facades\Auth;


class PrequestModel extends Model
{
    use HasFactory;

    protected $table = 'prequest';
    protected $guarded = ['id'];
    protected $with = ['cabang', 'departemens']; // tambahkan ini

    function cabang()
    {
        return $this->belongsTo(MasterCabangModel::class, 'id_cabang', 'id');
    }

    function departemens()
    {
        return $this->belongsTo(MasterDepartemenModel::class, 'departemen', 'id');
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
    public function items()
    {
        return $this->hasMany(\App\Models\preqitemModel::class, 'kode_pr', 'kode_pr');
    }
}
