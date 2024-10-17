<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Helpers\EmailHelper;
use Helpers\EventHelper;
use Helpers\NotificationHelper;
use Helpers\SecurityHelper;
use Helpers\TaskHelper;
use Illuminate\Http\Request;
use Jobs\SendAddCommentToTaskNotification;
use Jobs\SendEditTaskNotification;
use Jobs\SendNewTaskNotification;
use Jobs\SendTaskStatusChangedNotification;
use Models\TaskManagement;
use PDF;
use Services\CommitteeService;
use Services\MeetingService;
use Services\NotificationService;
use Services\VoteService;
use Services\TaskManagementService;
use Services\RoleRightService;
use Validator;

class TaskManagementController extends Controller
{
    private $taskManagementService;
    private $securityHelper;
    private $meetingService;
    private $notificationHelper;
    private $emailHelper;
    private $eventHelper;
    private $taskHelper;
    private $committeeService;
    private $roleRightService;
    private $notificationService;
    private $voteService;

    public function __construct(TaskManagementService $taskManagementService,
        SecurityHelper $securityHelper,
        MeetingService $meetingService,
        NotificationHelper $notificationHelper,
        EmailHelper $emailHelper,
        EventHelper $eventHelper,
        TaskHelper $taskHelper,
        CommitteeService $committeeService,
        RoleRightService $roleRightService, NotificationService $notificationService, VoteService $voteService)
    {
        $this->taskManagementService = $taskManagementService;
        $this->securityHelper = $securityHelper;
        $this->meetingService = $meetingService;
        $this->notificationHelper = $notificationHelper;
        $this->emailHelper = $emailHelper;
        $this->eventHelper = $eventHelper;
        $this->taskHelper = $taskHelper;
        $this->committeeService = $committeeService;
        $this->roleRightService = $roleRightService;
        $this->notificationService = $notificationService;
        $this->voteService = $voteService;
    }

    public function index()
    {
        return response()->json($this->taskManagementService->getAll(), 200);
    }

    public function show($id)
    {
        return response()->json($this->taskManagementService->getTaskDetails($id), 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if (isset($data['start_date'])) {
            $data['start_date'] = new Carbon($data['start_date']['year'].'-'.$data['start_date']['month'].'-'.$data['start_date']['day']);
        }
        $data['created_by'] = $user->id;
        $data['organization_id'] = $user->organization_id;

        $validator = Validator::make($data, TaskManagement::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $committee = null;
        if (isset($data['meeting_id'])) {
            $meeting = $this->meetingService->getById($data['meeting_id']);
            $committee = $meeting->committee;
            $data['committee_id'] = $committee->id;

        }
        if (isset($data['vote_id'])) {
            $vote = $this->voteService->getById($data['vote_id']);
            $committee = $vote->committee;
            $data['committee_id'] = $committee->id;
        }

        $lastTaskSequenceForOrganization = $this->taskManagementService->getLasTaskSequenceForOrganization($user->organization_id);
        $data = $this->taskHelper->prepareTaskDataAtCreation($data, $lastTaskSequenceForOrganization, $user->organization, $committee);
        $task = $this->taskManagementService->create($data);
        SendNewTaskNotification::dispatch($task, $user);

        return response()->json($task, 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $task = $this->taskManagementService->getById($id);
        $user = $this->securityHelper->getCurrentUser();
        $oldAssignee = $task->assigned_to;

        if (isset($data['start_date'])) {
            $data['start_date'] = new Carbon($data['start_date']['year'] . '-' . $data['start_date']['month'] . '-' . $data['start_date']['day']);
        }

        $validator = Validator::make($data, TaskManagement::rules('update'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $this->taskManagementService->update($id, $data);
        $task = $this->taskManagementService->getById($id);

        $newAssignee = $task->assigned_to;

        if($oldAssignee == $newAssignee){
            SendEditTaskNotification::dispatch($task,$user );
        }
        else{
            SendNewTaskNotification::dispatch($task,$user );
        }
        
        return response()->json(['message' => 'Task updated successfully'], 200);
    }

    public function destroy($id)
    {
        $this->taskManagementService->delete($id);
        return response()->json(['message' => "success"], 200);
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();

        if (!isset($filter->SearchObject["meeting_id"])) {
            return response()->json(['error' => "Meeting id is required"], 400);
        } else {
            $meeting = $this->meetingService->getById($filter->SearchObject["meeting_id"]);
            $organisers = array_column($meeting->organisers->toArray(), 'user_id');
            $participants = array_column($meeting->participants->toArray(), 'user_id');
            if (($user && $user->organization_id === $meeting->organization_id) && (in_array($user->id, $organisers) || in_array($user->id, $participants))) {
                return response()->json($this->taskManagementService->getPagedList($filter), 200);
            } else {
                return response()->json(['message' => ["Not Allowed"]], 401);
            }
        }

    }

    public function myTasksDashboard(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $filter = (object) ($request->all());
        $filter->SearchObject["assigned_to"] = $user->id;
        $dashboard["myTasks"] = $this->taskManagementService->getPagedList($filter);
        $dashboard["myTasksCounts"] = $this->taskManagementService->getUserTaskCount($user->id);
        return response()->json($dashboard, 200);
    }

    public function organizationTasksDashboard(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $filter = (object) ($request->all());
        $filter->SearchObject["organization_id"] = $user->organization_id;
        $canAccess = $this->roleRightService->canAccess($user->role_id, config('rights.allCommitteesTasks'));
        $filter->SearchObject["is_all_taskes"] = count($canAccess) > 0? true : false;
        $filter->SearchObject["user_id"] = $user->id;
        $dashboard["organizationTasks"] = $this->taskManagementService->getPagedList($filter);
        if (!isset($filter->SearchObject['task_statistics_type_id']) || (isset($filter->SearchObject['task_statistics_type_id']) && $filter->SearchObject['task_statistics_type_id'] == null)) {
            $dashboard["organizationTasksCounts"] = $this->taskManagementService->getorganizationTaskCount($user->organization_id,$filter->SearchObject["is_all_taskes"],$user->id);
        }
        //$dashboard["chart"] = $this->taskManagementService->organizationChartData($user->organization_id,$filter->SearchObject["is_all_taskes"],$user->id, date("Y"), date("n"));
        $dashboard["currentMonthNameEn"] = Carbon::now()->formatLocalized('%B');
        setlocale(LC_ALL, 'ar_AE.utf8');
        $dashboard["currentMonthNameAr"] = Carbon::now()->formatLocalized('%B');
        setlocale(LC_ALL, 'en_EN.utf8');
        return response()->json($dashboard, 200);
    }

    public function startTask(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $task = $this->taskManagementService->getById($request->taskId);
 
        $users = [];

        if($task->meeting_id){
            $meeting = $task->taskMeeting;
            $users = $meeting->meetingOrganisers->toArray();
        }
        if($task->vote_id){
            $decision = $task->decision;
            $users = [$decision->creator];
        }


        $userIds = array_column($users, 'id');
        if (
            (in_array($user->id, $userIds)) ||
            ($task->assigned_to == $user->id && $task->task_status_id == config('taskStatuses.new')) ||
            ($user->role_id == config('roles.organizationAdmin') && ($task->organization_id == $user->organization_id))
        ) {
            $data = $this->taskHelper->prepareTaskUpdatedData($request->all(),config('taskStatuses.inProgress'));
            $this->taskManagementService->update($request->taskId, $data);

            //send notification to organisers if status changed
            if ($task->assigned_to == $user->id) {
                $task = $this->taskManagementService->getById($request->taskId);
                SendTaskStatusChangedNotification::dispatch($task,$userIds, $user,$users);
                // $notificationData = $this->notificationHelper->prepareTaskStatusChangedNotificationData($task, $meeting, $organisersIds, $user);
                // $this->eventHelper->fireEvent($notificationData, 'App\Events\TaskStatusChangedNotificationEvent');
                // $emailData = $this->taskHelper->prepareTaskStatusChangedEmailData($task, $meeting, $user);
                // foreach ($meetingOrganisers as $organiser) {
                //     $this->emailHelper->sendTaskStatusChangedMail($organiser->email, $organiser->name_ar, $organiser->name, $emailData["meeting_title_ar"], $emailData["meeting_title_en"], $emailData["task_status_name_ar"], $emailData["task_status_name_en"], $emailData["changed_by_name_en"], $emailData["changed_by_name_ar"], $emailData["task_id"], $organiser->language_id);
                // }
            }

            return response()->json(['message' => 'Task started successfully'], 200);
        } else if ($task->assigned_to == $user->id && $task->task_status_id != config('taskStatuses.new')) {
            return response()->json(['error' => 'Task Can\'t be Started ,it\'s not new',
                'error_ar' => 'لا يمكن بدء هذه المهمة لانها ليست جديده'], 400);
        }
        return response()->json(['message' => ["Not Allowed"]], 401);
    }

    public function endTask(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $task = $this->taskManagementService->getById($request->taskId);
        $users = [];

        if($task->meeting_id){
            $meeting = $task->taskMeeting;
            $users = $meeting->meetingOrganisers->toArray();
        }
        if($task->vote_id){
            $decision = $task->decision;
            $users = [$decision->creator];
        }


        $userIds = array_column($users, 'id');
        if (
            (in_array($user->id, $userIds)) ||
            ($task->assigned_to == $user->id && $task->task_status_id == config('taskStatuses.inProgress')) ||
            ($user->role_id == config('roles.organizationAdmin') && ($task->organization_id == $user->organization_id))
        ) {
            $data = $this->taskHelper->prepareTaskUpdatedData($request->all(),config('taskStatuses.done'));
            $this->taskManagementService->update($request->taskId, $data);

            //send notification to organisers if status changed
            if ($task->assigned_to == $user->id) {
                $task = $this->taskManagementService->getById($request->taskId);
                SendTaskStatusChangedNotification::dispatch($task,$userIds, $user,$users);
                // $notificationData = $this->notificationHelper->prepareTaskStatusChangedNotificationData($task, $meeting, $organisersIds, $user);
                // $this->eventHelper->fireEvent($notificationData, 'App\Events\TaskStatusChangedNotificationEvent');
                // $emailData = $this->taskHelper->prepareTaskStatusChangedEmailData($task, $meeting, $user);
                // foreach ($meetingOrganisers as $organiser) {
                //     $this->emailHelper->sendTaskStatusChangedMail($organiser->email, $organiser->name_ar, $organiser->name, $emailData["meeting_title_ar"], $emailData["meeting_title_en"], $emailData["task_status_name_ar"], $emailData["task_status_name_en"], $emailData["changed_by_name_en"], $emailData["changed_by_name_ar"], $emailData["task_id"], $organiser->language_id);
                // }
            }

            return response()->json(['message' => 'Task ended successfully'], 200);
        } else if ($task->assigned_to == $user->id && $task->task_status_id != config('taskStatuses.inProgress')) {
            return response()->json(['error' => 'Task Can\'t be Ended ,it\'s not started yet',
                'error_ar' => 'لا يمكن إنهاء هذه المهمة لانها لم تبدأ بعد'], 400);
        }
        if ($task->assigned_to == $user->id) {

        }
        return response()->json(['message' => ["Not Allowed"]], 401);
    }

    public function renewTask(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $task = $this->taskManagementService->getById($request->taskId);
        $users = [];

        if($task->meeting_id){
            $meeting = $task->taskMeeting;
            $users = $meeting->meetingOrganisers->toArray();
        }
        if($task->vote_id){
            $decision = $task->decision;
            $users = [$decision->creator];
        }


        $userIds = array_column($users, 'id');        
        if (
            (in_array($user->id, $userIds)) ||
            ($user->role_id == config('roles.organizationAdmin') && ($task->organization_id == $user->organization_id))
        ) {
            $data = $this->taskHelper->prepareTaskUpdatedData($request->all(),config('taskStatuses.new'));
            $this->taskManagementService->update($request->taskId, $data);
            //send notification to organisers if status changed
            if ($task->assigned_to == $user->id) {
                $task = $this->taskManagementService->getById($request->taskId);
                SendTaskStatusChangedNotification::dispatch($task,$userIds, $user,$users);

            }
            return response()->json(['message' => 'Task renewed successfully'], 200);
        } else if ($task->assigned_to == $user->id) {
            return response()->json(['error' => 'You Can\'t renew this task',
                'error_ar' => 'لا يمكنك إعاده هذه المهمة إلى جديدة'], 400);
        }
        return response()->json(['message' => ["Not Allowed"]], 401);
    }

    public function sendTasksExpiredNotifications()
    {
        $expiresTasks = $this->taskManagementService->getExpiredTasks();

        foreach ($expiresTasks as $task) {
            $assignee = $task->assignee;
            $users = [];

            if($task->meeting_id){
                $meeting = $task->taskMeeting;
                $users = $meeting->meetingOrganisers->toArray();
            }
            if($task->vote_id){
                $decision = $task->decision;
                $users = [$decision->creator];
            }
    
    
            $userIds = array_column($users, 'id');
            $notificationData = $this->notificationHelper->prepareTaskExpiredNotificationData($task);
            $this->eventHelper->fireEvent($notificationData, 'App\Events\TaskExpiredNotificationEvent');
            $notification = $this->notificationHelper->prepareNotificationDataForTask($task,null,config('taskNotifications.taskExpired'),$userIds,[]);
            $this->notificationService->sendNotification($notification);
            $emailData = $this->taskHelper->prepareTaskExpiredEmailData($task);
            $this->emailHelper->sendTaskExpiredMail($assignee->email, $assignee->name_ar, $assignee->name,$task->serial_number, $emailData["task_id"], $assignee->language_id);

        }
    }

    public function downloadTasksPdf(string $lang){

        $user = $this->securityHelper->getCurrentUser();
        $canAccess = $this->roleRightService->canAccess($user->role_id, config('rights.allCommitteesTasks'));
        $isAllTasks = count($canAccess) > 0? true : false;
        $committees = $this->taskManagementService->getTasksGroupedByCommittee($user->organization_id,$isAllTasks,$user->id);
        $data = ['committees' => $committees, 'organization' => $user->organization];

        if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.mjlsi')) {
            $pdfFolderName = 'pdf';
        }
        else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.gaft')) {
            $pdfFolderName = 'pdf-gaft';
        }
        else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.eca')) {
            $pdfFolderName = 'pdf-eca';
        }
        else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.lcgpa')) {
            $pdfFolderName = 'pdf-lcgpa';
        } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.sadu')) {
                        $pdfFolderName = 'pdf-sadu';
        } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.swcc')) {
            $pdfFolderName = 'pdf-swcc';
         }
        $fileName = $lang == config('languages.ar')? $pdfFolderName.'.committee-tasks-ar' : $pdfFolderName.'.committee-tasks-en';
        $pdfEn = PDF::loadView($fileName, ['data' => $data])->download('committee_tasks.pdf');
            
        return response()->json([], 200);
    }
    
    public function getOrganizationCommitteeTasksDashboard(Request $request, $committeeId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $filter = (object) ($request->all());
        $filter->SearchObject["organization_id"] = $user->organization_id;
        $filter->SearchObject["committee_id"] = $committeeId;
        $dashboard["committee"] = $this->committeeService->getById($committeeId);
        $dashboard["committeeTasks"] = $this->taskManagementService->getPagedList($filter);
        $dashboard["committeeTasksCounts"] = $this->taskManagementService->getCommitteeTaskCount($user->organization_id,$committeeId, true, $user->id);
        //$dashboard["chart"] = $this->taskManagementService->organizationCommitteeChartData($user->organization_id,$committeeId, date("Y"), date("n"));
        $dashboard["currentMonthNameEn"] = Carbon::now()->formatLocalized('%B');
        setlocale(LC_ALL, 'ar_AE.utf8');
        $dashboard["currentMonthNameAr"] = Carbon::now()->formatLocalized('%B');
        setlocale(LC_ALL, 'en_EN.utf8');
        return response()->json($dashboard, 200);
    }

    public function addCommentToTaskHistory(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $task = $this->taskManagementService->getById($request->taskId);

        $users = [];

        if($task->meeting_id){
            $meeting = $task->taskMeeting;
            $users = $meeting->meetingOrganisers->toArray();
        }
        if($task->vote_id){
            $decision = $task->decision;
            $users = [$decision->creator];
        }


        $userIds = array_column($users, 'id');

        if (
            (in_array($user->id, $userIds)) ||
            ($task->assigned_to == $user->id) ||
            ($user->role_id == config('roles.organizationAdmin') && ($task->organization_id == $user->organization_id))
        ) {
            $this->taskManagementService->addCommentToTaskHistory($request->taskId, $data);

            //send notification to organisers if status changed
            if ($task->assigned_to == $user->id) {
                $task = $this->taskManagementService->getById($request->taskId);
                SendAddCommentToTaskNotification::dispatch($task,$userIds, $user,$users);
            }

            return response()->json(['message' => 'Comment added successfully'], 200);
        }
        return response()->json(['message' => ["Not Allowed"]], 401);
    }
    
    public function downloadCommitteeTasksPdf(int $committeeId,int $lang){
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->taskManagementService->getCommitteeTasks($user->organization_id,$committeeId, $user->id);
        $data = ['committees' => [$committee], 'organization' => $user->organization];
        $pdfFolderName = $this->taskHelper->getPDFFolderName();
        $fileName = $lang == config('languages.ar')? $pdfFolderName.'.committee-tasks-ar' : $pdfFolderName.'.committee-tasks-en';
        $pdfEn = PDF::loadView($fileName, ['data' => $data])->download('committee_tasks.pdf');
            
        return response()->json([], 200);
    }

    public function downloadTasksStatisticsPdf(Request $request,int $lang) {
        $data = $request->all();
        $extraData = null;
        $user = $this->securityHelper->getCurrentUser();
        $canAccess = $this->roleRightService->canAccess($user->role_id, config('rights.allCommitteesTasks'));
        $isAllTasks = count($canAccess) > 0? true : false;
        $committees = $this->taskManagementService->getTasksStatistics($user->organization_id,$data,$isAllTasks,$user->id);
        if(count($committees) == 0 && isset($data['committee_id'])) {
            $committee = $this->committeeService->getById($data['committee_id']);
            $extraData = $committee;
        }
        $statisticData = $this->taskHelper->getStatisticName($data);
        $data = ['committees' => $committees, 'organization' => $user->organization , 'statistic_name_ar' => $statisticData['statistic_name_ar'], 'statistic_name_en' => $statisticData['statistic_name_en'], 'extra_data' => $extraData];
        $pdfFolderName = $this->taskHelper->getPDFFolderName();
        $fileName = $lang == config('languages.ar')? $pdfFolderName.'.tasks-statistics-ar' : $pdfFolderName.'.tasks-statistics-en';
        $pdfEn = PDF::loadView($fileName, ['data' => $data])->download('tasks_statistics.pdf');
    }

    public function details($id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $timeZone = $user->organization->timeZone;
        $data = [];
        $task = $this->taskManagementService->getTaskForUpdate($id);

        $data['task']= $task;
        if($task->meeting_id){
            $meetingDetails = $this->meetingService->getMeetingDetails($task->meeting_id);
            $data['users']= $meetingDetails['meeting_participants'];
            $data['agendas']= $meetingDetails['meeting_agendas'];

        }
        if($task->vote_id){
            $decision = $this->voteService->getCircularDecicion($task->vote_id,$user->id,$timeZone);
            $data['users']= $decision['voters'];
            $data['agendas']= [];
        }

        return response()->json($data, 200);
    }
}
