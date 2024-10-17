<?php

namespace Services;

use Repositories\NotificationUserRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class NotificationUserService extends BaseService
{

    public function __construct(DatabaseManager $database, NotificationUserRepository $repository)
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

    public function getNotificationUserByIdAndUserId($notificationId,$userId){
        return $this->repository->getNotificationUserByIdAndUserId($notificationId,$userId);
    }

    public function readAllNotifications($userId){
        return $this->repository->readAllNotifications($userId);
    }
}