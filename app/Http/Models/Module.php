<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{

    use SoftDeletes;
    protected $fillable = ['module_name', 'module_name_ar', 'icon','menu_order'];

    public function rights()
    {
        return $this->hasMany('Models\Right');
    }

    public function submenu()
    {
        return $this->rights();
    }
}
