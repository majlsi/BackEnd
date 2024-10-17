<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class RoleRight extends Model  implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['role_id', 'right_id'];

    public function role()
    {
        return $this->belongsTo('Models\Role');
    }

    public function right()
    {
        return $this->belongsTo('Models\Right');
    }
}
