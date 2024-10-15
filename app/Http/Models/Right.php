<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Right extends Model
{

    protected $fillable = ['right_name', 'right_name_ar', 'module_id', 'right_url', 'right_order_number', 'in_menu', 'icon'];

    public function roleRights()
    {
        return $this->hasMany('Models\RoleRight');
    }

    public function module()
    {
        return $this->belongsTo('Models\Module');
    }
}
