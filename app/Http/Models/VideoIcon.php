<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Exception;

class VideoIcon extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['video_icon_name_ar','video_icon_name_en','video_icon_url'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'video_icons';

}
