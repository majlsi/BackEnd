<?php

namespace Repositories;

class FaqSectionRepository extends BaseRepository
{

    public function model()
    {
        return 'Models\FaqSection';
    }

    public function getPagedFaqSectionList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection)
    {
        $query = $this->getAllFaqSectionQuery($searchObj);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllFaqSectionQuery($searchObj)
    {
        if (isset($searchObj->faq_section_name_ar)) {
            $this->model = $this->model->whereRaw("(faq_sections.faq_section_name_ar like ?)", array('%' . $searchObj->faq_section_name_ar . '%'));
        }
        if (isset($searchObj->faq_section_name_en)) {
            $this->model = $this->model->whereRaw("(faq_sections.faq_section_name_en like ?)", array('%' . $searchObj->faq_section_name_en . '%'));
        }

        if (isset($searchObj->parent_section_id)) {
            $this->model = $this->model->where('faq_sections.parent_section_id', $searchObj->parent_section_id);
        }

        return $this->model->selectRaw('faq_sections.faq_section_name_ar,faq_sections.faq_section_name_en,faq_sections.id,parent_section.faq_section_name_en as parent_section_name_en,parent_section.faq_section_name_ar as parent_section_name_ar')
            ->leftJoin('faq_sections as parent_section', 'faq_sections.parent_section_id', 'parent_section.id')
            ->whereNull('parent_section.deleted_at');

    }

    public function getLeafSections()
    {
        return $this->model->selectRaw('faq_sections.*')
            ->leftJoin('faq_sections as parent_section', 'faq_sections.parent_section_id', 'parent_section.id')
            ->whereNotNull('faq_sections.parent_section_id')
            ->whereNull('parent_section.deleted_at')->get();
    }

    public function getSectionQuestionsTree()
    {
        return $this->model->whereHas('childSections', function($query){
            $query->whereHas('faqs', function ($query) {
                $query->where('is_active', 1);
            });
        })->with(['childSections' => function ($query) {
            $query ->with(['faqs' => function ($query) {
                    $query->where('is_active', 1);
                }])
                ->whereHas('faqs', function ($query) {
                    $query->where('is_active', 1);
                });
        }])
            ->whereNull('faq_sections.parent_section_id')
            ->get();
    }

}
