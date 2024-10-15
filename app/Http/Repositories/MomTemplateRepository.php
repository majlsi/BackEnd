<?php

namespace Repositories;

class MomTemplateRepository extends BaseRepository {


    public function model() {
        return 'Models\MomTemplate';
    }

    public function getPagedMomTemplateList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId)
    {
        $query = $this->getPagedMomTemplateListQuery($searchObj, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getPagedMomTemplateListQuery($searchObj, $organizationId)
    {
        if (isset($searchObj->template_name_en)) {
            $this->model = $this->model->whereRaw("(template_name_en like ?)", array('%' . $searchObj->template_name_en . '%'));
        }
        if (isset($searchObj->template_name_ar)) {
            $this->model = $this->model->whereRaw("(template_name_ar like ?)", array('%' . $searchObj->template_name_ar . '%'));
        }

        $this->model = $this->model->where('organization_id', $organizationId);
        $this->model = $this->model->selectRaw('*');

        return $this->model;
    }

    public function getMomTemplateDetails($momTemplateId){
        return $this->model->selectRaw('mom_templates.show_mom_header,mom_templates.show_agenda_list,
                mom_templates.show_timer,mom_templates.show_presenters,mom_templates.introduction_template_ar,
                mom_templates.show_purpose,mom_templates.show_participant_nickname,
                mom_templates.show_participant_job,mom_templates.show_participant_title,
                mom_templates.show_conclusion,mom_templates.show_vote_results,mom_templates.show_vote_status,mom_templates.conclusion_template_en,
                mom_templates.conclusion_template_ar,mom_templates.member_list_introduction_template_en,
                mom_templates.member_list_introduction_template_ar,mom_templates.introduction_template_en,
                CASE WHEN (mom_templates.logo_id IS NOT NULL) THEN images.image_url ELSE organizations_images.image_url  END AS meeting_mom_template_logo')
            ->join('organizations', 'organizations.id', 'mom_templates.organization_id')
            ->leftJoin('images', 'images.id', 'mom_templates.logo_id')
            ->leftJoin('images AS organizations_images', 'organizations_images.id', 'organizations.logo_id')
            ->where('mom_templates.id',$momTemplateId)
            ->first();
    }
}   