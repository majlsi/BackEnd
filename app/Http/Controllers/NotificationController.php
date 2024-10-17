<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\NotificationService;
use Services\NotificationUserService;
use Helpers\SecurityHelper;
use Models\Notification;
use Validator;

class NotificationController extends Controller {

    private $notificationService;
    private $notificationUserService;
    private $securityHelper;

    public function __construct(NotificationService $notificationService, SecurityHelper $securityHelper,
        NotificationUserService $notificationUserService) {
        $this->notificationService = $notificationService;
        $this->securityHelper = $securityHelper;
        $this->notificationUserService = $notificationUserService;
    }

    public function changeNotificationIsReadFlag(Request $request, $id){
        $user = $this->securityHelper->getCurrentUser();
        $notificationUser = $this->notificationUserService->getNotificationUserByIdAndUserId($id,$user->id);
        if($notificationUser) {
            $this->notificationUserService->update($notificationUser->id,['is_read' => true]);
            return response()->json(['message' => 'Notification updated successfully','message_ar' => 'تم التعديل بنجاح'], 200);
        } else {
            return response()->json(['error' => 'Can\'t updated this notification','error_ar' => 'لا يمكنك التعديل'], 400);
        }
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->notificationService->getPagedList($filter,$user->id),200);
    }

    public function getListOfNotification(){
        $user = $this->securityHelper->getCurrentUser();
        $list = $this->notificationService->getNotificationList($user)->groupBy(function($item) {
            return $item->is_read? 'old_notifications' : 'new_notifications';
        })->toArray();
        $list['new_notifications'] = isset($list['new_notifications'])? $list['new_notifications'] : [];
        $list['old_notifications'] = isset($list['old_notifications'])? $list['old_notifications'] : [];

        return response()->json($list,200);
    }

    public function getCountOfNewNotification(){
        $user = $this->securityHelper->getCurrentUser();
        return response()->json(['count' => $this->notificationService->getCountOfNewNotification($user->id)],200);
    }

    public function readAllNotifications(Request $request){
        $user = $this->securityHelper->getCurrentUser();
        $this->notificationUserService->readAllNotifications($user->id);
        return response()->json(['message' => 'All notifications have been read successfully','message_ar' => 'تم قراءه كل الإشعارات بنجاح'], 200);
    }
}