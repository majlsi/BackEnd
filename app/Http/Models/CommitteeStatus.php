<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CommitteeStatus extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['committee_status_name_ar', 'committee_status_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'committee_statuses';
}
