<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MasterDetailStdHargaBeliModel extends Model
{
    use HasFactory;

    protected $table = 'master_detail_std_harga_beli';
    protected $guarded = ['id'];
    protected $with = ['headerStdHargaBeli', 'cabang'];
    public function cabang()
    {
        return $this->belongsTo(MasterCabangModel::class, 'id_cabang', 'id');
    }
    public function headerStdHargaBeli()
    {
        return $this->belongsTo(MasterHeaderStdHargaBeliModel::class, 'id_header_std_harga_beli', 'id');
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
