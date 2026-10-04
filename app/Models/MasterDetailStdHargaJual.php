<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MasterDetailStdHargaJual extends Model
{
    use HasFactory;
    protected $table = 'master_detail_std_harga_jual';
    protected $guarded = ['id'];

    protected $with = ['headerHargaJual'];
    public function headerHargaJual()
    {
        return $this->belongsTo(MasterHeaderStdHargaJual::class, 'id_header_std_harga_jual', 'id');
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
