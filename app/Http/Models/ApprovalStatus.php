<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class ApprovalStatus extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['approval_status_name_ar', 'approval_status_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'approval_statuses';
}
