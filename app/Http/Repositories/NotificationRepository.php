<?php

namespace Repositories;

class NotificationRepository extends BaseRepository {

    public function model() {
        return 'Models\Notification';
    }

    public function getPagedNotification($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId){
        $query = $this->getAllNotificationQuery($searchObj, $userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    private function getAllNotificationQuery($searchObj, $userId) 
    {
        $this->model = $this->model->selectRaw('notifications.*,notification_users.is_read')
            ->join('notification_users','notification_users.notification_id','notifications.id')
            ->orderBy('notification_users.is_read','ASC')
            ->orderBy('notifications.id', 'desc')
            ->where('notification_users.user_id', $userId);

        return $this->model;
    }

    public function getNotificationList($userId){
        return $this->model->selectRaw('notifications.*,notification_users.is_read')
            ->join('notification_users','notification_users.notification_id','notifications.id')
            ->orderBy('notification_users.is_read','ASC')
            ->orderBy('notifications.id', 'desc')
            ->limit(config('notificationModelTypes.numberOfNotification'))
            ->where('notification_users.user_id',$userId)
            ->get();
    }

    public function getCountOfNewNotification($userId){
        return $this->model
            ->join('notification_users','notification_users.notification_id','notifications.id')
            ->where('notification_users.user_id',$userId)
            ->where('notification_users.is_read',0)
            ->count('notifications.id');
    }
}