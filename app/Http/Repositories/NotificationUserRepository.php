<?php

namespace Repositories;

class NotificationUserRepository extends BaseRepository {

    public function model() {
        return 'Models\NotificationUser';
    }

    public function getNotificationUserByIdAndUserId($notificationId,$userId){
        return $this->model->selectRaw('notification_users.*')
            ->where('notification_users.notification_id',$notificationId)
            ->where('notification_users.user_id',$userId)
            ->first();
    }

    public function readAllNotifications($userId){
        return $this->model
            ->where('notification_users.user_id',$userId)
            ->where('notification_users.is_read',0)
            ->update(['notification_users.is_read' => 1]);
    }
}