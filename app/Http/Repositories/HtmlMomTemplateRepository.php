<?php

namespace Repositories;

class HtmlMomTemplateRepository extends BaseRepository {


    public function model() {
        return 'Models\HtmlMomTemplate';

    }
    
    public function getPagedHtmlMomTemplateList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId)
    {
        $query = $this->getPagedHtmlMomTemplateListQuery($searchObj, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getPagedHtmlMomTemplateListQuery($searchObj, $organizationId)
    {
        if (isset($searchObj->html_mom_template_name_ar)) {
            $this->model = $this->model->whereRaw("(html_mom_template_name_ar like ?)", array('%' . $searchObj->html_mom_template_name_ar . '%'));
        }
        if (isset($searchObj->html_mom_template_name_en)) {
            $this->model = $this->model->whereRaw("(html_mom_template_name_en like ?)", array('%' . $searchObj->html_mom_template_name_en . '%'));
        }

        $this->model = $this->model->where('organization_id', $organizationId);
        $this->model = $this->model->selectRaw('*');

        return $this->model;
    
    }
   
}   