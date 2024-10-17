<?php

namespace Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Faq extends Model implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['faq_question_ar', 'faq_answer_ar','faq_question_en', 'faq_answer_en','section_id','is_active'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'faqs';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'faq_question_ar' => 'required',
                    'faq_answer_ar' =>'required',
                    //'faq_question_en' => 'required',
                    //'faq_answer_en' =>'required',
                    'section_id' => 'required',
                );
            case 'update':
                return array(
                    'faq_question_ar' => 'required',
                    'faq_answer_ar' =>'required',
                    //'faq_question_en' => 'required',
                    //'faq_answer_en' =>'required',
                    'section_id' => 'required',
                );
        }
    }


    public function section(){
        return $this->belongsTo('Models\FaqSection', 'section_id');
    }

}
