<?php

namespace Services;

use Helpers\TaskActionHistoryHelper;
use Illuminate\Database\DatabaseManager;
use Repositories\TaskActionHistoryRepository;
use Repositories\TaskManagementRepository;
use Repositories\CommitteeRepository;
use \Illuminate\Database\Eloquent\Model;

class TaskManagementService extends BaseService
{

    private $taskActionHistoryRepository;
    private $taskActionHistoryHelper;
    private $committeeRepository;

    public function __construct(DatabaseManager $database, TaskManagementRepository $repository, TaskActionHistoryRepository $taskActionHistoryRepository,
        TaskActionHistoryHelper $taskActionHistoryHelper, CommitteeRepository $committeeRepository) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->taskActionHistoryRepository = $taskActionHistoryRepository;
        $this->taskActionHistoryHelper = $taskActionHistoryHelper;
        $this->committeeRepository = $committeeRepository;
    }

    public function prepareCreate(array $data)
    {
        $task = $this->repository->create($data);
        $statusLogData = $this->taskActionHistoryHelper->prepareLogData($data["task_status_id"], $task->id,null,false);
        $this->taskActionHistoryRepository->create($statusLogData);
        return $task;
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $comment = null;
        if (isset($data['task_comment_text'])) {
            $comment = $data['task_comment_text'];
            unset($data['task_comment_text']);
        }
        $this->repository->update($data, $model->id);
        if (isset($data["task_status_id"]) && $model->task_status_id != $data["task_status_id"]) {
            $statusLogData = $this->taskActionHistoryHelper->prepareLogData($data["task_status_id"], $model->id,$comment,false);
            $this->taskActionHistoryRepository->create($statusLogData);
        }
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function getPagedList($filter)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "task_management.id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        return $this->repository->getPagedTasksList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection);
    }

    public function getUserTaskCount(int $userId)
    {
        $results = $this->repository->getUserTaskCount($userId);
        if (!$results) {
            return ["new_tasks" => 0, "progress_tasks" => 0, "done_tasks" => 0, "all_tasks" => 0];
        }
        return $results;
    }
    public function getOrganizationTaskDashboard(int $organizationId)
    {
        $results = $this->repository->getOrganizationTaskDashboard($organizationId);
        if (!$results) {
            return ["new_tasks" => 0, "progress_tasks" => 0, "done_tasks" => 0, "all_tasks" => 0,'delayed_tasks'=> 0];
        }
        return $results;
    }

    public function getCommitteeTaskDashboard(int $committeeId)
    {
        $results = $this->repository->getCommitteeTaskDashboard($committeeId);
        if (!$results) {
            return ["new_tasks" => 0, "progress_tasks" => 0, "done_tasks" => 0, "all_tasks" => 0,'delayed_tasks'=> 0];
        }
        return $results;
    }

    

    public function getorganizationTaskCount(int $organizationId, $isAllTasks, int $userId)
    {
        $results = $this->repository->getorganizationTaskCount($organizationId, $isAllTasks, $userId);
        if (!$results) {
            return ["new_tasks" => 0, "progress_tasks" => 0, "done_tasks" => 0, "delay_tasks" => 0, "tasks_of_week" => 0, "tasks_of_month" => 0,"later_tasks" => 0, 'total_tasks' => 0];
        }
        return $results;
    }

    public function organizationChartData(int $organizationId,$isAllTasks,$userId, $year, $month)
    {
        $tasksInMonth = $this->repository->organizationChartData($organizationId,$isAllTasks,$userId, $year, $month)->toArray();
        $numberOfDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $tasksInAllDays = [];
        for ($i = 1; $i <= $numberOfDaysInMonth; $i++) {
            if (array_key_exists($i, $tasksInMonth)) {
                //"Key exists!";
                $tasksInAllDays[$i] = $tasksInMonth[$i];
            } else {
                // "Key does not exist!";
                $tasksInAllDays[$i] = [];
            }
        }
        $results = [];
        $i = 0;
        foreach ($tasksInAllDays as $day => $tasksStatusId) {
            $results[$i]["name"] = $day;
            $results[$i]["value"] = array_sum(array_values($tasksStatusId));
            $results[$i]['done'] = 0;

            if (count($tasksStatusId) != 0) {
                foreach ($tasksStatusId as $taskStatusId => $count) {
                    if ($taskStatusId == config('taskStatuses.done')) {
                        $results[$i]['done'] = $count;
                    }
                }
            }
            $i++;
        }

        $allTasksCount = collect($results)->map(function ($element) {
            array_forget($element, 'done');
            return $element;
        });

        $doneTasksCount = collect($results)->map(function ($element) {
            array_forget($element, 'value');
            return array(
                'name' => $element['name'],
                'value' => $element['done'],
            );
        });

        return [
            "chartEn" => [
                ["name" => "All Tasks", "series" => $allTasksCount],
                ["name" => "Done Tasks", "series" => $doneTasksCount],
            ],
            "chartAr" => [
                ["name" => "مجمل المهام التى تم إنشاؤها", "series" => $allTasksCount],
                ["name" => "مهام تم تنفيذها", "series" => $doneTasksCount],
            ],
            "ticks" => array_column($results, "name"),
        ];

    }

    public function getTaskDetails($taskId)
    {
        $results = $this->repository->getTaskDetails($taskId);
        $results["task_status_history_group"] = $results["taskStatusHistory"]->groupBy('action_date')->map(function ($groupItems) {
            return $groupItems->sortBy('action_time')->values()->all();
        });
        return $results;
    }

    public function getTaskForUpdate($taskId)
    {
        $results = $this->repository->getTaskForUpdate($taskId);
        return $results;
    }

    public function getExpiredTasks()
    {
        return $this->repository->getExpiredTasks();
    }

    public function getTasksGroupedByCommittee($organizationId,$isAllTasks,$userId){
        return array_values($this->repository->getTasksGroupedByCommittee($organizationId,null,$isAllTasks,$userId)->groupBy(['committee_id'])->map(function ($groupItems) use ($organizationId,$isAllTasks,$userId) {
            $data['committee_name_en'] = $groupItems[0]['committee_name_en'];
            $data['committee_name_ar'] = $groupItems[0]['committee_name_ar'];
            $committeeCounts = $this->getCommitteeTaskCount($organizationId,$groupItems[0]['committee_id'],$isAllTasks,$userId);

            $data['new_tasks'] = $committeeCounts->new_tasks;
            $data['progress_tasks'] = $committeeCounts->progress_tasks;
            $data['done_tasks'] = $committeeCounts->done_tasks;
            $data['delay_tasks'] = $committeeCounts->delay_tasks;
            $data['tasks_of_week'] = $committeeCounts->tasks_of_week;
            $data['tasks_of_month'] = $committeeCounts->tasks_of_month;
            $data['later_tasks'] = $committeeCounts->later_tasks;

            $data['tasks'] = $groupItems;
            return $data;
        })->toArray());
    }

    public function getCommitteeTaskCount($organizationId,$committeeId,$isAllTasks,$userId){
        $results = $this->repository->getCommitteeTaskCount($organizationId,$committeeId,$isAllTasks,$userId);
        if (!$results) {
            return ["new_tasks" => 0, "progress_tasks" => 0, "done_tasks" => 0, "delay_tasks" => 0, "tasks_of_week" => 0, "tasks_of_month" => 0,"later_tasks" => 0, 'total_tasks' => 0];
        }
        return $results;
    }

    public function organizationCommitteeChartData($organizationId,$committeeId, $year, $month){
        $tasksInMonth = $this->repository->organizationCommitteeChartData($organizationId,$committeeId, $year, $month)->toArray();
        $results = $this->calculateTasksStatusCount ($tasksInMonth, $month, $year);
        
        $allTasksCount = collect($results)->map(function ($element) {
            array_forget($element, 'done');
            return $element;
        });

        $doneTasksCount = collect($results)->map(function ($element) {
            array_forget($element, 'value');
            return array(
                'name' => $element['name'],
                'value' => $element['done'],
            );
        });

        return [
            "chartEn" => [
                ["name" => "All Tasks", "series" => $allTasksCount],
                ["name" => "Done Tasks", "series" => $doneTasksCount],
            ],
            "chartAr" => [
                ["name" => "مجمل المهام التى تم إنشاؤها", "series" => $allTasksCount],
                ["name" => "مهام تم تنفيذها", "series" => $doneTasksCount],
            ],
            "ticks" => array_column($results, "name"),
        ];
    }

    private function calculateTasksStatusCount ($tasksInMonth, $month, $year) {
        $numberOfDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $tasksInAllDays = [];
        for ($i = 1; $i <= $numberOfDaysInMonth; $i++) {
            if (array_key_exists($i, $tasksInMonth)) {
                //"Key exists!";
                $tasksInAllDays[$i] = $tasksInMonth[$i];
            } else {
                // "Key does not exist!";
                $tasksInAllDays[$i] = [];
            }
        }
        $results = [];
        $i = 0;
        foreach ($tasksInAllDays as $day => $tasksStatusId) {
            $results[$i]["name"] = $day;
            $results[$i]["value"] = array_sum(array_values($tasksStatusId));
            $results[$i]['done'] = 0;

            if (count($tasksStatusId) != 0) {
                foreach ($tasksStatusId as $taskStatusId => $count) {
                    if ($taskStatusId == config('taskStatuses.done')) {
                        $results[$i]['done'] = $count;
                    }
                }
            }
            $i++;
        }
        return $results;
    }

    public function addCommentToTaskHistory($taskId, array $data)
    {
        $comment = $data['task_comment_text'];
        $task = $this->getById($taskId);
        $statusLogData = $this->taskActionHistoryHelper->prepareLogData($task->task_status_id, $taskId,$comment,true);
        $this->taskActionHistoryRepository->create($statusLogData);
    }
    
    public function getCommitteeTasks($organizationId,$committeeId, $userId){
        $data = [];

        $tasks = $this->repository->getCommitteeTasks($organizationId,$committeeId)->toArray();
        $committee = $this->committeeRepository->getCommitteeData($organizationId,$committeeId);
        $committeeCounts = $this->getCommitteeTaskCount($organizationId,$committeeId, true,$userId);

        $data['tasks'] = $tasks;
        $data['committee_name_en'] = $committee->committee_name_en;
        $data['committee_name_ar'] = $committee->committee_name_ar;
        $data['new_tasks'] = $committeeCounts->new_tasks;
        $data['progress_tasks'] = $committeeCounts->progress_tasks;
        $data['done_tasks'] = $committeeCounts->done_tasks;
        $data['delay_tasks'] = $committeeCounts->delay_tasks;
        $data['tasks_of_week'] = $committeeCounts->tasks_of_week;
        $data['tasks_of_month'] = $committeeCounts->tasks_of_month;
        $data['later_tasks'] = $committeeCounts->later_tasks;

        return $data;
    }

    public function getTasksStatistics($organizationId,$data,$isAllTasks,$uesrId){
        return array_values($this->repository->getTasksGroupedByCommittee($organizationId,$data,$isAllTasks,$uesrId)->groupBy(['committee_id'])->map(function ($groupItems){
            $data['committee_name_en'] = $groupItems[0]['committee_name_en'];
            $data['committee_name_ar'] = $groupItems[0]['committee_name_ar'];
            $data['tasks'] = $groupItems;
            return $data;
        })->toArray());
    }

    public function getLasTaskSequenceForOrganization($organizationId){
        return $this->repository->getLasTaskSequenceForOrganization($organizationId);
    }

    public function getDelayedTasksStatusStatisticsForUser($userId){
        return $this->repository->getDelayedTasksStatusStatisticsForUser($userId)->count();
    }

    public function getLimitOfTasksForUser($userId){
        return $this->repository->getLimitOfTasksForUser($userId);
    }

    public function getLimitOfTasksForOrganization($organizationId){
        return $this->repository->getLimitOfTasksForOrganization($organizationId);
    }

    public function getLimitOfTasksForCommittee($committeeId){
        return $this->repository->getLimitOfTasksForCommittee($committeeId);
    }
}
