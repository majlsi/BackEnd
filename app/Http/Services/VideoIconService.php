<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\VideoIconRepository;
use \Illuminate\Database\Eloquent\Model;

class VideoIconService extends BaseService
{

    public function __construct(DatabaseManager $database, VideoIconRepository $repository)
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
}
