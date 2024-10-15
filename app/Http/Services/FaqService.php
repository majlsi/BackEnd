<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\FaqRepository;
use Repositories\FaqSectionRepository;
use \Illuminate\Database\Eloquent\Model;

class FaqService extends BaseService
{
    private $faqSectionRepository;

    public function __construct(DatabaseManager $database, FaqRepository $repository , FaqSectionRepository $faqSectionRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->faqSectionRepository = $faqSectionRepository;
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
        return $this->repository->getPagedFaqList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection);
    }

    public function getSectionQuestionsTree()
    {
        return $this->faqSectionRepository->getSectionQuestionsTree();
    }

}
