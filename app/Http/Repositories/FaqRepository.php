<?php

namespace Repositories;

class FaqRepository extends BaseRepository {


    public function model() {
        return 'Models\Faq';
    }

    public function getPagedFaqList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection)
    {
        $query = $this->getAllFaqQuery($searchObj);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllFaqQuery($searchObj)
    {
        if (isset($searchObj->faq_question_ar)) {
            $this->model = $this->model->whereRaw("(faq_question_ar like ?)", array('%' . $searchObj->faq_question_ar . '%'));
        }
        if (isset($searchObj->faq_question_en)) {
            $this->model = $this->model->whereRaw("(faq_question_en like ?)", array('%' . $searchObj->faq_question_en . '%'));
        }

        if (isset($searchObj->faq_answer_ar)) {
            $this->model = $this->model->whereRaw("(faq_answer_ar like ?)", array('%' . $searchObj->faq_answer_ar . '%'));
        }
        if (isset($searchObj->faq_answer_en)) {
            $this->model = $this->model->whereRaw("(faq_answer_en like ?)", array('%' . $searchObj->faq_answer_en . '%'));
        }

        if (isset($searchObj->section_id)) {
            $this->model = $this->model->where('section_id',$searchObj->section_id);
        }

        return $this->model->selectRaw('faqs.* , faq_sections.faq_section_name_ar,faq_sections.faq_section_name_en')
        ->leftJoin('faq_sections','faq_sections.id','faqs.section_id')
        ->whereNull('faq_sections.deleted_at');
    }


}   