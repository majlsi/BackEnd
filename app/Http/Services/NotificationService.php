<?php

namespace Services;

use Repositories\NotificationRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Helpers\NotificationHelper;
use Helpers\EventHelper;
use Carbon\Carbon;
use stdClass;
use Log;

class NotificationService extends BaseService
{
    private $notificationHelper;
    private $eventHelper;

    public function __construct(DatabaseManager $database, NotificationRepository $repository,
        NotificationHelper $notificationHelper, EventHelper $eventHelper)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->notificationHelper = $notificationHelper;
        $this->eventHelper = $eventHelper;
    }

    public function prepareCreate(array $data)
    {
        $notificationUsers = [];
        if(isset($data['notification_users'])){
            $notificationUsers = $data['notification_users'];
            unset($data['notification_users']);
        }
        $notification = $this->repository->create($data);
        if (count($notificationUsers) > 0) {
            $notification->notificationUsers()->createMany($notificationUsers);
        }
        return $notification;
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function sendNotification($notificationData){
        try {
            //create notification record
            $notification = $this->notificationHelper->prepareNotificationDataAtCreateNewNotification($notificationData);
            $notification = $this->prepareCreate($notification);
            $notificationData['notificationId'] = $notification->id;
            $this->eventHelper->fireEvent($notificationData, 'App\Events\SystemNotificationEvent');
            Log::channel('notifications')->info(['notification_data' => $notificationData]);
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function getPagedList($filter,$userId) {
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
        $result = $this->repository->getPagedNotification($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $userId);
        return $this->prepareNotificationsList($result);
    }

    public function getNotificationList($user)
    {
        $list = $this->repository->getNotificationList($user->id);
        foreach ($list as $key => $notification) {
            Carbon::setLocale('ar');
            $list[$key]['time_string_ar'] = Carbon::parse($notification['notification_date'])->diffForHumans();
            Carbon::setLocale('en');
            $list[$key]['time_string_en'] = Carbon::parse($notification['notification_date'])->diffForHumans();
        }
        return $list;
    }

    public function getCountOfNewNotification($userId){
        return $this->repository->getCountOfNewNotification($userId);
    }

    private function prepareNotificationsList($result){
        $list = new stdClass();
        $list->TotalRecords = $result->TotalRecords;
        $list->Results['new_notifications'] = [];
        $list->Results['old_notifications'] = [];
        
        foreach ($result->Results as $key => $item) {
            Carbon::setLocale('ar');
            $item->time_string_ar = Carbon::parse($item->notification_date)->diffForHumans();
            Carbon::setLocale('en');
            $item->time_string_en = Carbon::parse($item->notification_date)->diffForHumans();
            if($item->is_read) {
                $list->Results['old_notifications'][]= $item;
            } else {
                $list->Results['new_notifications'][]= $item;
            }
        }
        return $list;
    }
}