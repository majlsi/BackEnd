<?php

namespace Repositories;

class AgendaTemplateRepository extends BaseRepository {


    public function model() {
        return 'Models\AgendaTemplate';

    }
    
    public function getPagedAgendaTemplateList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId)
    {
        $query = $this->getPagedAgendaTemplateListQuery($searchObj, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getPagedAgendaTemplateListQuery($searchObj, $organizationId)
    {
        if (isset($searchObj->agenda_template_name_en)) {
            $this->model = $this->model->whereRaw("(agenda_template_name_en like ?)", array('%' . $searchObj->agenda_template_name_en . '%'));
        }
        if (isset($searchObj->agenda_template_name_ar)) {
            $this->model = $this->model->whereRaw("(agenda_template_name_ar like ?)", array('%' . $searchObj->agenda_template_name_ar . '%'));
        }

        $this->model = $this->model->where('organization_id', $organizationId);
        $this->model = $this->model->selectRaw('*');

        return $this->model;
    
    }
   
}   