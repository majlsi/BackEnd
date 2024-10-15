<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Exception;

class GuideVideo extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['video_name_ar','video_name_en','video_description_ar','video_description_en','video_url','video_icon_id','video_order','tutorial_step_tag'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'guide_videos';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'video_name_ar' => 'required',
                    'video_name_en' =>'sometimes',
                    'video_description_ar' => 'sometimes',
                    'video_description_en' =>'sometimes',
                    'video_url' => 'sometimes',
                    'video_icon_id' => 'sometimes',
                    'tutorial_step_tag' => 'sometimes',
                );
            case 'update':
                return array(
                    'video_name_ar' => 'required',
                    'video_name_en' =>'sometimes',
                    'video_description_ar' => 'sometimes',
                    'video_description_en' =>'sometimes',
                    'video_url' => 'sometimes',
                    'video_icon_id' => 'sometimes',
                    'tutorial_step_tag' => 'sometimes',
                );
        }
    }

    public function videoIcon(){
        return $this->belongsTo('Models\VideoIcon', 'video_icon_id');
    }
}
