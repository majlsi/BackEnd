<?php

namespace Repositories;

class FailedLoginAttemptRepository extends BaseRepository {


    public function model() {
        return 'Models\FailedLoginAttempt';
    }

    public function getCountOfFailedLoginAttepsByIP($clientIp){
        return $this->model
        ->where('failed_login_attempts.ip_address',$clientIp)
        ->whereRaw('DATE_SUB(UTC_TIMESTAMP(), INTERVAL ? MINUTE) <= failed_login_attempts.failed_login_date',array(config('login.duration_per_minute')))   
        ->count();
    }

    public function getPagedFailedAttemptsList($pageNumber, $pageSize,$searchObj,$sortBy,$sortDirection){
        $query = $this->getAllFailedAttemptsQuery($searchObj);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllFailedAttemptsQuery($searchObj){
        if (isset($searchObj->email_address)) {
            $this->model = $this->model->whereRaw("(email_address like ?)", array('%' . $searchObj->email_address . '%'));
        }
        if (isset($searchObj->ip_address)) {
            $this->model = $this->model->whereRaw("(ip_address like ?)", array('%' . $searchObj->ip_address . '%'));
        }
        if(isset($searchObj->user_name)) {
            $this->model = $this->model->whereRaw("(users.name like ? OR users.name_ar like ?)", array('%' . $searchObj->user_name . '%','%' . $searchObj->user_name . '%'));
        }

        $this->model = $this->model->selectRaw('failed_login_attempts.*,users.name,users.name_ar')
            ->leftJoin('users','users.id','failed_login_attempts.user_id');
        return $this->model;
    }
}
