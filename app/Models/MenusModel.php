<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MenusModel extends Model
{
    use HasFactory;
    protected $table = 'menus';
    protected $with = ['SubMenusModel'];
    // protected $fillable = ['nama_menu', 'link', 'created_by', 'updated_by'];
    protected $guarded  = ['id'];
    public function SubMenusModel()
    {
        return $this->hasMany(SubMenusModel::class, 'menu_parent_id', 'id');
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
