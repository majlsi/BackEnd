<?php

namespace Repositories;

class DocumentUserRepository extends BaseRepository {


    public function model() {
        return 'Models\DocumentUser';
    }

    public function getDocumentUserByDocumentAndUserId($documentId,$userId){
        return $this->model->select('*')
            ->where('user_id',$userId)
            ->where('document_id',$documentId)
            ->first();
    }

    public function getDocumentUsersWithDelayedDate(){
        return $this->model->select('document_users.id')->whereNotIn('document_users.document_status_id',[config('documentStatuses.complete'),config('documentStatuses.delay')])
            ->join('documents','documents.id','document_users.document_id')
            ->join('organizations','organizations.id','documents.organization_id')
            ->join('time_zones','time_zones.id','organizations.time_zone_id')
            ->where('documents.document_status_id','!=',config('documentStatuses.complete'))
            ->whereRaw('DATE_ADD(documents.review_end_date, INTERVAL (time_zones.diff_hours * -1) HOUR)  < UTC_TIMESTAMP()')
            ->get();
    }

    public function updateDocumentUsersStatusToDelay($documentsUsersIds){
        $this->model->whereIn('document_users.id',$documentsUsersIds)
            ->update(['document_users.document_status_id' => config('documentStatuses.delay')]);
    }
}
