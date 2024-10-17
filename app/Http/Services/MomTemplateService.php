<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\CommitteeRepository;
use Repositories\MeetingRepository;
use Repositories\MomTemplateRepository;
use Repositories\ImageRepository;
use \Illuminate\Database\Eloquent\Model;

class MomTemplateService extends BaseService
{

    private $meetingRepository;
    private $committeeRepository;
    private $imageRepository;

    public function __construct(DatabaseManager $database,
        MomTemplateRepository $repository,
        MeetingRepository $meetingRepository,
        CommitteeRepository $committeeRepository,
        ImageRepository $imageRepository) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->meetingRepository = $meetingRepository;
        $this->committeeRepository = $committeeRepository;
        $this->imageRepository = $imageRepository;
    }

    public function prepareCreate(array $data)
    {

        if (isset($data['introduction_template_ar'])) {
            $data['introduction_template_ar'] = $this->parseInputWhenSaveOrUpdate($data['introduction_template_ar']);
        }

        if (isset($data['introduction_template_en'])) {
            $data['introduction_template_en'] = $this->parseInputWhenSaveOrUpdate($data['introduction_template_en']);
        }

        if (isset($data['member_list_introduction_template_ar'])) {
            $data['member_list_introduction_template_ar'] = $this->parseInputWhenSaveOrUpdate($data['member_list_introduction_template_ar']);
        }

        if (isset($data['member_list_introduction_template_en'])) {
            $data['member_list_introduction_template_en'] = $this->parseInputWhenSaveOrUpdate($data['member_list_introduction_template_en']);
        }

        if (isset($data['conclusion_template_en'])) {
            $data['conclusion_template_en'] = $this->parseInputWhenSaveOrUpdate($data['conclusion_template_en']);
        }

        if (isset($data['conclusion_template_ar'])) {
            $data['conclusion_template_ar'] = $this->parseInputWhenSaveOrUpdate($data['conclusion_template_ar']);
        }

        if(isset($data['logo_image'])){
            $logoImage = $this->imageRepository->create($data['logo_image']);
            $data['logo_id'] = $logoImage->id;
            unset($data['logo_image']);
        }

        return $this->repository->create($data);
    }

    private function parseInputWhenSaveOrUpdate($text)
    {
        preg_match_all('/{([^}]+)}/', $text, $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {         
            $colExistInMeetingTable = $this->meetingRepository->checkColumnExists(trim($matches[1][$i]));
            $colExistsInCommitteeTable = $this->committeeRepository->checkColumnExists(trim($matches[1][$i]));
            if ($colExistInMeetingTable == true || $colExistsInCommitteeTable == true || (in_array(trim($matches[1][$i]),['meeting_schedule_date_from','meeting_schedule_time_from']))) {
                $text = str_replace($matches[0][$i], "{{\$data['" . trim($matches[1][$i]) . "']}}", $text);
            }

        }
        return $text;
    }

    public function prepareUpdate(Model $model, array $data)
    {

        if (isset($data['introduction_template_ar'])) {
            $data['introduction_template_ar'] = $this->parseInputWhenSaveOrUpdate($data['introduction_template_ar']);
        }

        if (isset($data['introduction_template_en'])) {
            $data['introduction_template_en'] = $this->parseInputWhenSaveOrUpdate($data['introduction_template_en']);
        }

        if (isset($data['member_list_introduction_template_ar'])) {
            $data['member_list_introduction_template_ar'] = $this->parseInputWhenSaveOrUpdate($data['member_list_introduction_template_ar']);
        }

        if (isset($data['member_list_introduction_template_en'])) {
            $data['member_list_introduction_template_en'] = $this->parseInputWhenSaveOrUpdate($data['member_list_introduction_template_en']);
        }

        if (isset($data['conclusion_template_en'])) {
            $data['conclusion_template_en'] = $this->parseInputWhenSaveOrUpdate($data['conclusion_template_en']);
        }

        if (isset($data['conclusion_template_ar'])) {
            $data['conclusion_template_ar'] = $this->parseInputWhenSaveOrUpdate($data['conclusion_template_ar']);
        }

        if(isset($data['logo_image'])){
            if (isset($data['logo_image']['id'])) {
                $this->imageRepository->update($data['logo_image'], $data['logo_image']['id']);
            } else {
                $logoImage = $this->imageRepository->create($data['logo_image']);
                $data['logo_id'] = $logoImage->id;
            }
            unset($data['logo_image']);
        }

        return $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function getPagedList($filter, $organizationId)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "ASC";
        }
        return $this->repository->getPagedMomTemplateList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId);
    }

    public function getOrganizationMomTemplates(int $organizationId)
    {
        return $this->repository->findWhere(['organization_id'=> $organizationId]);
    }

    public function getMomTemplateDetails($momTemplateId){
        return $this->repository->getMomTemplateDetails($momTemplateId);
    }
}
