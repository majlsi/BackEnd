<?php

namespace Services;

use Repositories\CommitteeUserRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Storages\StorageFactory;

class CommitteeUserService extends BaseService
{
    private $storage;

    public function __construct(DatabaseManager $database, CommitteeUserRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->storage = StorageFactory::createStorage();
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

    public function checkIfUserIsHeadOfCommittee($userId, $committeeId)
    {
        return ($this->repository->checkIfUserIsHeadOfCommittee($userId, $committeeId)) ?  true : false;
    }

    public function deleteByUserIdAndCommitteeId($userId, $committeeId)
    {
        $this->repository->deleteByUserIdAndCommitteeId($userId, $committeeId);
    }

    public function getCommitteeUser($userId, $committeeId)
    {
       return $this->repository->getCommitteeUserId($userId, $committeeId);
    }

    public function getByIdOrNull($id)
    {
        return $this->repository->getByIdOrNull($id);
    }

    public function addDisclosureToCommitteeUser($committeeUser, $directoryPath, $request)
    {
        try {
            $committeeUser->disclosure_url = $this->storage->uploadFile($request, $directoryPath);
            $committeeUser->is_conflict = (bool)$request->get('is_conflict');
            $committeeUser->save();
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
