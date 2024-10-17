<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use OwenIt\Auditing\Contracts\Auditable;


class MeetingRecommendation extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'meeting_id', 'recommendation_text','recommendation_date', 'responsible_user',
        'responsible_party', 'recommendation_status_id'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_recommendations';
    public static function rules($action)
    {
        switch ($action) {
            case 'save':
            case 'update':
                return array(
                    'meeting_id' => 'nullable|numeric|exists:meetings,id',
                    'recommendation_text' => 'required',
                    'recommendation_date' => 'required',
                    'responsible_user' => 'required',
                    'responsible_party' => 'required',
                    'recommendation_status_id' => 'required'
                );
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'save':
            case 'update':
                return array(
                    'meeting_id.required' => [
                        'message_ar' =>  Lang::get('validation.custom.meeting_id.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.meeting_id.required', [], 'en'),
                    ],
                    'meeting_id.numeric' => [
                        'message_ar' =>  Lang::get('validation.custom.meeting_id.numeric', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.meeting_id.numeric', [], 'en'),
                    ],
                    'meeting_id.exists' => [
                        'message_ar' =>  Lang::get('validation.custom.meeting_id.exists', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.meeting_id.exists', [], 'en'),
                    ],
                    'recommendation_text.required' => [
                        'message_ar' =>  Lang::get('validation.custom.recommendation_text.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.recommendation_text.required', [], 'en'),
                    ],
                    'recommendation_date.required' => [
                        'message_ar' =>  Lang::get('validation.custom.recommendation_date.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.recommendation_date.required', [], 'en'),
                    ],
                    'responsible_user.required' => [
                        'message_ar' =>  Lang::get('validation.custom.responsible_user.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.responsible_user.required', [], 'en'),
                    ],
                    'responsible_party.required' => [
                        'message_ar' =>  Lang::get('validation.custom.responsible_party.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.responsible_party.required', [], 'en'),
                    ],
                    'recommendation_status_id.required' => [
                        'message_ar' =>  Lang::get('validation.custom.recommendation_status_id.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.recommendation_status_id.required', [], 'en'),
                    ],
                );
        }
    }

    public function meeting()
    {
        return $this->belongsTo('Models\Meeting', 'meeting_id');
    }
}
