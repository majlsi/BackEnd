<?php

namespace Services;

use Repositories\CommitteeRecommendationRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;

class CommitteeRecommendationService extends BaseService
{
    public function __construct(
        DatabaseManager $database,
        CommitteeRecommendationRepository $repository,
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }
    public function prepareCreate(array $data)
    {
        $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }
}
