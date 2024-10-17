<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\ChatGroupUserRepository;
use \Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ChatGroupUserService extends BaseService
{
    public function __construct(DatabaseManager $database, ChatGroupUserRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }

    public function prepareCreate($data) {
        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model,array $data) {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id){
        $this->repository->delete($id);
    }

    public function getIndividualChatUser($chatGroupId,$currentUserId){
        return $this->repository->getIndividualChatUser($chatGroupId,$currentUserId);
    }

    public function getChatGroupUserByUserIdAndChatGroupId($chatGroupId, $userId){
        return $this->repository->getChatGroupUserByUserIdAndChatGroupId($chatGroupId, $userId);
    }
}