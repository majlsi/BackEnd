<?php

namespace Repositories;

use Carbon\Carbon;

class TaskManagementRepository extends BaseRepository
{
    public function model()
    {
        return 'Models\TaskManagement';
    }

    public function getPagedTasksList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection)
    {
        $query = $this->getAllTaskQuery($searchObj);

        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllTaskQuery($searchObj)
    {
        $q = $this->model;
        if (isset($searchObj->description)) {
            $q = $q->whereRaw('(description like ?)', ['%'.$searchObj->description.'%']);
        }

        if (isset($searchObj->meeting_id)) {
            $q = $q->where('task_management.meeting_id', $searchObj->meeting_id);
        }

        if (isset($searchObj->vote_id)) {
            $q = $q->where('vote_id', $searchObj->vote_id);
        }
        if (isset($searchObj->assigned_to)) {
            $q = $q->where('assigned_to', $searchObj->assigned_to);
        }

        if (isset($searchObj->task_status_id)) {
            $q = $q->where('task_status_id', $searchObj->task_status_id);
        }

        if (isset($searchObj->meeting_agenda_id)) {
            $q = $q->where('meeting_agenda_id', $searchObj->meeting_agenda_id);
        }

        if (isset($searchObj->organization_id)) {
            $q = $q->where('users.organization_id', $searchObj->organization_id)
                ->leftJoin('users', 'users.id', 'task_management.assigned_to');
        }
        if (isset($searchObj->committee_id)) {
            $q = $q->where('committee_id', $searchObj->committee_id);
        }

        if (isset($searchObj->start_date) && isset($searchObj->end_date)) {
            $q = $q->whereRaw('(start_date >= ? and DATE_ADD(start_date, INTERVAL number_of_days DAY) BETWEEN ? and  ?)', [$searchObj->start_date, $searchObj->start_date, $searchObj->end_date]);
        } elseif (isset($searchObj->start_date)) {
            $q = $q->whereDate('task_management.start_date', '>=', $searchObj->start_date);
        } elseif (isset($searchObj->end_date)) {
            $q = $q->whereRaw('DATE_ADD(start_date, INTERVAL number_of_days DAY) <= '.'"'.$searchObj->end_date.'"');
        }

        if (isset($searchObj->is_all_taskes) && isset($searchObj->user_id) && !$searchObj->is_all_taskes) {
            $q = $q->whereRaw('(committees.committee_head_id = ? OR committees.committee_organiser_id = ?)', [$searchObj->user_id, $searchObj->user_id]);
        }

        if (isset($searchObj->task_statistics_type_id)) {
            $q = $this->getTasksStatistics($q, $searchObj->task_statistics_type_id);
        }

        if (isset($searchObj->serial_number)) {
            $q = $q->whereRaw('(serial_number like ?)', ['%'.trim($searchObj->serial_number).'%']);
        }

        $q = $q->leftJoin('task_statuses', 'task_statuses.id', 'task_management.task_status_id')
            ->leftJoin('committees', 'committees.id', 'task_management.committee_id');

        $q = $q->with(['assignee' => function ($query) {
            $query->selectRaw('users.*,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                            nicknames.nickname_ar,nicknames.nickname_en')
                ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id');
        }])->selectRaw('task_management.*,task_statuses.task_status_name_ar,task_statuses.task_status_name_en,
        committees.committee_name_en,committees.committee_name_ar,
        DATE_ADD(start_date, INTERVAL number_of_days DAY) as end_date,
        JSON_OBJECT(
            "month", CAST(DATE_FORMAT(start_date,"%m") AS UNSIGNED ),
            "year", CAST(DATE_FORMAT(start_date,"%Y") AS UNSIGNED ),
            "day", CAST(DAY(start_date)  AS UNSIGNED )) as start_date_formated
        ');


        return $q;
    }

    public function getUserTaskCount(int $userId)
    {
        return $this->model->selectRaw('
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_status_id = '.config('taskStatuses.new')
            ." AND assigned_to = $userId  AND deleted_at is null),0) as new_tasks,
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_status_id = ".config('taskStatuses.inProgress')
            ." AND assigned_to = $userId AND deleted_at is null ),0) as progress_tasks,
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_status_id = ".config('taskStatuses.done')
            ." AND assigned_to = $userId AND deleted_at is null ),0) as done_tasks,
        IFNULL((SELECT COUNT(id) FROM task_management WHERE  assigned_to = $userId AND deleted_at is null ),0) as all_tasks
        ")
            ->where('assigned_to', $userId)
            ->groupBy('assigned_to')
            ->first();
    }


    public function getOrganizationTaskDashboard(int $organizationId)
    {
        return $this->model->selectRaw('
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_status_id = '.config('taskStatuses.new')
            ." AND task_management.organization_id = $organizationId  AND deleted_at is null),0) as new_tasks,
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_status_id = ".config('taskStatuses.inProgress')
            ." AND task_management.organization_id = $organizationId AND deleted_at is null ),0) as progress_tasks,
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_status_id = ".config('taskStatuses.done')
            ." AND task_management.organization_id = $organizationId AND deleted_at is null ),0) as done_tasks,
        IFNULL((SELECT COUNT(id) FROM task_management WHERE  task_management.organization_id = $organizationId AND deleted_at is null ),0) as all_tasks
        ,
        IFNULL((SELECT COUNT(task_management.id) FROM task_management  WHERE task_status_id != ".config('taskStatuses.done')
                ." AND task_management.organization_id = $organizationId AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE() AND task_management.deleted_at is null ),0) as delayed_tasks")
                ->where('task_management.organization_id', $organizationId)
                ->groupBy('task_management.organization_id')

            ->first();
    }

    public function getCommitteeTaskDashboard(int $committee_id)
    {
        return $this->model->selectRaw('
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_status_id = '.config('taskStatuses.new')
            ." AND task_management.committee_id = $committee_id  AND deleted_at is null),0) as new_tasks,
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_status_id = ".config('taskStatuses.inProgress')
            ." AND task_management.committee_id = $committee_id AND deleted_at is null ),0) as progress_tasks,
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_status_id = ".config('taskStatuses.done')
            ." AND task_management.committee_id = $committee_id AND deleted_at is null ),0) as done_tasks,
        IFNULL((SELECT COUNT(id) FROM task_management WHERE task_management.committee_id = $committee_id AND deleted_at is null ),0) as all_tasks
        ,
        IFNULL((SELECT COUNT(task_management.id) FROM task_management WHERE task_status_id != ".config('taskStatuses.done')
                ." AND task_management.committee_id = $committee_id AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE() AND task_management.deleted_at is null ),0) as delayed_tasks")
                ->join('committees', 'committees.id', 'task_management.committee_id')
                ->groupBy('task_management.committee_id')

            ->first();
    }


    public function getorganizationTaskCount(int $organizationId, $isAllTasks, $userId)
    {
        if ($isAllTasks) {
            return $this->model->selectRaw('
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management 
                    left join users on users.id=task_management.assigned_to WHERE task_status_id = '.config('taskStatuses.new')
                ." AND task_management.organization_id = $organizationId  AND task_management.deleted_at is null),0) as new_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to WHERE task_management.organization_id = $organizationId  AND task_management.deleted_at is null),0) as total_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to WHERE task_status_id = ".config('taskStatuses.inProgress')
                ." AND task_management.organization_id = $organizationId AND task_management.deleted_at is null ),0) as progress_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to WHERE task_status_id = ".config('taskStatuses.done')
                ." AND task_management.organization_id = $organizationId AND task_management.deleted_at is null ),0) as done_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to WHERE task_status_id != ".config('taskStatuses.done')
                ." AND task_management.organization_id = $organizationId AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE() AND task_management.deleted_at is null ),0) as delay_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to WHERE task_management.organization_id = $organizationId
                    AND DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.week_days_number')." DAY)  AND task_management.deleted_at is null ),0) as tasks_of_week,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to WHERE task_management.organization_id = $organizationId
                    AND DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.month_days_number')." DAY)  AND task_management.deleted_at is null ),0) as tasks_of_month,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to WHERE task_management.organization_id = $organizationId
                    AND DATE_ADD(start_date, INTERVAL number_of_days DAY) > DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.month_days_number').' DAY)  AND task_management.deleted_at is null ),0) as later_tasks
                    ')
                ->where('task_management.organization_id', $organizationId)
                ->leftJoin('users', 'users.id', 'task_management.assigned_to')
                ->groupBy('task_management.organization_id')
                ->first();
        } else {
            return $this->model->selectRaw('
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE task_status_id = '.config('taskStatuses.new')
                ." AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId  AND task_management.deleted_at is null),0) as new_tasks,
                IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE
                 (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId  AND task_management.deleted_at is null),0) as total_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE task_status_id = ".config('taskStatuses.inProgress')
                ." AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId AND task_management.deleted_at is null ),0) as progress_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE task_status_id = ".config('taskStatuses.done')
                ." AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId AND task_management.deleted_at is null ),0) as done_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to join committees on committees.id = task_management.committee_id WHERE task_status_id != ".config('taskStatuses.done')
                ." AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE() AND task_management.deleted_at is null ),0) as delay_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE users.organization_id = $organizationId
                    AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.week_days_number')." DAY)  AND task_management.deleted_at is null ),0) as tasks_of_week,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE users.organization_id = $organizationId
                    AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.month_days_number')." DAY)  AND task_management.deleted_at is null ),0) as tasks_of_month,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE users.organization_id = $organizationId
                    AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND DATE_ADD(start_date, INTERVAL number_of_days DAY) > DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.month_days_number').' DAY)  AND task_management.deleted_at is null ),0) as later_tasks
                    ')
                ->where('task_management.organization_id', $organizationId)
                ->leftJoin('users', 'users.id', 'task_management.assigned_to')
                ->groupBy('users.organization_id')
                ->first();
        }
    }

    public function organizationChartData(int $organizationId, $isAllTasks, $userId, $year, $month)
    {
        $query = $this->model
            ->whereYear('start_date', '=', $year)
            ->whereMonth('start_date', '=', $month)
            ->where('users.organization_id', $organizationId)
            ->leftJoin('users', 'users.id', 'task_management.assigned_to')
            ->orderBy('start_date', 'asc');

        if (!$isAllTasks) {
            $query = $query->join('committees', 'committees.id', 'task_management.committee_id')
                        ->whereRaw('(committees.committee_head_id = ? OR committees.committee_organiser_id = ?)', [$userId, $userId]);
        }

        $grouped = $query->get()->groupBy([
            function ($date) {
                return Carbon::parse($date->start_date)->day; // grouping by day
            },
            'task_status_id', // grouping by task_status_id
        ]);

        /** count number of hours for each reservation source id ineach day in month */
        $groupCount = $grouped->map(function ($item) {
            return $item->map(function ($i, $k) {
                return collect($i)->count();
            });
        });

        return $groupCount;
    }

    public function getTaskDetails($taskId)
    {
        return $this->model->selectRaw('task_management.*,
        JSON_OBJECT(
            "month", CAST(DATE_FORMAT(start_date,"%m") AS UNSIGNED ),
            "year", CAST(DATE_FORMAT(start_date,"%Y") AS UNSIGNED ),
            "day", CAST(DAY(start_date)  AS UNSIGNED )) as start_date_formated
            ,DATE_ADD(start_date, INTERVAL number_of_days DAY) as end_date')->with([
            'assignee' => function ($query) {
                $query->selectRaw('users.*,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                            nicknames.nickname_ar,nicknames.nickname_en,
                            CASE WHEN images.image_url IS NULL
                            THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
                            ELSE images.image_url
                            END as image_url')
                    ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                    ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                    ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
                    ->leftJoin('images', 'images.id', 'users.profile_image_id');
            },
            'taskStatus',
            'createdBy' => function ($query) {
                $query->selectRaw('users.*,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                        user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                        nicknames.nickname_ar,nicknames.nickname_en,
                        CASE WHEN images.image_url IS NULL
                        THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
                        ELSE images.image_url
                        END as image_url')
                    ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                    ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                    ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
                    ->leftJoin('images', 'images.id', 'users.profile_image_id');
            },
            'taskStatusHistory' => function ($query) {
                $query->selectRaw('task_action_history.*,date(task_action_history.action_time) as action_date,users.name,users.name_ar,users.email,users.user_phone,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                            nicknames.nickname_ar,nicknames.nickname_en,task_statuses.task_status_name_ar,task_statuses.task_status_name_en,
                            CASE WHEN images.image_url IS NULL
                            THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
                            ELSE images.image_url
                            END as image_url')
                    ->leftJoin('users', 'users.id', 'task_action_history.user_id')
                    ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                    ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                    ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
                    ->leftJoin('images', 'images.id', 'users.profile_image_id')
                    ->leftJoin('task_statuses', 'task_statuses.id', 'task_action_history.task_status_id')
                    ->orderBy('action_time', 'desc');
            }, ])

            ->where('task_management.id', $taskId)
            ->first();
    }


    public function getTaskForUpdate($taskId)
    {
        return $this->model->selectRaw('task_management.*,
        JSON_OBJECT(
            "month", CAST(DATE_FORMAT(start_date,"%m") AS UNSIGNED ),
            "year", CAST(DATE_FORMAT(start_date,"%Y") AS UNSIGNED ),
            "day", CAST(DAY(start_date)  AS UNSIGNED )) as start_date_formated
            ,DATE_ADD(start_date, INTERVAL number_of_days DAY) as end_date')
            ->where('task_management.id', $taskId)
            ->first();
    }

    public function getExpiredTasks()
    {
        return $this->model
            ->whereNotIn('task_status_id', [config('taskStatuses.done')])
            ->whereRaw('DATE_ADD(start_date, INTERVAL number_of_days DAY) = CURRENT_DATE()')
            ->get();
    }

    public function getTasksGroupedByCommittee($organizationId, $data, $isAllTasks, $userId)
    {
        $q = $this->model;
        if (!$isAllTasks) {
            $q = $q->whereRaw('(committees.committee_head_id = ? OR committees.committee_organiser_id = ?)', [$userId, $userId]);
        }
        if (isset($data['task_statistics_type_id'])) {
            $q = $this->getTasksStatistics($q, $data['task_statistics_type_id']);
        }
        if (isset($data['committee_id'])) {
            $q = $q->where('task_management.committee_id', $data['committee_id']);
        }

        return $q->selectRaw('task_management.description,task_management.serial_number,task_management.start_date,task_statuses.task_status_name_ar,task_statuses.task_status_name_en,task_management.committee_id,
            committees.committee_name_en,committees.committee_name_ar,
            users.name as user_name,users.name_ar as  user_name_ar,
            job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
            nicknames.nickname_ar,nicknames.nickname_en')
            ->join('task_statuses', 'task_statuses.id', 'task_management.task_status_id')
            ->join('committees', 'committees.id', 'task_management.committee_id')
            ->join('users', 'users.id', 'task_management.assigned_to')
            ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
            ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
            ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
            ->where('users.organization_id', $organizationId)
            ->groupBy('users.organization_id', 'task_management.id')
            ->get();
    }

    public function getCommitteeTaskCount($organizationId, $committeeId, $isAllTasks, $userId)
    {
        $q = $this->model;
        if ($isAllTasks) {
            $q = $q->selectRaw('
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management  left join users on users.id=task_management.assigned_to WHERE task_status_id = '.config('taskStatuses.new')
                ." AND users.organization_id = $organizationId  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as new_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management  left join users on users.id=task_management.assigned_to WHERE 
                    users.organization_id = $organizationId  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as total_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management  left join users on users.id=task_management.assigned_to WHERE task_status_id = ".config('taskStatuses.inProgress')
                ." AND users.organization_id = $organizationId AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as progress_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management  left join users on users.id=task_management.assigned_to WHERE task_status_id = ".config('taskStatuses.done')
                ." AND users.organization_id = $organizationId AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as done_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management  left join users on users.id=task_management.assigned_to WHERE task_status_id != ".config('taskStatuses.done')
                ." AND users.organization_id = $organizationId AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE() AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as delay_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management  left join users on users.id=task_management.assigned_to WHERE users.organization_id = $organizationId
                    AND DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.week_days_number')." DAY)  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as tasks_of_week,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management  left join users on users.id=task_management.assigned_to WHERE users.organization_id = $organizationId
                    AND DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.month_days_number')." DAY)  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as tasks_of_month,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to WHERE users.organization_id = $organizationId
                    AND DATE_ADD(start_date, INTERVAL number_of_days DAY) > DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.month_days_number')." DAY)  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as later_tasks
                    ");
        } else {
            $q = $q->selectRaw('
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to join committees on committees.id = task_management.committee_id WHERE task_status_id = '.config('taskStatuses.new')
                ." AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as new_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE
                    (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as total_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE task_status_id = ".config('taskStatuses.inProgress')
                ." AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as progress_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE task_status_id = ".config('taskStatuses.done')
                ." AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as done_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE task_status_id != ".config('taskStatuses.done')
                ." AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND users.organization_id = $organizationId AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE() AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as delay_tasks,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to  join committees on committees.id = task_management.committee_id WHERE users.organization_id = $organizationId
                    AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.week_days_number')." DAY)  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as tasks_of_week,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to join committees on committees.id = task_management.committee_id WHERE users.organization_id = $organizationId
                    AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.month_days_number')." DAY)  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as tasks_of_month,
                    IFNULL((SELECT COUNT(task_management.id) FROM task_management left join users on users.id=task_management.assigned_to join committees on committees.id = task_management.committee_id WHERE users.organization_id = $organizationId
                    AND (committees.committee_head_id = $userId OR committees.committee_organiser_id = $userId) AND DATE_ADD(start_date, INTERVAL number_of_days DAY) > DATE_ADD(CURRENT_DATE(), INTERVAL ".config('organization.month_days_number')." DAY)  AND task_management.deleted_at is null AND task_management.committee_id = $committeeId),0) as later_tasks
                    ");
        }

        return $q->where('users.organization_id', $organizationId)
            ->leftJoin('users', 'users.id', 'task_management.assigned_to')
            ->groupBy('users.organization_id')
            ->first();
    }

    public function organizationCommitteeChartData($organizationId, $committeeId, $year, $month)
    {
        $grouped = $this->model
            ->whereYear('start_date', '=', $year)
            ->whereMonth('start_date', '=', $month)
            ->where('users.organization_id', $organizationId)
            ->where('task_management.committee_id', $committeeId)
            ->leftJoin('users', 'users.id', 'task_management.assigned_to')
            ->orderBy('start_date', 'asc')
            ->get()
            ->groupBy([
                function ($date) {
                    return Carbon::parse($date->start_date)->day; // grouping by day
                },
                'task_status_id', // grouping by task_status_id
            ]);

        /** count number of hours for each reservation source id ineach day in month */
        $groupCount = $grouped->map(function ($item) {
            return $item->map(function ($i, $k) {
                return collect($i)->count();
            });
        });

        return $groupCount;
    }

    public function getCommitteeTasks($organizationId, $committeeId)
    {
        return $this->model->selectRaw('task_management.description,task_management.serial_number,task_management.start_date,task_statuses.task_status_name_ar,task_statuses.task_status_name_en,
            users.name as user_name,users.name_ar as  user_name_ar,
            job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
            nicknames.nickname_ar,nicknames.nickname_en')
            ->join('task_statuses', 'task_statuses.id', 'task_management.task_status_id')
            ->join('committees', 'committees.id', 'task_management.committee_id')
            ->join('users', 'users.id', 'task_management.assigned_to')
            ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
            ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
            ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
            ->where('users.organization_id', $organizationId)
            ->where('task_management.committee_id', $committeeId)
            ->get();
    }

    private function getTasksStatistics($q, $taskStatisticsTypeId)
    {
        switch ($taskStatisticsTypeId) {
            case config('tasksStatisticsTypes.new_tasks'):
                return $q->where('task_status_id', config('taskStatuses.new'));
            case config('tasksStatisticsTypes.progress_tasks'):
                return $q->where('task_status_id', config('taskStatuses.inProgress'));
            case config('tasksStatisticsTypes.done_tasks'):
                return $q->where('task_status_id', config('taskStatuses.done'));
            case config('tasksStatisticsTypes.total_tasks'):
                return $q;
            case config('tasksStatisticsTypes.delay_tasks'):
                return $q->whereRaw('(task_status_id != ? AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE())', [config('taskStatuses.done')]);
            case config('tasksStatisticsTypes.tasks_of_week'):
                return $q->whereRaw('(DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL '.config('organization.week_days_number').' DAY))');
            case config('tasksStatisticsTypes.tasks_of_month'):
                return $q->whereRaw('(DATE_ADD(start_date, INTERVAL number_of_days DAY) >= CURRENT_DATE() AND DATE_ADD(start_date, INTERVAL number_of_days DAY) <= DATE_ADD(CURRENT_DATE(), INTERVAL '.config('organization.month_days_number').' DAY))');
            case config('tasksStatisticsTypes.later_tasks'):
                return $q->whereRaw('( DATE_ADD(start_date, INTERVAL number_of_days DAY) > DATE_ADD(CURRENT_DATE(), INTERVAL '.config('organization.month_days_number').' DAY))');
        }
    }

    public function getLasTaskSequenceForOrganization($organizationId)
    {
        return $this->model->selectRaw('task_management.id,task_sequence,task_management.created_at')
            ->whereRaw('(task_management.organization_id = '.$organizationId.' AND task_management.deleted_at  IS NULL)')
            ->whereRaw('DATE(task_management.created_at) = DATE("'.Carbon::now(config('app.timezone')).'") ')
            ->orderBy('task_management.id', 'desc')
            ->first();
    }

    public function getDelayedTasksStatusStatisticsForUser($userId){
        return $this->model->selectRaw('distinct task_management.*')
            ->whereRaw('(task_status_id != ? AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE())',array(config('taskStatuses.done')))
            ->where('assigned_to',$userId)
            ->get();
    }

    public function getLimitOfTasksForUser($userId){
        return $this->model->selectRaw('distinct task_management.*,task_statuses.task_status_name_ar,task_statuses.task_status_name_en,
        (CASE WHEN (task_status_id != ? AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE()) THEN 1 ELSE 0 END) as IsDelayed',array(config('taskStatuses.done')))
            ->join('task_statuses', 'task_statuses.id', 'task_management.task_status_id')
            ->where('assigned_to',$userId)
            ->limit(config('committeeDashboard.maxTasksNumberForMemberDashboard'))->orderBy('task_management.id','desc')->get();
    }

    public function getLimitOfTasksForOrganization($organizationId){
        return $this->model->selectRaw('distinct task_management.*,task_statuses.task_status_name_ar,task_statuses.task_status_name_en,
        (CASE WHEN (task_status_id != ? AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE()) THEN 1 ELSE 0 END) as IsDelayed',array(config('taskStatuses.done')))
            ->join('task_statuses', 'task_statuses.id', 'task_management.task_status_id')
            ->whereRaw('(task_management.organization_id = '.$organizationId.' AND task_management.deleted_at  IS NULL)')
            ->limit(config('committeeDashboard.maxTasksNumberForBoardDashboard'))->orderBy('task_management.id','desc')->get();
    }

    public function getLimitOfTasksForCommittee($committee_id){
        return $this->model->selectRaw('distinct task_management.*,task_statuses.task_status_name_ar,task_statuses.task_status_name_en,
        (CASE WHEN (task_status_id != ? AND DATE_ADD(start_date, INTERVAL number_of_days DAY) < CURRENT_DATE()) THEN 1 ELSE 0 END) as IsDelayed',array(config('taskStatuses.done')))
            ->join('task_statuses', 'task_statuses.id', 'task_management.task_status_id')
            ->whereRaw('(task_management.committee_id = '.$committee_id.' AND task_management.deleted_at  IS NULL)')
            ->limit(config('committeeDashboard.maxTasksNumberForCommitteeDashboard'))->orderBy('task_management.id','desc')->get();
    }
}
