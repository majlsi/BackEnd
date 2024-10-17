<?php

namespace Helpers;

use Carbon\Carbon;
use Lang;

class NotificationHelper
{

    public function __construct()
    {
    }

    public static function prepareNotificationDataOnPublishing($meeting, $meetingStatusId)
    {
        $notificationData = [];
        $notificationData['meetingTypeNameEn'] = ($meeting->committee->committee_name_en ? $meeting->committee->committee_name_en : $meeting->committee->committee_name_ar);
        $notificationData['meetingTitleEn'] = ($meeting->meeting_title_en ? $meeting->meeting_title_en : $meeting->meeting_title_ar);

        $notificationData['meetingCreatorNameEn'] = ($meeting->creator->name ? $meeting->creator->name : $meeting->creator->name_ar);

        $notificationData['meetingTypeNameAr'] = $meeting->committee->committee_name_ar;
        $notificationData['meetingCreatorNameAr'] = ($meeting->creator->name_ar ? $meeting->creator->name_ar : $meeting->creator->name);
        $notificationData['meetingTitleAr'] = $meeting->meeting_title_ar ? $meeting->meeting_title_ar : $meeting->meeting_title_en;
        $notificationViewData = $notificationData;
        if ($meetingStatusId == config('meetingStatus.publish')) {
            $notificationViewData['meetingStatusAr'] = 'إرسال دعوات';
            $notificationViewData['meetingStatusEn'] = 'invitations sent';
            $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.NotificationTitleEn', $notificationViewData);
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.NotificationMessageEn', $notificationViewData);
            $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.NotificationTitleAr', $notificationViewData);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.NotificationMessageAr', $notificationViewData);
        } elseif ($meetingStatusId == config('meetingStatus.publishAgenda')) {
            $notificationViewData['meetingStatusAr'] = ' نشر جدول الأعمال';
            $notificationViewData['meetingStatusEn'] = 'agenda published';
            $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.NotificationTitleEn', $notificationViewData);
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.NotificationMessageEn', $notificationViewData);
            $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.NotificationTitleAr', $notificationViewData);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.NotificationMessageAr', $notificationViewData);
        } elseif ($meetingStatusId == config('meetingStatus.start')) {

            $notificationViewData['meetingStatusAr'] = 'بدء';
            $notificationViewData['meetingStatusEn'] = 'started';
            $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.NotificationTitleEn', $notificationViewData);
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.NotificationMessageEn', $notificationViewData);
            $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.NotificationTitleAr', $notificationViewData);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.NotificationMessageAr', $notificationViewData);
        } elseif ($meetingStatusId == config('meetingStatus.end')) {

            $notificationViewData['meetingStatusAr'] = 'انتهاء';
            $notificationViewData['meetingStatusEn'] = 'ended';
            $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.NotificationTitleEn', $notificationViewData);
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.NotificationMessageEn', $notificationViewData);
            $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.NotificationTitleAr', $notificationViewData);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.NotificationMessageAr', $notificationViewData);
        }

        $meetingParticipants = $meeting->meetingParticipants;
        $meetingParticipantIds = array_column($meetingParticipants->toArray(), 'id');
        $meetingOrganisers = $meeting->meetingOrganisers;
        $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');
        $meetingMemberIds = array_merge($meetingParticipantIds, $meetingOrganiserIds);
        $notificationData['meetingMemberIds'] = $meetingMemberIds;
        $notificationData['meetingId'] = $meeting->id;

        return $notificationData;
    }

    public static function getNotificationData($viewName, $dataArray)
    {
        $notification = view($viewName, $dataArray)->render();
        return $notification;
    }

    public static function prepareReminderNotificationData($meeting)
    {
        $notificationData = [];
        $notificationData['meetingTypeNameEn'] = ($meeting->committee->committee_name_en ? $meeting->committee->committee_name_en : $meeting->committee->committee_name_ar);
        $notificationData['meetingTitleEn'] = ($meeting->meeting_title_en ? $meeting->meeting_title_en : $meeting->meeting_title_ar);

        $notificationData['meetingCreatorNameEn'] = ($meeting->creator->name ? $meeting->creator->name : $meeting->creator->name_ar);

        $notificationData['meetingTypeNameAr'] = $meeting->committee->committee_name_ar;
        $notificationData['meetingCreatorNameAr'] = ($meeting->creator->name_ar ? $meeting->creator->name_ar : $meeting->creator->name);
        $notificationData['meetingTitleAr'] = $meeting->meeting_title_ar ? $meeting->meeting_title_ar : $meeting->meeting_title_en;
        $notificationViewData = $notificationData;

        Carbon::setLocale('ar');
        $notificationViewData['meetingAfterTimeAr'] = Carbon::parse($meeting->meeting_schedule_from)->diffForHumans(Carbon::now()->addHours($meeting->timeZone->diff_hours));
        Carbon::setLocale('en');
        $notificationViewData['meetingAfterTimeEn'] = Carbon::parse($meeting->meeting_schedule_from)->diffForHumans(Carbon::now()->addHours($meeting->timeZone->diff_hours), true);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.ReminderNotificationTitleEn', $notificationViewData);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.ReminderNotificationMessageEn', $notificationViewData);
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.ReminderNotificationTitleAr', $notificationViewData);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.ReminderNotificationMessageAr', $notificationViewData);

        $meetingParticipants = $meeting->meetingParticipants;
        $meetingParticipantIds = array_column($meetingParticipants->toArray(), 'id');
        $meetingOrganisers = $meeting->meetingOrganisers;
        $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');
        $meetingMemberIds = array_merge($meetingParticipantIds, $meetingOrganiserIds);
        $notificationData['meetingMemberIds'] = $meetingMemberIds;
        $notificationData['meetingId'] = $meeting->id;

        return $notificationData;
    }

    public function prepareNewTaskNotificationData($task)
    {

        $notificationData = [];

        $notificationData['serial_number'] = $task->serial_number;
        $notificationData['taskCreatorNameAr'] = ($task->createdBy->name_ar ? $task->createdBy->name_ar : $task->createdBy->name);
        $notificationData['taskCreatorNameEn'] = ($task->createdBy->name ? $task->createdBy->name : $task->createdBy->name_ar);

        $notificationViewData = $notificationData;

        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.NewTaskNotificationTitleEn', $notificationViewData);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.NewTaskNotificationMessageEn', $notificationViewData);
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.NewTaskNotificationTitleAr', $notificationViewData);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.NewTaskNotificationMessageAr', $notificationViewData);


        $notificationData['assignedTo'] = $task->assigned_to;
        $notificationData['taskId'] = $task->id;

        return $notificationData;
    }

    public function prepareTaskExpiredNotificationData($task)
    {

        $notificationData = [];

        $notificationData['serial_number'] = $task->serial_number;
        $notificationViewData = $notificationData;

        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.TaskExpiredNotificationTitleEn', $notificationViewData);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.TaskExpiredNotificationMessageEn', $notificationViewData);
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.TaskExpiredNotificationTitleAr', $notificationViewData);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.TaskExpiredNotificationMessageAr', $notificationViewData);

        $notificationData['assignedTo'] = $task->assigned_to;
        $notificationData['taskId'] = $task->id;

        return $notificationData;
    }

    public function prepareTaskStatusChangedNotificationData($task, $userIds, $user)
    {

        $notificationData = [];

        $notificationData['serial_number'] = $task->serial_number;
        $notificationData['taskStatusNameAr'] = $task->taskStatus->task_status_name_ar;
        $notificationData['taskStatusNameEn'] = $task->taskStatus->task_status_name_en;
        $notificationData['changedByNameAr'] = ($user->name_ar ? $user->name_ar : $user->name);
        $notificationData['changedByNameEn'] = ($user->name ? $user->name : $user->name_ar);

        $notificationViewData = $notificationData;

        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.TaskStatusChangedNotificationTitleEn', $notificationViewData);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.TaskStatusChangedNotificationMessageEn', $notificationViewData);
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.TaskStatusChangedNotificationTitleAr', $notificationViewData);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.TaskStatusChangedNotificationMessageAr', $notificationViewData);


        $notificationData['userIds'] = $userIds;
        $notificationData['taskId'] = $task->id;

        return $notificationData;
    }

    public function prepareEditTaskNotificationData($task)
    {

        $notificationData = [];

        $notificationData['serial_number'] = $task->serial_number;
        $notificationData['taskCreatorNameAr'] = ($task->createdBy->name_ar ? $task->createdBy->name_ar : $task->createdBy->name);
        $notificationData['taskCreatorNameEn'] = ($task->createdBy->name ? $task->createdBy->name : $task->createdBy->name_ar);

        $notificationViewData = $notificationData;

        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.EditTaskNotificationTitleEn', $notificationViewData);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.EditTaskNotificationMessageEn', $notificationViewData);
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.EditTaskNotificationTitleAr', $notificationViewData);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.EditTaskNotificationMessageAr', $notificationViewData);


        $notificationData['assignedTo'] = $task->assigned_to;
        $notificationData['taskId'] = $task->id;

        return $notificationData;
    }

    public function prepareAddCommentToTaskNotificationData($task, $userIds, $user)
    {

        $notificationData = [];

        $notificationData['serial_number'] = $task->serial_number;
        $notificationData['taskStatusNameAr'] = $task->taskStatus->task_status_name_ar;
        $notificationData['taskStatusNameEn'] = $task->taskStatus->task_status_name_en;
        $notificationData['changedByNameAr'] = ($user->name_ar ? $user->name_ar : $user->name);
        $notificationData['changedByNameEn'] = ($user->name ? $user->name : $user->name_ar);

        $notificationViewData = $notificationData;

        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.AddCommentToTaskNotificationTitleEn', $notificationViewData);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.AddCommentToTaskNotificationMessageEn', $notificationViewData);
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.AddCommentToTaskNotificationTitleAr', $notificationViewData);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.AddCommentToTaskNotificationMessageAr', $notificationViewData);


        $notificationData['userIds'] = $userIds;
        $notificationData['taskId'] = $task->id;

        return $notificationData;
    }

    public function prepareNotificationDataForMeetingDecision($decision, $user, $notificationType, $extraData)
    {
        $data = [];
        $notificationData = [];
        $meetingParticipantsIds = array_column($decision->meeting->meetingParticipants->toArray(), 'id');
        $meetingOrganisersIds = array_column($decision->meeting->meetingOrganisers->toArray(), 'id');

        $data['meetingDecisionSubjectEn'] = $decision->vote_subject_en ? $decision->vote_subject_en : $decision->vote_subject_ar;
        $data['meetingDecisionSubjectAr'] = $decision->vote_subject_ar ? $decision->vote_subject_ar : $decision->vote_subject_en;
        $data['meetingTitleEn'] = $decision->meeting->meeting_title_en ? $decision->meeting->meeting_title_en : $decision->meeting->meeting_title_ar;
        $data['meetingTitleAr'] = $decision->meeting->meeting_title_ar ? $decision->meeting->meeting_title_ar : $decision->meeting->meeting_title_en;
        $data['changedByNameAr'] = $user->name_ar ? $user->name_ar : $user->name;
        $data['changedByNameEn'] = $user->name ? $user->name : $user->name_ar;
        $notificationData['notificationIcon'] = config('notificationIcons.meetingDecision');
        $notificationData['notificationUrl'] = config('notificationUrls.meetingDecision');
        $notificationData['notificationModelType'] = config('notificationModelTypes.meetingDecision');
        $notificationData['notificationModelId'] = $decision->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.MeetingDecisionNotificationTitleAr', $notificationData);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.MeetingDecisionNotificationTitleEn', $notificationData);
        $notificationData['notificationExtraData'] = [];
        $notificationData['notificationExtraData']['meeting_id'] = $decision->meeting_id;

        if ($notificationType == config('meetingDecision.addDecision')) {
            $votersIds = array_values(array_unique(array_merge($meetingParticipantsIds, $meetingOrganisersIds)));
            $guestsIds = array_column($decision->meeting->guests->toArray(), 'id');
            $notificationData['notificationGuestsIds'] = $guestsIds;
            $notificationData['notificationUsersIds'] = $votersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.AddMeetingDecisionNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.AddMeetingDecisionNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingDecision.editDecision')) {
            $votersIds = array_values(array_unique(array_merge($meetingParticipantsIds, $meetingOrganisersIds)));
            $guestsIds = array_column($decision->meeting->guests->toArray(), 'id');
            $notificationData['notificationGuestsIds'] = $guestsIds;
            $notificationData['notificationUsersIds'] = $votersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.EditMeetingDecisionNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.EditMeetingDecisionNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingDecision.addVote')) {
            $votersIds = array_values(array_unique(array_merge([$decision->meeting->created_by], $meetingOrganisersIds)));
            $data['voteStatusNameAr'] = $extraData['vote_status_id'] == config('voteStatuses.yes') ? Lang::get('translation.meeting_decision.vote_status.yes', [], 'ar') : ($extraData['vote_status_id'] == config('voteStatuses.no') ? Lang::get('translation.meeting_decision.vote_status.no', [], 'ar') : Lang::get('translation.meeting_decision.vote_status.abstain', [], 'ar'));
            $data['voteStatusNameEn'] = $extraData['vote_status_id'] == config('voteStatuses.yes') ? Lang::get('translation.meeting_decision.vote_status.yes', [], 'en') : ($extraData['vote_status_id'] == config('voteStatuses.no') ? Lang::get('translation.meeting_decision.vote_status.no', [], 'en') : Lang::get('translation.meeting_decision.vote_status.abstain', [], 'en'));
            $notificationData['notificationUsersIds'] = $votersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.ChangeMeetingDecisionStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.ChangeMeetingDecisionStatusNotificationMessageAr', $data);
        }

        return $notificationData;
    }

    public function prepareNotificationDataForDocumentation($document, $user, $notificationType, $extraData = [])
    {
        $notificationData = [];
        $data = [];
        $reviewersIds = array_column($document->reviewres->toArray(), 'id');
        $index = array_search($document->added_by, $reviewersIds);
        if ($index >= 0) {
            unset($reviewersIds[$index]);
            $reviewersIds = array_values($reviewersIds);
        }
        $notificationData['notificationExtraData'] = [];
        $data['documentSubject'] = $document->document_subject_ar;
        $data['documentDescription'] = $document->document_description_ar;
        $data['changedByNameAr'] = $user->name_ar ? $user->name_ar : $user->name;
        $data['changedByNameEn'] = $user->name ? $user->name : $user->name_ar;
        $notificationData['notificationIcon'] = config('notificationIcons.reviewDocument');
        $notificationData['notificationUrl'] = config('notificationUrls.reviewDocument') . $document->id;
        $notificationData['notificationModelType'] = config('notificationModelTypes.reviewDocument');
        $notificationData['notificationModelId'] = $document->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.DocumentNotificationTitleAr', $notificationData);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.DocumentNotificationTitleEn', $notificationData);

        if ($notificationType == config('documentNotification.startDocument')) {
            $notificationData['notificationUsersIds'] = $reviewersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.StartDocumentNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.StartDocumentNotificationMessageAr', $data);
        } else if ($notificationType == config('documentNotification.editDocument')) {
            $notificationData['notificationUsersIds'] = $reviewersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.EditDocumentNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.EditDocumentNotificationMessageAr', $data);
        } else if ($notificationType == config('documentNotification.addAnnotation')) {
            $notificationData['notificationUsersIds'] = [$document->added_by];
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.AddAnnotationNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.AddAnnotationNotificationMessageAr', $data);
        } else if ($notificationType == config('documentNotification.editAnnotation')) {
            $notificationData['notificationUsersIds'] = [$document->added_by];
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.EditAnnotationNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.EditAnnotationNotificationMessageAr', $data);
        } else if ($notificationType == config('documentNotification.deleteAnnotation')) {
            $notificationData['notificationUsersIds'] = [$document->added_by];
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.DeleteAnnotationNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.DeleteAnnotationNotificationMessageAr', $data);
            $notificationData['notificationExtraData']['deleted_annotation_id'] = $extraData['annotation_id'];
        } else if ($notificationType == config('documentNotification.completeReview')) {
            $notificationData['notificationUsersIds'] = [$document->added_by];
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.CompleteDocumentReviewNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.CompleteDocumentReviewNotificationMessageAr', $data);
        }

        return $notificationData;
    }

    public function prepareNotificationDataForCircularDecision($decision, $user, $notificationType, $extraData = [])
    {
        $notificationData = [];
        $data = [];
        $votersIds = array_column($decision->voters->toArray(), 'id');
        $index = array_search($decision->creator_id, $votersIds);
        if ($index >= 0) {
            unset($votersIds[$index]);
            $votersIds = array_values($votersIds);
        }
        $data['circularDecisionSubjectEn'] = $decision->vote_subject_en ? $decision->vote_subject_en : $decision->vote_subject_ar;
        $data['circularDecisionSubjectAr'] = $decision->vote_subject_ar ? $decision->vote_subject_ar : $decision->vote_subject_en;
        $data['changedByNameAr'] = $user->name_ar ? $user->name_ar : $user->name;
        $data['changedByNameEn'] = $user->name ? $user->name : $user->name_ar;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.ChangeCircularDecisionStatusNotificationTitleAr', $notificationData);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.ChangeCircularDecisionStatusNotificationTitleEn', $notificationData);
        $notificationData['notificationIcon'] = config('notificationIcons.circularDecision');
        $notificationData['notificationUrl'] = config('notificationUrls.circularDecision') . $decision->id;
        $notificationData['notificationModelType'] = config('notificationModelTypes.circularDecision');
        $notificationData['notificationModelId'] = $decision->id;

        if ($notificationType == config('decisionNotification.changeVote')) {
            $data['voteStatusNameAr'] = $extraData['vote_status_id'] == config('voteStatuses.yes') ? Lang::get('translation.circular_decision.vote_status.yes', [], 'ar') : ($extraData['vote_status_id'] == config('voteStatuses.no') ? Lang::get('translation.circular_decision.vote_status.no', [], 'ar') : Lang::get('translation.circular_decision.vote_status.abstain', [], 'ar'));
            $data['voteStatusNameEn'] = $extraData['vote_status_id'] == config('voteStatuses.yes') ? Lang::get('translation.circular_decision.vote_status.yes', [], 'en') : ($extraData['vote_status_id'] == config('voteStatuses.no') ? Lang::get('translation.circular_decision.vote_status.no', [], 'en') : Lang::get('translation.circular_decision.vote_status.abstain', [], 'en'));
            $notificationData['notificationUsersIds'] = [$decision->creator_id];
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.ChangeCircularDecisionStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.ChangeCircularDecisionStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('decisionNotification.startDecision')) {
            $notificationData['notificationUsersIds'] = $votersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.StartCircularDecisionNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.StartCircularDecisionNotificationMessageAr', $data);
        } else if ($notificationType == config('decisionNotification.editDecision')) {
            $notificationData['notificationUsersIds'] = $votersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.EditCircularDecisionNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.EditCircularDecisionNotificationMessageAr', $data);
        }

        return $notificationData;
    }

    public function prepareNotificationDataAtCreateNewNotification($notificationData)
    {
        $notification = [];

        $notification['notification_title_ar'] = $notificationData['notificationTitleAr'];
        $notification['notification_title_en'] = $notificationData['notificationTitleEn'];
        $notification['notification_body_ar'] = $notificationData['notificationMessageAr'];
        $notification['notification_body_en'] = $notificationData['notificationMessageEn'];
        $notification['notification_icon'] = $notificationData['notificationIcon'];
        $notification['notification_model_id'] = $notificationData['notificationModelId'];
        $notification['notification_url'] = $notificationData['notificationUrl'];
        $notification['notification_model_type'] = $notificationData['notificationModelType'];
        $notification['notification_date'] = Carbon::now();
        $notification['notification_users'] = [];

        foreach ($notificationData['notificationUsersIds'] as $key => $notificationUserId) {
            $notification['notification_users'][$key]['user_id'] = $notificationUserId;
            $notification['notification_users'][$key]['is_read'] = false;
        }

        return $notification;
    }

    public function prepareNotificationDataForMeeting($meeting, $user, $notificationType, $extraData = [])
    {
        $data = [];
        $notificationData = [];
        $meetingParticipantsIds = array_column($meeting->meetingParticipants->toArray(), 'id');
        $meetingOrganisersIds = array_column($meeting->meetingOrganisers->toArray(), 'id');

        $data['meetingTitleEn'] = $meeting->meeting_title_en ? $meeting->meeting_title_en : $meeting->meeting_title_ar;
        $data['meetingTitleAr'] = $meeting->meeting_title_ar ? $meeting->meeting_title_ar : $meeting->meeting_title_en;
        $data['changedByNameAr'] = $user->name_ar ? $user->name_ar : $user->name;
        $data['changedByNameEn'] = $user->name ? $user->name : $user->name_ar;
        $notificationData['notificationIcon'] = config('notificationIcons.meeting');
        $notificationData['notificationUrl'] = config('notificationUrls.meeting') . $meeting->id;
        $notificationData['notificationModelType'] = config('notificationModelTypes.meeting');
        $notificationData['notificationModelId'] = $meeting->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.MeetingNotificationTitleAr', $notificationData);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.MeetingNotificationTitleEn', $notificationData);
        $notificationData['notificationExtraData'] = [];

        if ($notificationType == config('meetingNotifications.publishMeeting')) {
            $membersIds = array_values(array_unique(array_merge($meetingParticipantsIds, $meetingOrganisersIds)));
            $membersIds = array_values(array_unique(array_merge($membersIds, $user->id == $meeting->created_by ? [] : [$meeting->created_by])));
            $guestsIds = array_column($meeting->guests->toArray(), 'id');
            $data['meetingStatusAr'] = Lang::get('translation.meeting.status.sendInvitations', [], 'ar');
            $data['meetingStatusEn'] = Lang::get('translation.meeting.status.sendInvitations', [], 'en');
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationGuestsIds'] = $guestsIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingNotifications.publishMeetingAgenda')) {
            $membersIds = array_values(array_unique(array_merge($meetingParticipantsIds, $meetingOrganisersIds)));
            $membersIds = array_values(array_unique(array_merge($membersIds, $user->id == $meeting->created_by ? [] : [$meeting->created_by])));
            $guestsIds = array_column($meeting->guests->toArray(), 'id');
            $data['meetingStatusAr'] = Lang::get('translation.meeting.status.publishAgenda', [], 'ar');
            $data['meetingStatusEn'] = Lang::get('translation.meeting.status.publishAgenda', [], 'en');
            $notificationData['notificationGuestsIds'] = $guestsIds;
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingNotifications.startMeeting')) {
            $membersIds = array_values(array_unique(array_merge($meetingParticipantsIds, $meetingOrganisersIds)));
            $membersIds = array_values(array_unique(array_merge($membersIds, $user->id == $meeting->created_by ? [] : [$meeting->created_by])));
            $guestsIds = array_column($meeting->guests->toArray(), 'id');
            $data['meetingStatusAr'] = Lang::get('translation.meeting.status.startMeeting', [], 'ar');
            $data['meetingStatusEn'] = Lang::get('translation.meeting.status.startMeeting', [], 'en');
            $notificationData['notificationGuestsIds'] = $guestsIds;
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingNotifications.endMeeting')) {
            $membersIds = array_values(array_unique(array_merge($meetingParticipantsIds, $meetingOrganisersIds)));
            $membersIds = array_values(array_unique(array_merge($membersIds, $user->id == $meeting->created_by ? [] : [$meeting->created_by])));
            $guestsIds = array_column($meeting->guests->toArray(), 'id');
            $data['meetingStatusAr'] = Lang::get('translation.meeting.status.endMeeting', [], 'ar');
            $data['meetingStatusEn'] = Lang::get('translation.meeting.status.endMeeting', [], 'en');
            $notificationData['notificationGuestsIds'] = $guestsIds;
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingNotifications.sendRecommendation')) {
            $membersIds = array_values(array_unique(array_merge($meetingParticipantsIds, $meetingOrganisersIds)));
            $membersIds = array_values(array_unique(array_merge($membersIds, $user->id == $meeting->created_by ? [] : [$meeting->created_by])));
            $guestsIds = array_column($meeting->guests->toArray(), 'id');
            $data['meetingStatusAr'] = Lang::get('translation.meeting.status.sendRecommendation', [], 'ar');
            $data['meetingStatusEn'] = Lang::get('translation.meeting.status.sendRecommendation', [], 'en');
            $notificationData['notificationGuestsIds'] = $guestsIds;
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingNotifications.publishMeetingChanges')) {
            $membersIds = array_values(array_unique(array_merge($meetingParticipantsIds, $meetingOrganisersIds)));
            $membersIds = array_values(array_unique(array_merge($membersIds, $user->id == $meeting->created_by ? [] : [$meeting->created_by])));
            $guestsIds = array_column($meeting->guests->toArray(), 'id');
            $data['meetingStatusAr'] = Lang::get('translation.meeting.status.publishMeetingChanges', [], 'ar');
            $data['meetingStatusEn'] = Lang::get('translation.meeting.status.publishMeetingChanges', [], 'en');
            $notificationData['notificationGuestsIds'] = $guestsIds;
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingNotifications.editMom')) {
            $membersIds = $meetingOrganisersIds;
            $membersIds = array_values(array_unique(array_merge($membersIds, $user->id == $meeting->created_by ? [] : [$meeting->created_by])));
            $data['meetingStatusAr'] = Lang::get('translation.meeting.status.editMom', [], 'ar');
            $data['meetingStatusEn'] = Lang::get('translation.meeting.status.editMom', [], 'en');
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationUrl'] = config('notificationUrls.editMom') . $meeting->id;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingNotifications.sendMom')) {
            $membersIds = array_values(array_unique(array_merge($meetingParticipantsIds, $meetingOrganisersIds)));
            $membersIds = array_values(array_unique(array_merge($membersIds, $user->id == $meeting->created_by ? [] : [$meeting->created_by])));
            $guestsIds = array_column($meeting->guests->toArray(), 'id');
            $data['meetingStatusAr'] = Lang::get('translation.meeting.status.sendMom', [], 'ar');
            $data['meetingStatusEn'] = Lang::get('translation.meeting.status.sendMom', [], 'en');
            $notificationData['notificationGuestsIds'] = $guestsIds;
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingNotifications.sendSignature')) {
            $membersIds = [$extraData['user_id']];
            $data['meetingStatusAr'] = Lang::get('translation.meeting.status.sendSignature', [], 'ar');
            $data['meetingStatusEn'] = Lang::get('translation.meeting.status.sendSignature', [], 'en');
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingStatusNotificationMessageAr', $data);
        } else if ($notificationType == config('meetingNotifications.attendanceChangedByOrganiser')) {
            if ($extraData['meeting_attendance_status'] == config('meetingAttendanceStatus.attend')) {
                $data['meetingAttendanceStatusAr'] = Lang::get('translation.meeting.attendanceStatus.attend', [], 'ar');
                $data['meetingAttendanceStatusEn'] = Lang::get('translation.meeting.attendanceStatus.attend', [], 'en');
            } else if ($extraData['meeting_attendance_status'] == config('meetingAttendanceStatus.absent')) {
                $data['meetingAttendanceStatusAr'] = Lang::get('translation.meeting.attendanceStatus.absent', [], 'ar');
                $data['meetingAttendanceStatusEn'] = Lang::get('translation.meeting.attendanceStatus.absent', [], 'en');
            } else {
                $data['meetingAttendanceStatusAr'] = Lang::get('translation.meeting.attendanceStatus.acceptAbsent', [], 'ar');
                $data['meetingAttendanceStatusEn'] = Lang::get('translation.meeting.attendanceStatus.acceptAbsent', [], 'en');
            }

            $notificationData['notificationUsersIds'] = [$extraData['user_id']];
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.MeetingAttendanceStatusNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.MeetingAttendanceStatusNotificationMessageAr', $data);
        }

        return $notificationData;
    }

    public function prepareNotificationDataForTask($task, $user, $notificationType, $userIds = [], $extraData = [])
    {
        $data = [];
        $extraUsersIds = [];
        $notificationData = [];
        $parentObjectOwner = null;
        if ($task->meeting_id) {
            $parentObjectOwner = $task->taskMeeting->created_by;
        }
        if ($task->vote_id) {
            $parentObjectOwner = $task->decision->creator_id;
        }
        $data['serial_number'] = $task->serial_number;
        $data['taskCreatorNameAr'] = ($task->createdBy->name_ar ? $task->createdBy->name_ar : $task->createdBy->name);
        $data['taskCreatorNameEn'] = ($task->createdBy->name ? $task->createdBy->name : $task->createdBy->name_ar);
        $data['taskStatusNameAr'] = $task->taskStatus->task_status_name_ar;
        $data['taskStatusNameEn'] = $task->taskStatus->task_status_name_en;
        $notificationData['notificationIcon'] = config('notificationIcons.task');
        $notificationData['notificationUrl'] = config('notificationUrls.task') . $task->id;
        $notificationData['notificationModelType'] = config('notificationModelTypes.task');
        $notificationData['notificationModelId'] = $task->id;
        $notificationData['notificationExtraData'] = [];
        if ($user) {
            $extraUsersIds = ($parentObjectOwner != null && $user->id == $parentObjectOwner) ? [] : array_merge($extraUsersIds, [$user->id]);
            $extraUsersIds = $user->id == $task->assigned_to ? [] : array_merge($extraUsersIds, [$task->assigned_to]);
            $data['changedByNameAr'] = ($user->name_ar ? $user->name_ar : $user->name);
            $data['changedByNameEn'] = ($user->name ? $user->name : $user->name_ar);
        }
        if ($notificationType == config('taskNotifications.addTask')) {
            $notificationData['notificationUsersIds'] = [$task->assigned_to];
            $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.NewTaskNotificationTitleEn', $data);
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.NewTaskNotificationMessageEn', $data);
            $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.NewTaskNotificationTitleAr', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.NewTaskNotificationMessageAr', $data);
        } else if ($notificationType == config('taskNotifications.editTask')) {
            $notificationData['notificationUsersIds'] = [$task->assigned_to];
            $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.EditTaskNotificationTitleEn', $data);
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.EditTaskNotificationMessageEn', $data);
            $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.EditTaskNotificationTitleAr', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.EditTaskNotificationMessageAr', $data);
        } else if ($notificationType == config('taskNotifications.changeTaskStatus')) {
            $membersIds = array_values(array_unique(array_merge($userIds, $extraUsersIds)));
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.TaskStatusChangedNotificationTitleEn', $data);
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.TaskStatusChangedNotificationMessageEn', $data);
            $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.TaskStatusChangedNotificationTitleAr', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.TaskStatusChangedNotificationMessageAr', $data);
        } else if ($notificationType == config('taskNotifications.addComment')) {
            $membersIds = array_values(array_unique(array_merge($userIds, $extraUsersIds)));
            $notificationData['notificationUsersIds'] = $membersIds;
            $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.AddCommentToTaskNotificationTitleEn', $data);
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.AddCommentToTaskNotificationMessageEn', $data);
            $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.AddCommentToTaskNotificationTitleAr', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.AddCommentToTaskNotificationMessageAr', $data);
        } else if ($notificationType == config('taskNotifications.taskExpired')) {
            $notificationData['notificationUsersIds'] = [$task->assigned_to];
            $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.TaskExpiredNotificationTitleEn', $data);
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.TaskExpiredNotificationMessageEn', $data);
            $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.TaskExpiredNotificationTitleAr', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.TaskExpiredNotificationMessageAr', $data);
        }

        return $notificationData;
    }

    public function prepareNotificationDataForChat($chatGroup, $user, $notificationType, $extraData = [])
    {
        $data = [];
        $notificationData = [];
        $groupChatUsersIds = array_column($chatGroup->memberUsers->toArray(), 'id');
        $groupChatGuestsIds = array_column($chatGroup->guests->toArray(), 'id');
        $data['chatGroupNameEn'] = $chatGroup->chat_group_name_en ? $chatGroup->chat_group_name_en : $chatGroup->chat_group_name_ar;
        $data['chatGroupNameAr'] = $chatGroup->chat_group_name_ar ? $chatGroup->chat_group_name_ar : $chatGroup->chat_group_name_en;
        $data['changedByNameAr'] = $user->name_ar ? $user->name_ar : $user->name;
        $data['changedByNameEn'] = $user->name ? $user->name : $user->name_ar;
        $notificationData['notificationIcon'] = config('notificationIcons.chatGroup');
        $notificationData['notificationUrl'] = config('notificationUrls.chatGroup');
        $notificationData['notificationModelType'] = config('notificationModelTypes.chatGroup');
        $notificationData['notificationModelId'] = $chatGroup->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.ChatGroupNotificationTitleAr', $notificationData);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.ChatGroupNotificationTitleEn', $notificationData);
        $notificationData['notificationExtraData'] = [];

        if ($notificationType == config('chatGroupNotifications.sendMessage')) {
            $notificationData['notificationUsersIds'] = $groupChatUsersIds;
            $notificationData['notificationGuestsIds'] = $groupChatGuestsIds;
            $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.ChatGroupNotificationMessageEn', $data);
            $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.ChatGroupNotificationMessageAr', $data);
        }

        return $notificationData;
    }

    public function prepareNotificationDataForSharingFile($directory, $file, $user, $isDirectory, $notificationType, $extraData = [])
    {
        $data = [];
        $notificationData = [];
        $data['changedByNameAr'] = $user->name_ar ? $user->name_ar : $user->name;
        $data['changedByNameEn'] = $user->name ? $user->name : $user->name_ar;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.SharingFilesNotificationTitleAr', $notificationData);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.SharingFilesNotificationTitleEn', $notificationData);
        $notificationData['notificationIcon'] = config('notificationIcons.directory');
        $notificationData['notificationModelType'] = $isDirectory ? config('notificationModelTypes.directory') : config('notificationModelTypes.file');
        $notificationData['notificationModelId'] = $isDirectory ? $directory->id : $file->id;
        $notificationData['notificationUsersIds'] = $extraData['users_ids'];

        switch ($notificationType) {
            case config('sharingNotifications.shareDirecrory'):
                $data['directoryNameAr'] = $directory->directory_name_ar ? $directory->directory_name_ar : $directory->directory_name;
                $data['directoryNameEr'] = $directory->directory_name ? $directory->directory_name : $directory->directory_name_ar;
                $notificationData['notificationUrl'] = config('notificationUrls.directory') . $directory->id;
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.SharingDirectoryNotificationMessageEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.SharingDirectoryNotificationMessageAr', $data);
                break;
            case config('sharingNotifications.shareFile'):
                $data['fileNameAr'] = $file->file_name_ar ? $file->file_name_ar : $file->file_name;
                $data['fileNameEr'] = $file->file_name ? $file->file_name : $file->file_name_ar;
                $notificationData['notificationUrl'] = config('notificationUrls.file');
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.SharingFileNotificationMessageEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.SharingFileNotificationMessageAr', $data);
                break;
            case config('sharingNotifications.removeDirectoryAccess'):
                $data['directoryNameAr'] = $directory->directory_name_ar ? $directory->directory_name_ar : $directory->directory_name;
                $data['directoryNameEr'] = $directory->directory_name ? $directory->directory_name : $directory->directory_name_ar;
                $notificationData['notificationUrl'] = null;
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.RemoveSharingDirectoryNotificationMessageEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.RemoveSharingDirectoryNotificationMessageAr', $data);
                break;
            case config('sharingNotifications.removeFileAccess'):
                $data['fileNameAr'] = $file->file_name_ar ? $file->file_name_ar : $file->file_name;
                $data['fileNameEr'] = $file->file_name ? $file->file_name : $file->file_name_ar;
                $notificationData['notificationUrl'] = config('notificationUrls.file');
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.RemoveSharingFileNotificationMessageEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.RemoveSharingFileNotificationMessageAr', $data);
                break;
        }
        return $notificationData;
    }

    public function prepareNotificationDataForApproveUnfreezeCommitteeMember($committee, $data)
    {
        $notificationData = [];
        $notificationData['notificationIcon'] = config('notificationIcons.unfreezing');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.committee');
        $notificationData['notificationModelId'] = $committee->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.UnfreezingCommitteeApprovedNotificationTitleAr', $notificationData);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.UnfreezingCommitteeApprovedNotificationTitleEn', $notificationData);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.UnfreezingCommitteeApprovedNotificationMessageEn', $committee);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.UnfreezingCommitteeApprovedNotificationMessageAr', $committee);
        $notificationData['notificationUsersIds'] = [$data['created_by']];
        return $notificationData;
    }


    public function prepareNotificationDataForRejectRequest($request)
    {
        $notificationData = [];
        $notificationData['notificationIcon'] = config('notificationIcons.reject');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationModelId'] = $request->id;
        switch ($request->request_type_id) {
            case config('requestTypes.unfreezeCommittee'):
                $notificationData['notificationUsersIds'] = [$request['created_by']];
                $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.UnfreezingCommitteeRejectedNotificationTitleEn', $request);
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.UnfreezingCommitteeRejectedNotificationMessageEn', $request);
                $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.UnfreezingCommitteeRejectedNotificationTitleAr', $request);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.UnfreezingCommitteeRejectedNotificationMessageAr', $request);
                break;
            case config('requestTypes.addCommittee'):
                $notificationData['notificationUsersIds'] = [$request['request_body']['committee_head_id']];
                $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.RejectAddCommitteeNotificationTitleEn', $request);
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.RejectAddCommitteeNotificationMessageEn', $request);
                $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.RejectAddCommitteeNotificationTitleAr', $request);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.RejectAddCommitteeNotificationMessageAr', $request);
                break;

            case config('requestTypes.deleteFile'):
                $data = [];
                $data['committee_name_en'] = $request->request_body['committee_name_en'];
                $data['committee_name_ar'] = $request->request_body['committee_name_ar'];
                $data["file_name"] = $request->request_body["file"]['file_name'];
                $data["file_name_ar"] = $request->request_body["file"]['file_name_ar'];
                $data['reject_reason'] = $request->reject_reason;
                $notificationData['notificationUsersIds'] = [$request->created_by];
                $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationTitleAr', $data);
                $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationTitleEn', $data);
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationMessageEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationMessageAr', $data);
                break;


            case config('requestTypes.deleteUser'):
                $data = [];
                $data['committee_name_en'] = $request->request_body['committee_name_en'];
                $data['committee_name_ar'] = $request->request_body['committee_name_ar'];
                $data['reject_reason'] = $request->reject_reason;
                $notificationData['notificationUsersIds'] = [$request->created_by];
                $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationTitleAr', $data);
                $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationTitleEn', $data);
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationMessageEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationMessageAr', $data);
                break;

            case config('requestTypes.addUserToCommittee'):
                $data = [];
                $data['committee_name_en'] = $request->request_body['committee_name_en'];
                $data['committee_name_ar'] = $request->request_body['committee_name_ar'];
                $data['reject_reason'] = $request->reject_reason;
                $notificationData['notificationUsersIds'] = [$request->created_by];
                $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationTitleAr', $data);
                $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationTitleEn', $data);
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationMessageEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationMessageAr', $data);
                break;
            case config('requestTypes.updateCommittee'):
                $data = [];
                $data['committee_name_en'] = $request->request_body['committee_name_en'];
                $data['committee_name_ar'] = $request->request_body['committee_name_ar'];
                $data['reject_reason'] = $request->reject_reason;
                $notificationData['notificationUsersIds'] = [$request->created_by];
                $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeRequestRejectedNotificationTitleAr', $data);
                $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeRequestRejectedNotificationTitleEn', $data);
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeRequestRejectedNotificationMessageEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeRequestRejectedNotificationMessageAr', $data);
                break;
        }
        return $notificationData;
    }

    public function prepareNotificationDataForAcceptAddCommitteeRequest($committee, $requestData)
    {
        $notificationData = [];
        $notificationData['notificationIcon'] = config('notificationIcons.unfreezing');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.committee');
        $notificationData['notificationModelId'] = $committee->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.ApproveAddCommitteeNotificationTitleAr', $notificationData);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.ApproveAddCommitteeNotificationTitleEn', $notificationData);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.ApproveAddCommitteeNotificationMessageEn', $committee);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.ApproveAddCommitteeNotificationMessageAr', $committee);
        $notificationData['notificationUsersIds'] = [$committee['committee_head_id']];
        return $notificationData;
    }


    public function prepareNotificationDataForRequest($requestData)
    {
        $data = [];
        $notificationData = [];
        $data['requestTypeNameEn'] = $requestData->requestType->request_type_name_en
            ?? $requestData->requestType->request_type_name_ar;

        $data['requestTypeNameAr'] = $requestData->requestType->request_type_name_ar
            ?? $requestData->requestType->request_type_name_en;

        $notificationData['notificationTitleAr'] = 'الطلبات';
        $notificationData['notificationTitleEn'] = 'Requests';
        $notificationData['notificationIcon'] = config('notificationIcons.requests');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationModelId'] = $requestData->id;
        $notificationData['notificationUsersIds'] = [$requestData->orgnization->system_admin_id];

        switch ($requestData->request_type_id)
        {
            case config('requestTypes.deleteFile'):
                $data = [];
                $data['committee_name_en'] = $requestData->request_body['committee_name_en'];
                $data['committee_name_ar'] = $requestData->request_body['committee_name_ar'];
                $data["file_name"] = $requestData->request_body["file"]['file_name'];
                $data["file_name_ar"] = $requestData->request_body["file"]['file_name_ar'];
                $notificationData['notificationUrl'] = config('notificationUrls.requests') .'delete-file/'. $requestData->id;
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.DeleteRequestNotificationEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.DeleteRequestNotificationAr', $data);
                break;
            case config('requestTypes.addUserToCommittee'):
                $notificationData['notificationUrl'] = config('notificationUrls.requests') .'add-member/'. $requestData->id;
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.AddUserToCommitteeRequestNotificationEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.AddUserToCommitteeRequestNotificationAr', $data);
                break;
            case config('requestTypes.addCommittee'):
                $notificationData['notificationUrl'] = config('notificationUrls.requests') .'add-committee-requests/'. $requestData->id;
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.AddCommitteeRequestNotificationEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.AddCommitteeRequestNotificationAr', $data);
                break;
            case config('requestTypes.unfreezeCommittee'):
                $notificationData['notificationUrl'] = config('notificationUrls.requests') .'unfreeze-requests/'. $requestData->id;
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.UnFreezeCommitteeRequestNotificationEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.UnFreezeCommitteeRequestNotificationAr', $data);        
                break;
            case config('requestTypes.deleteUser'):
                $data["committee_name_en"]=$requestData->request_body["committee_name_en"];
                $data["committee_name_ar"]=$requestData->request_body["committee_name_ar"];
                $notificationData['notificationUrl'] = config('notificationUrls.requests') .'delete-member/'. $requestData->id;
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.DeleteCommitteeUserRequestNotificationEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.DeleteCommitteeUserRequestNotificationAr', $data);        
                break;
            case config('requestTypes.updateCommittee'):
                $data["committee_name_en"] = $requestData->request_body["committee_name_en"];
                $data["committee_name_ar"] = $requestData->request_body["committee_name_ar"];
                $notificationData['notificationUrl'] = config('notificationUrls.requests') . 'update-committee-requests/' . $requestData->id;
                $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeRequestNotificationEn', $data);
                $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeRequestNotificationAr', $data);
                break;
        }

        return $notificationData;
    }



    public function prepareNotificationDataForAcceptAddMemberRequest($request)
    {
        $notificationData = [];
        $data = [];
        $data['committee_name_ar'] = $request->request_body['committee_name_ar'];
        $data['committee_name_en'] = $request->request_body['committee_name_en'];
        $notificationData['notificationIcon'] = config('notificationIcons.accept');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationUsersIds'] = [$request->created_by];
        $notificationData['notificationModelId'] = $request->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.addMemberRequestApprovedNotificationTitleAr', $data);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.addMemberRequestApprovedNotificationTitleEn', $data);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.addMemberRequestApprovedNotificationMessageEn', $data);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.addMemberRequestApprovedNotificationMessageAr', $data);


        return $notificationData;
    }


    public function prepareNotificationDataForRejectAddMemberRequest($request)
    {
        $notificationData = [];
        $data = [];
        $data['committee_name'] = $request->request_body['committee_name'];
        $data['reject_reason'] = $request->reject_reason;
        $notificationData['notificationIcon'] = config('notificationIcons.reject');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationUsersIds'] = [$request->created_by];
        $notificationData['notificationModelId'] = $request->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationTitleAr', $data);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationTitleEn', $data);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationMessageEn', $data);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationMessageAr', $data);


        return $notificationData;
    }



    public function prepareNotificationDataForAcceptDeleteMemberRequest($request)
    {
        $notificationData = [];
        $data = [];
        $data['committee_name_en'] = $request->request_body['committee_name_en'];
        $data['committee_name_ar'] = $request->request_body['committee_name_ar'];
        $notificationData['notificationIcon'] = config('notificationIcons.accept');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationUsersIds'] = [$request->created_by];
        $notificationData['notificationModelId'] = $request->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestApprovedNotificationTitleAr', $data);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestApprovedNotificationTitleEn', $data);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestApprovedNotificationMessageEn', $data);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestApprovedNotificationMessageAr', $data);


        return $notificationData;
    }


    public function prepareNotificationDataForRejectDeleteMemberRequest($request)
    {
        $notificationData = [];
        $data = [];
        $data['committee_name'] = $request->request_body['committee_name'];
        $data['reject_reason'] = $request->reject_reason;
        $notificationData['notificationIcon'] = config('notificationIcons.reject');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationUsersIds'] = [$request->created_by];
        $notificationData['notificationModelId'] = $request->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationTitleAr', $data);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationTitleEn', $data);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationMessageEn', $data);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationMessageAr', $data);


        return $notificationData;
    }




    public function prepareNotificationDataForAcceptDeleteFileRequest($request)
    {
        $notificationData = [];
        $data = [];
        $data['committee_name_en'] = $request->request_body['committee_name_en'];
        $data['committee_name_ar'] = $request->request_body['committee_name_ar'];
        $data["file_name"] = $request->request_body["file"]['file_name'];
        $data["file_name_ar"] = $request->request_body["file"]['file_name_ar'];
        $notificationData['notificationIcon'] = config('notificationIcons.accept');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationUsersIds'] = [$request->created_by];
        $notificationData['notificationModelId'] = $request->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestApprovedNotificationTitleAr', $data);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestApprovedNotificationTitleEn', $data);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestApprovedNotificationMessageEn', $data);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestApprovedNotificationMessageAr', $data);


        return $notificationData;
    }

    public function prepareNotificationDataForNearedExpiredCommittees($committee, $mangerId)
    {
        $notificationData = [];
        $data = [];
        $memberIds = array_column($committee->memberUsers->toArray(),'id');
        $memberIds = array_merge([$mangerId], $memberIds);
        $data['committee_name_ar'] = $committee->committee_name_ar ?? $committee->committee_name_en;
        $data['committee_name_en'] = $committee->committee_name_en ?? $committee->committee_name_ar;
        $notificationData['notificationIcon'] = config('notificationIcons.accept');
        $notificationData['notificationUrl'] = config('notificationUrls.viewCommittee'). $committee->id;
        $notificationData['notificationModelType'] = config('notificationModelTypes.committee');
        $notificationData['notificationUsersIds'] = $memberIds;
        $notificationData['notificationModelId'] = $committee->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.NearedExpiredCommitteeTitleAr', $data);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.NearedExpiredCommitteeTitleEn', $data);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.NearedExpiredCommitteeEn', $data);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.NearedExpiredCommitteeAr', $data);


        return $notificationData;
    }

    public function prepareNotificationDataForRejectDeleteFileRequest($request)
    {
        $notificationData = [];
        $data = [];
        $data['committee_name_en'] = $request->request_body['committee_name_en'];
        $data['committee_name_ar'] = $request->request_body['committee_name_ar'];
        $data["file_name"] = $request->request_body["file"]['file_name'];
        $data["file_name_ar"] = $request->request_body["file"]['file_name_ar'];
        $data['reject_reason'] = $request->reject_reason;
        $notificationData['notificationIcon'] = config('notificationIcons.reject');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationUsersIds'] = [$request->created_by];
        $notificationData['notificationModelId'] = $request->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationTitleAr', $data);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationTitleEn', $data);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationMessageEn', $data);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationMessageAr', $data);


        return $notificationData;
    }

    public function prepareNotificationDataForExpiredCommitteeMissingFinalOutput($committee, $mangerId)
    {
        $notificationData = [];
        $data = [];
        $data['committee_name_ar'] = $committee->committee_name_ar ?? $committee->committee_name_en;
        $data['committee_name_en'] = $committee->committee_name_en ?? $committee->committee_name_ar;
        $notificationData['notificationIcon'] = config('notificationIcons.accept');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationUsersIds'] = [$mangerId];
        $notificationData['notificationModelId'] = $committee->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.ExpiredCommitteeMissingFinalOutputTitleAr', $data);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.ExpiredCommitteeMissingFinalOutputTitleEn', $data);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.ExpiredCommitteeMissingFinalOutputEn', $data);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.ExpiredCommitteeMissingFinalOutputAr', $data);


        return $notificationData;
    }

    public function prepareNotificationDataForUpdateCommitteeRequest($request)
    {
        $notificationData = [];
        $data = [];
        $data['committee_name_en'] = $request->request_body['committee_name_en'];
        $data['committee_name_ar'] = $request->request_body['committee_name_ar'];
        $notificationData['notificationIcon'] = config('notificationIcons.accept');
        $notificationData['notificationUrl'] = config('notificationUrls.committees');
        $notificationData['notificationModelType'] = config('notificationModelTypes.request');
        $notificationData['notificationUsersIds'] = [$request->created_by];
        $notificationData['notificationModelId'] = $request->id;
        $notificationData['notificationTitleAr'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeNotificationTitleAr', $data);
        $notificationData['notificationTitleEn'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeNotificationTitleEn', $data);
        $notificationData['notificationMessageEn'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeNotificationMessageEn', $data);
        $notificationData['notificationMessageAr'] = NotificationHelper::getNotificationData('notification.UpdateCommitteeNotificationMessageAr', $data);


        return $notificationData;
    }

}
