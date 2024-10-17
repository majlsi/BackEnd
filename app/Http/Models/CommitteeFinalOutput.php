<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;

class CommitteeFinalOutput extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'final_output_url',
        'final_output_date',
        'committee_id',
    ];

    protected $dates = ['created_at', 'updated_at'];
    protected $table = 'committee_final_output';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'update':
                return array(
                    'final_output_url' => 'required',
                    'final_output_date' => 'nullable|date',
                );
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'update':
                return array(
                    'final_output_url.required' => [
                        'message' => Lang::get(
                            'validation.custom.final_output_url.required',
                            [],
                            'en'
                        ),
                        'message_ar' => Lang::get(
                            'validation.custom.final_output_url.required',
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
