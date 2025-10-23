<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
