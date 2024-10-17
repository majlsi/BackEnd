<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\FaqSectionRepository;
use \Illuminate\Database\Eloquent\Model;

class FaqSectionService extends BaseService
{

    public function __construct(DatabaseManager $database, FaqSectionRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }

    public function prepareCreate(array $data)
    {
        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function getPagedList($filter)
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
        return $this->repository->getPagedFaqSectionList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection);
    }

    public function getParentSections()
    {
        return $this->repository->findWhere([
            ['parent_section_id', '=', null],

        ]);
    }
    public function getLeafSections()
    {
        return $this->repository->getLeafSections();
    }

    public function setHasChilds($section)
    {
        if ($section->parent_section_id == null) {
            $section['hasChilds'] = $section->childSections->count() > 0 ? true : false;
        } else {
            $section['hasChilds'] = $section->faqs->count() > 0 ? true : false;
        }

        return $section;
    }

}
