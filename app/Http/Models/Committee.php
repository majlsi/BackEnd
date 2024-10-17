<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;
use Exception;
use Illuminate\Validation\Rule;

class Committee extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    protected $fillable = [
        'committee_name_en', 'committee_name_ar', 'committeee_members_count', 'organization_id',
        'committee_head_id', 'committee_code', 'can_delete', 'committee_organiser_id', 'chat_room_id',
        'last_message_text', 'last_message_date', 'committee_start_date', 'committee_expired_date',
        'governance_regulation_url', 'file_id', 'is_system', 'directory_id', 'decision_number', 'decision_date',
        'decision_responsible_user_id', 'committee_status_id', 'decision_document_url', 'committee_type_id',
        'committee_reason', 'final_output_url', 'has_recommendation_section' , 'final_output_date','committee_nature_id'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'committees';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'committeeRecommendations':
                return array(
                    'newRecommendations.*.recommendation_body' => 'required|string',
                    'newRecommendations.*.recommendation_date' => 'required|date',
                    'newRecommendations.*.responsible_user' => 'required',
                    'newRecommendations.*.responsible_party' => 'required',
                    'newRecommendations.*.recommendation_status_id' => 'required'
                );
            case 'save':
                return array(
                    'committee_name_ar' => 'required',
                    'committeee_members_count' => 'required',
                    'organization_id' => 'required',
                    'committee_head_id' => 'required',
                    'committee_code' => [
                        Rule::requiredIf(config('customSetting.removeCommitteeCode') === false),
                        'unique_with:committees,organization_id,NULL,id,deleted_at,NULL',
                    ],
                    'committee_organiser_id'=> 'required'
                );
            case 'update':
                return array(
                    'committee_name_ar' => 'required',
                    'committeee_members_count' => 'required',
                    'organization_id' => 'required',
                    'committee_head_id' => 'required',
                    'committee_code' => [
                        Rule::requiredIf(config('customSetting.removeCommitteeCode') === false),
                        'unique_with:committees,organization_id,NULL,' . $id . ',deleted_at,NULL',
                    ],
                    'committee_organiser_id'=> 'required'
                );
            case 'unfreezeCommittee':   
                return array(
                    'committee_start_date' => 'required' ,
                    'committee_expired_date' => 'required' ,
                );
            case 'finalOutputFile':
                return array(
                    'final_output_url' => 'required',
                    'final_output_date' => 'nullable|date',
                );
            case 'updateWithNewFields':
                return array(
                    'committee_name_ar' => 'required',
                    'organization_id' => 'required',
                    'committee_code' => [
                        Rule::requiredIf(config('customSetting.removeCommitteeCode') === false),
                        'unique:committees,organization_id,NULL,id,deleted_at,NULL',
                    ],
                    'decision_number' => 'required',
                    'decision_date' => 'required',
                    'decision_responsible_user_id' => 'required',
                    'committee_status_id' => 'required',
                    'decision_document_url' => 'required',
                    'committee_type_id' => 'required',
                    'committee_start_date' => 'required_if:request_body.committee_type_id,' . config('committeeTypes.temporary'),
                    'committee_expired_date' => 'required_if:request_body.committee_type_id,' . config('committeeTypes.temporary'),
                );
        }
    }

    public static function messages($action)
    {    
        switch ($action) {
            case 'committeeRecommendations':
                return array(
                    'newRecommendations.*.recommendation_body.required' => [
                        'message' => Lang::get('validation.custom.recommendations.recommendation_body.required', [], 'en'),
                        'message_ar' => Lang::get('validation.custom.recommendations.recommendation_body.required', [], 'ar')
                    ],
                    'newRecommendations.*.recommendation_date.required' => [
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
                    'newRecommendations.*.responsible_user.required' => [
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
                    'newRecommendations.*.responsible_party.required' => [
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
                    'newRecommendations.*.recommendation_status_id.required' => [
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
            case 'save':
                return array(
                    'committee_name_ar.required' => ['message'=>Lang::get('validation.custom.committee_name_ar.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.committee_name_ar.required',[],'ar')],

                    'committee_code.required' => ['message'=>Lang::get('validation.custom.committee_code.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.committee_code.required',[],'ar')], 
                    
                    'committee_code.unique_with' => ['message'=>Lang::get('validation.custom.committee_code.unique_with',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.committee_code.unique_with',[],'ar')], 
               
                    'committee_organiser_id.required' => ['message'=>Lang::get('validation.custom.committee_organiser_id.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.committee_organiser_id.required',[],'ar')]
               
                );
            case 'update': 
                return array(
                    'committee_name_ar.required' => ['message'=>Lang::get('validation.custom.committee_name_ar.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.committee_name_ar.required',[],'ar')],

                    'committee_code.required' => ['message'=>Lang::get('validation.custom.committee_code.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.committee_code.required',[],'ar')],

                    'committee_code.unique_with' => ['message'=>Lang::get('validation.custom.committee_code.unique_with',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.committee_code.unique_with',[],'ar')], 

                    'committee_organiser_id.required' => ['message'=>Lang::get('validation.custom.committee_organiser_id.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.committee_organiser_id.required',[],'ar')]

                );
            case 'unfreezeCommittee':
                return array(
                    'committee_start_date.required' => [
                        'message' => Lang::get('validation.custom.committee_start_date.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_start_date.required', [], 'ar')
                    ],
                    'committee_expired_date.required' => [
                        'message' => Lang::get('validation.custom.committee_expired_date.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_expired_date.required', [], 'ar')
                    ],
                );
            case 'finalOutputFile':
                return array(
                    'final_output_url.required' => [
                        'message' => Lang::get('validation.custom.final_output_url.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.final_output_url.required', [], 'ar')
                    ],
                    'final_output_date.date' => [
                        'message' => Lang::get('validation.date', ['attribute'=> 'final output date'], 'en'), 'message_ar' => Lang::get('validation.date', ['attribute' => 'تاريخ المخرج النهائى'], 'ar')
                    ],
                );
            case 'updateWithNewFields':
                return array(
                    'request_body.committee_name_ar.required' => [
                        'message' => Lang::get('validation.custom.committee_name_ar.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_name_ar.required', [], 'ar')
                    ],

                    'request_body.committee_code.required' => [
                        'message' => Lang::get('validation.custom.committee_code.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_code.required', [], 'ar')
                    ],

                    'request_body.committee_code.unique_with' => [
                        'message' => Lang::get('validation.custom.committee_code.unique_with', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_code.unique_with', [], 'ar')
                    ],

                    'request_body.committee_organiser_id.required' => [
                        'message' => Lang::get('validation.custom.committee_organiser_id.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_organiser_id.required', [], 'ar')
                    ],
                    'request_body.decision_number.required' => [
                        'message' => Lang::get('validation.custom.decision_number.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.decision_number.required', [], 'ar')
                    ],

                    'request_body.decision_date.required' => [
                        'message' => Lang::get('validation.custom.decision_date.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.decision_date.required', [], 'ar')
                    ],
                    'request_body.decision_responsible_user_id.required' => [
                        'message' => Lang::get('validation.custom.decision_responsible_user_id.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.decision_responsible_user_id.required', [], 'ar')
                    ],

                    'request_body.committee_status_id.required' => [
                        'message' => Lang::get('validation.custom.committee_status_id.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_status_id.required', [], 'ar')
                    ],
                    'request_body.decision_document_url.required' => [
                        'message' => Lang::get('validation.custom.decision_document_url.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.decision_document_url.required', [], 'ar')
                    ],
                    'request_body.committee_type_id.required' => [
                        'message' => Lang::get('validation.custom.committee_type_id.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_type_id.required', [], 'ar')
                    ],
                    'request_body.committee_start_date.required' => [
                        'message' => Lang::get('validation.custom.committee_start_date.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_start_date.required', [], 'ar')
                    ],
                    'request_body.committee_expired_date.required' => [
                        'message' => Lang::get('validation.custom.committee_expired_date.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_expired_date.required', [], 'ar')
                    ],
                );
        }
    }

    public function committeeUsers(){
        return $this->hasMany('Models\CommitteeUser','committee_id');
    }

    public function committeeHead(){
        return $this->belongsTo('Models\User','committee_head_id');
    }

    public function committeeOrganiser(){
        return $this->belongsTo('Models\User','committee_organiser_id');
    }

    public function memberUsers(){
        return $this->belongsToMany('Models\User','committee_users','committee_id', 'user_id');
    }

    public function meetings(){
        return $this->hasMany('Models\Meeting','committee_id');
    }

    
    public function tasks(){
        return $this->hasMany('Models\TaskManagement','committee_id');
    }

    public function file(){
        return $this->belongsTo('Models\File','file_id');
    }

    public function organization()
    {
        return $this->belongsTo('Models\Organization', 'organization_id');
    }

    public function directory(){
        return $this->belongsTo('Models\Directory','directory_id');
    }
    protected static function boot() {
        parent::boot();
        
        static::deleting(function($committee) {
            $relatedRelations = ['meetings'];
             foreach($relatedRelations as $relatedRelation){
                if ($committee->$relatedRelation()->count() > 0){
                    throw new Exception("Model have child records");
                }
            }
            
        });
    }
    public function committeeResponsible(){
        return $this->belongsTo('Models\User','decision_responsible_user_id');
    }

    public function committeeStatus(){
        return $this->belongsTo('Models\CommitteeStatus','committee_status_id');
    }
    public function committeeType(){
        return $this->belongsTo('Models\CommitteeType','committee_type_id');
    }
    public function committeeNature(){
        return $this->belongsTo('Models\CommitteeNature','committee_nature_id');
    }



    public function worksDone(){
        return $this->hasMany('Models\WorksDoneByCommittee');
    }

    public function recommendations()
    {
        return $this->hasMany('Models\CommitteeRecommendation', 'committee_id');
    }

    public function finalOutputs()
    {
        return $this->hasMany('Models\CommitteeFinalOutput', 'committee_id');
    }
}
