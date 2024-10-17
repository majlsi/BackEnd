<?php

namespace Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use OwenIt\Auditing\Contracts\Auditable;

class Approval extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'committee_id', 'status_id', 'created_by', 'approval_title',
        'organization_id', 'attachment_url', 'attachment_name',
        'file_id', 'meeting_id', 'signature_document_id'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'approvals';

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function (Approval $approval) {
            $approval->status_id = config("approvalStatuses.new", 1);
        });
    }

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
            case 'update':
                return array(
                    'committee_id' => 'nullable|numeric|exists:committees,id',
                    'members' => 'required|array|min:1',
                    'members.*' => 'numeric|exists:users,id',
                    'attachment_name' => 'required',
                    'attachment_url' => 'required',
                    'approval_title' => 'required|string'
                );
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'save':
            case 'update':
                return array(
                    'committee_id.required' => [
                        'message_ar' =>  Lang::get('validation.custom.committee_id.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.committee_id.required', [], 'en'),
                    ],
                    'committee_id.numeric' => [
                        'message_ar' =>  Lang::get('validation.custom.committee_id.numeric', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.committee_id.numeric', [], 'en'),
                    ],
                    'committee_id.exists' => [
                        'message_ar' =>  Lang::get('validation.custom.committee_id.exists', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.committee_id.exists', [], 'en'),
                    ],
                    'members.required' => [
                        'message_ar' =>  Lang::get('validation.custom.members.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.members.required', [], 'en'),
                    ],
                    'members.min' => [
                        'message_ar' =>  Lang::get('validation.custom.members.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.members.required', [], 'en'),
                    ],
                    'members.*.exists' => [
                        'message_ar' =>  Lang::get('validation.custom.members.exists', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.members.exists', [], 'en'),
                    ],
                    'attachment_name.required' => [
                        'message_ar' =>  Lang::get('validation.custom.attachments.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.attachments.required', [], 'en'),
                    ],
                    'attachment_url.required' => [
                        'message_ar' =>  Lang::get('validation.custom.attachments.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.attachments.required', [], 'en'),
                    ],
                    'approval_title.required' => [
                        'message_ar' =>  Lang::get('validation.custom.approval_title.required', [], 'ar'),
                        'message' =>  Lang::get('validation.custom.approval_title.required', [], 'en'),
                    ],
                );
        }
    }

    public function members()
    {
        return $this->hasMany('Models\ApprovalMember', 'approval_id');
    }

    public function approvalSender()
    {
        return $this->belongsTo('Models\User', 'created_by');
    }

    public function status()
    {
        return $this->belongsTo('Models\ApprovalStatus', 'status_id');
    }

    public function committee()
    {
        return $this->belongsTo('Models\Committee', 'committee_id');
    }

    public function organization()
    {
        return $this->belongsTo('Models\Committee', 'organization_id');
    }

    public function file()
    {
        return $this->belongsTo('Models\File', 'file_id');
    }

    public function meeting()
    {
        return $this->belongsTo('Models\Meeting', 'meeting_id');
    }
}
