<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;

class CommitteeRecommendation extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'recommendation_body', 'recommendation_date', 'responsible_user', 'responsible_party',
        'committee_id', 'committee_final_output_id', 'recommendation_status_id'
    ];

    protected $dates = ['created_at', 'updated_at'];
    protected $table = 'committee_recommendation';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'update':
                return array(
                    'recommendation_body' => 'required',
                    'recommendation_date' => 'required|date',
                    'responsible_user' => 'required',
                    'responsible_party' => 'required',
                    'recommendation_status_id' => 'required'
                );
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'update':
                return array(
                    'recommendation_body.required' => [
                        'message' => Lang::get(
                            'validation.custom.recommendations.recommendation_body.required',
                            [],
                            'en'
                        ),
                        'message_ar' => Lang::get(
                            'validation.custom.recommendations.recommendation_body.required',
                            [],
                            'ar'
                        )
                    ],
                    'recommendation_date.required' => [
                        'message' => Lang::get(
                            'validation.custom.recommendation_date.required',
                            [],
                            'en'
                        ),
                        'message_ar' => Lang::get(
                            'validation.custom.recommendation_date.required',
                            [],
                            'ar'
                        )
                    ],
                    'responsible_user.required' => [
                        'message' => Lang::get(
                            'validation.custom.responsible_user.required',
                            [],
                            'en'
                        ),
                        'message_ar' => Lang::get(
                            'validation.custom.responsible_user.required',
                            [],
                            'ar'
                        )
                    ],
                    'responsible_party.required' => [
                        'message' => Lang::get(
                            'validation.custom.responsible_party.required',
                            [],
                            'en'
                        ),
                        'message_ar' => Lang::get(
                            'validation.custom.responsible_party.required',
                            [],
                            'ar'
                        )
                    ],
                    'recommendation_status_id.required' => [
                        'message' => Lang::get(
                            'validation.custom.recommendation_status_id.required',
                            [],
                            'en'
                        ),
                        'message_ar' => Lang::get(
                            'validation.custom.recommendation_status_id.required',
                            [],
                            'ar'
                        )
                    ],
                );
        }
    }

    public function committees()
    {
        return $this->belongsTo('Models\Committee', 'committee_id');
    }
}
