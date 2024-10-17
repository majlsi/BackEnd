<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\GuideVideoRepository;
use \Illuminate\Database\Eloquent\Model;

class GuideVideoService extends BaseService
{

    public function __construct(DatabaseManager $database, GuideVideoRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }

    public function prepareCreate(array $data)
    {
        $lastVideo = $this->repository->getLastVideo();
        $data['video_order'] = $lastVideo? $lastVideo->video_order + 1 : 1;
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
}
