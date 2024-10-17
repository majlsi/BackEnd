<?php

namespace Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;

class FaqSection extends Model implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['faq_section_name_ar', 'faq_section_name_en', 'parent_section_id','is_active'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'faq_sections';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'faq_section_name_ar' => 'required'
                );
            case 'update':
                return array(
                    'faq_section_name_ar' => 'required'
                );
        }
    }

    public function faqs()
    {
        return $this->hasMany('Models\Faq', 'section_id');
    }

    public function childSections()
    {
        return $this->hasMany('Models\FaqSection', 'parent_section_id');
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'update':
                return array(
                    'faq_section_name_ar.required' => ['error' => Lang::get('validation.custom.faq_section_name_ar.required', [], 'en')
                        , 'error_ar' => Lang::get('validation.custom.faq_section_name_ar.required', [], 'ar')]
                 
                );

        }
    }



    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($faqSection) {
            $relatedRelations = ['faqs'];
            foreach ($relatedRelations as $relatedRelation) {
                if ($faqSection->$relatedRelation()->count() > 0) {
                    throw new Exception("Model have child records");
                }
            }

        });
    }
}
