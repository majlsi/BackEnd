<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\Lang;
use Exception;
use Illuminate\Validation\Rule;


class Request extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    protected $fillable = [
        'request_type_id', 'request_body', 'created_by', 'approved_by', 'rejected_by',
        'is_approved', 'organization_id', 'evidence_document_url', 'evidence_document_id', 'reject_reason',
        'target_id'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'requests';



    protected $casts = [
        'request_body' => 'json',
    ];

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'saveCommitteeRequest':
                return array(
                    'request_body.committee_name_ar' => 'required',
                    'request_body.organization_id' => 'required',
                    'request_body.committee_code' => [
                        Rule::requiredIf(config('customSetting.removeCommitteeCode') === false),
                        'unique:committees,organization_id,NULL,id,deleted_at,NULL',
                    ],
                    'request_body.decision_number' => 'required',
                    'request_body.decision_date' => 'required',
                    'request_body.decision_responsible_user_id' => 'required',
                    'request_body.committee_status_id' => 'required',
                    'request_body.decision_document_url' => 'required',
                    'request_body.committee_type_id' => 'required',
                    'request_body.committee_start_date' => 'required_if:request_body.committee_type_id,' . config('committeeTypes.temporary'),
                    'request_body.committee_expired_date' => 'required_if:request_body.committee_type_id,' . config('committeeTypes.temporary'),
                );
            case 'deleteCommitteeUserRequest':
                return array(
                    'delete_reason' => 'required',
                    'committee_id' => 'required',
                );
            case 'unFreezeCommitteeRequest':
                return array(
                    'request_body.id' => 'required',
                    'request_body.reason' => 'required',
                );
            case 'rejectRequest':
                return array(
                    'reject_reason' => 'required',
                );
            case 'deleteFileRequest':
                return array(
                    'request_body.reason' => 'required'
                );
            case 'rejectAddUserRequest':
                return array(
                    'reject_reason' => 'required',
                );
            case 'updateCommitteeWithNewFieldsRequest':
                return array(
                    'request_body.committee_name_ar' => 'required',
                    'request_body.organization_id' => 'required',
                    'request_body.committee_code' => [
                        Rule::requiredIf(config('customSetting.removeCommitteeCode') === false),
                        'unique:committees,organization_id,NULL,id,deleted_at,NULL',
                    ],
                    'request_body.decision_number' => 'required',
                    'request_body.decision_date' => 'required',
                    'request_body.decision_responsible_user_id' => 'required',
                    'request_body.committee_status_id' => 'required',
                    'request_body.decision_document_url' => 'required',
                    'request_body.committee_type_id' => 'required',
                    'request_body.committee_start_date' => 'required_if:request_body.committee_type_id,' . config('committeeTypes.temporary'),
                    'request_body.committee_expired_date' => 'required_if:request_body.committee_type_id,' . config('committeeTypes.temporary'),
                );
            case 'updateCommitteeRequest':
                return array(
                    'request_body.committee_name_ar' => 'required',
                    'request_body.committeee_members_count' => 'required',
                    'request_body.organization_id' => 'required',
                    'request_body.committee_head_id' => 'required',
                    'committee_code' => [
                        Rule::requiredIf(config('customSetting.removeCommitteeCode') === false),
                        'unique_with:committees,organization_id,NULL,' . $id . ',deleted_at,NULL',
                    ],
                    'request_body.committee_organiser_id' => 'required'
                );
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'saveCommitteeRequest':
            case 'unFreezeCommitteeRequest':
                return array(
                    'request_body.id.required' => [
                        'message' => Lang::get('validation.custom.committee_id.required', [], 'en'),
                        'message_ar' => Lang::get('validation.custom.committee_id.required', [], 'ar')
                    ],
                    'request_body.reason.required' => [
                        'message' => Lang::get('validation.custom.request_body_reason.required', [], 'en'),
                        'message_ar' => Lang::get('validation.custom.request_body_reason.required', [], 'ar')
                    ],
                );
            case 'deleteFileRequest':
                return array(
                    'request_body.file_id.required' => [
                        'message' => Lang::get('validation.custom.file_id.required', [], 'en'),
                        'message_ar' => Lang::get('validation.custom.file_id.required', [], 'ar')
                    ]
                );
                //! put the message
            case 'rejectRequest':
                return array(
                    'reject_reason.required' => [
                        'message' => Lang::get('validation.custom.reject_reason.required', [], 'en'),
                        'message_ar' => Lang::get('validation.custom.reject_reason.required', [], 'ar')
                    ]
                );
            case 'deleteCommitteeUserRequest':
            case 'updateCommitteeRequest':
                return array(
                    'committee_name_ar.required' => [
                        'message' => Lang::get('validation.custom.committee_name_ar.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_name_ar.required', [], 'ar')
                    ],

                    'committee_code.required' => [
                        'message' => Lang::get('validation.custom.committee_code.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_code.required', [], 'ar')
                    ],

                    'committee_code.unique_with' => [
                        'message' => Lang::get('validation.custom.committee_code.unique_with', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_code.unique_with', [], 'ar')
                    ],

                    'committee_organiser_id.required' => [
                        'message' => Lang::get('validation.custom.committee_organiser_id.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.committee_organiser_id.required', [], 'ar')
                    ]

                );
            case 'updateCommitteeWithNewFieldsRequest':
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
    public function requestType()
    {
        return $this->belongsTo('Models\RequestType', 'request_type_id');
    }

    public function requestSender()
    {
        return $this->belongsTo('Models\User', 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo('Models\User', 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo('Models\User', 'rejected_by');
    }
    public function orgnization()
    {
        return $this->belongsTo('Models\Organization', 'organization_id');
    }
}
