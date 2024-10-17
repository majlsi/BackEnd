<?php

namespace Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ApprovalMember extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['approval_id', 'member_id', 'signature_page_number', 'signature_x_upper_left', 'signature_y_upper_left', 'is_signed', 'signature_comment'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'approval_members';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'approval_id' => 'required',
                    'member_id' => 'required',
                );
        }
    }

    public function approval()
    {
        return $this->belongsTo('Models\Approval', 'approval_id');
    }

    public function member()
    {
        return $this->belongsTo('Models\User', 'member_id');
    }
}
