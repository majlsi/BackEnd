<?php

namespace Repositories;

class DocumentRepository extends BaseRepository {


    public function model() {
        return 'Models\Document';
    }

    public function getDocumentsPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId,$userId){
        $query = $this->getDocumentsQuery($searchObj,$organizationId,$userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    private function getDocumentsQuery($searchObj,$organizationId,$userId)
    {
        if(isset($searchObj->document_status_id)){
            $this->model = $this->model->whereRaw('CASE WHEN (documents.document_status_id = '.config('documentStatuses.complete').' || added_by = '.$userId.') THEN documents.document_status_id = ' . $searchObj->document_status_id .' ELSE document_users.document_status_id = ' .$searchObj->document_status_id .' END');
        }
        if(isset($searchObj->committee_id)){
            $this->model = $this->model->where('documents.committee_id',$searchObj->committee_id);
        }
        if(isset($searchObj->review_start_date)){
            $this->model = $this->model->where('documents.review_start_date','>=',$searchObj->review_start_date);
        }
        if(isset($searchObj->review_end_date)){
            $this->model = $this->model->where('documents.review_end_date','<=',$searchObj->review_end_date);
        }
        if(isset($searchObj->is_my_document)){
            $this->model = $this->model->where('documents.added_by',$userId);
        } else if(isset($searchObj->is_document_assign_to_me)){
            $this->model = $this->model->where('document_users.user_id',$userId)->where('documents.added_by','!=',$userId);
        } else {
            $this->model = $this->model->whereRaw('(documents.added_by = '.$userId . ' Or document_users.user_id = '.$userId. ')');
        }

        return $this->model->selectRaw('DISTINCT documents.id,documents.added_by,documents.organization_id,documents.document_subject_ar,
                documents.document_description_ar,documents.document_url,documents.document_name,documents.committee_id,documents.review_start_date,documents.review_end_date,
                CASE WHEN (documents.document_status_id = '.config('documentStatuses.complete').' || added_by = '.$userId.') THEN document_statuses.document_status_name_ar ELSE document_user_statuses.document_status_name_ar END AS document_status_name_ar,
                CASE WHEN (documents.document_status_id = '.config('documentStatuses.complete').' || added_by = '.$userId.') THEN document_statuses.document_status_name_en ELSE document_user_statuses.document_status_name_en END AS document_status_name_en,
                users.name_ar AS creator_name_ar,users.name AS creator_name,
                CASE WHEN (added_by = '.$userId.') THEN 1 ELSE 0 END AS can_edit,
                CASE WHEN (documents.document_status_id = '.config('documentStatuses.complete').') THEN 1 ELSE 0 END AS is_completed,
                CASE WHEN ('.$userId.' IN (SELECT document_users.user_id FROM document_users WHERE document_users.document_id = documents.id) AND (DATE_ADD(documents.review_start_date, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP())) THEN 1 ELSE 0 END AS can_add_review,
                CASE WHEN (documents.document_status_id = '.config('documentStatuses.complete').' || added_by = '.$userId.') THEN documents.document_status_id ELSE document_users.document_status_id END AS document_status_id')
            ->join('document_statuses','document_statuses.id','documents.document_status_id')
            ->join('users','users.id','documents.added_by')
            ->join('document_users','document_users.document_id','documents.id')
            ->join('organizations','organizations.id','documents.organization_id')
            ->join('time_zones','time_zones.id','organizations.time_zone_id')
            ->join('document_statuses As document_user_statuses','document_user_statuses.id','document_users.document_status_id')
            ->whereRaw('CASE WHEN (documents.document_status_id = '. config('documentStatuses.new') .' AND documents.added_by != '.$userId.') THEN (DATE_ADD(documents.review_start_date, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) ELSE 1 END')
            ->where('documents.organization_id',$organizationId);
    }

    public function updateStatusOfDocumentToDelay(){
        $this->model->whereNotIn('documents.document_status_id',[config('documentStatuses.complete'),config('documentStatuses.delay')])
            ->whereRaw('documents.review_end_date < UTC_TIMESTAMP()')
            ->update(['documents.document_status_id' => config('documentStatuses.delay')]);
    }

    public function getDocumentDetails($id,$userId){
        return $this->model->selectRaw('documents.id,documents.added_by,documents.organization_id,documents.document_subject_ar,
            documents.document_description_ar,documents.document_url,documents.document_name,documents.committee_id,documents.review_start_date,documents.review_end_date,
            CASE WHEN (added_by = '.$userId.') THEN documents.document_status_id ELSE document_users.document_status_id END AS document_status_id,
            CASE WHEN (documents.document_status_id = '.config('documentStatuses.complete').') THEN 1 ELSE 0 END AS is_completed')
            ->join('document_users','document_users.document_id','documents.id')
            ->where('documents.id',$id)
            ->where('document_users.user_id',$userId)
            ->first();
    }

    public function getAllDocumentsWithReviewEndDateLessCurrentDate(){
        return $this->model->selectRaw('DISTINCT documents.*')
            ->join('organizations','organizations.id','documents.organization_id')
            ->join('time_zones','time_zones.id','organizations.time_zone_id')
            ->where('documents.document_status_id','!=',config('documentStatuses.complete'))
            ->whereRaw('DATE_ADD(documents.review_end_date, INTERVAL (time_zones.diff_hours * -1) HOUR) < UTC_TIMESTAMP()')
            ->get();
    }

    public function getDocumentUsersHaveStatusNotNew($documentId,$ussersIds) {
        return $this->model
            ->join('document_users','document_users.document_id','documents.id')
            ->where('documents.id',$documentId)
            ->whereIn('document_users.user_id',$ussersIds)
            ->where('document_users.document_status_id','!=',config('documentStatuses.new'))
            ->count('document_users.id');
    }

    public function getStartedDocuments(){
        return $this->model->selectRaw('documents.*')
            ->join('users','users.id','documents.added_by')
            ->join('organizations','organizations.id','users.organization_id')
            ->join('time_zones','time_zones.id','organizations.time_zone_id')
            ->whereRaw('(DATE_ADD(documents.review_start_date, INTERVAL (time_zones.diff_hours * -1) HOUR) = DATE_SUB(UTC_TIMESTAMP(), INTERVAL SECOND(UTC_TIMESTAMP()) SECOND))')
            ->get();
    }

    public function getDocumentDataWithCanSendNotificationFlag($documentId){
        return $this->model->selectRaw('documents.*,
            CASE WHEN (DATE_ADD(documents.review_start_date, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) THEN 1 ELSE 0 END AS can_send_notification')
            ->join('users','users.id','documents.added_by')
            ->join('organizations','organizations.id','users.organization_id')
            ->join('time_zones','time_zones.id','organizations.time_zone_id')
            ->where('documents.id',$documentId)
            ->first();
    }

    public function getDocumentsResultStatusStatisticsForUser($userId,$documentStatusId){
        return $this->model->selectRaw('distinct documents.*')
            ->join('document_users','document_users.document_id','documents.id')
            ->whereRaw('(documents.added_by = '.$userId . ' OR document_users.user_id = '.$userId. ')')
            ->whereRaw('document_users.document_status_id = ' .$documentStatusId)
            ->get();
    }


    public function getDocumentsResultStatusStatisticsForOrganization($organizationId,$documentStatusId){
        return $this->model->selectRaw('distinct documents.*')
            ->whereRaw('(documents.organization_id = '.$organizationId. ')')
            ->whereRaw('documents.document_status_id = ' .$documentStatusId)
            ->get();
    }

    public function getDocumentsResultStatusStatisticsForCommittee($committeeId,$documentStatusId){
        return $this->model->selectRaw('distinct documents.*')
            ->whereRaw('(documents.committee_id = '.$committeeId. ')')
            ->whereRaw('documents.document_status_id = ' .$documentStatusId)
            ->get();
    }

    public function getLimitOfDocumentsForUser($userId){
        return $this->model->selectRaw('distinct documents.id,documents.added_by,documents.organization_id,documents.document_subject_ar,
            documents.document_description_ar,documents.document_url,documents.document_name,documents.committee_id,documents.review_start_date,documents.review_end_date,users.name_ar AS creator_name_ar,users.name AS creator_name , document_users.document_status_id ,document_statuses.document_status_name_ar , document_statuses.document_status_name_en')
            ->join('users','users.id','documents.added_by')
            ->join('document_users','document_users.document_id','documents.id')
            ->join('document_statuses','document_statuses.id','document_users.document_status_id')
            ->whereRaw('(documents.added_by = '.$userId . ' OR document_users.user_id = '.$userId. ')')
            ->limit(config('committeeDashboard.maxDocumentsNumberForMemberDashboard'))->orderBy('documents.id','desc')->get();
    }

    public function getLimitOfDocumentsForOrganization($organizationId){
        return $this->model->selectRaw('distinct documents.id,documents.added_by,documents.organization_id,documents.document_subject_ar,
            documents.document_description_ar,documents.document_url,documents.document_name,documents.committee_id,documents.review_start_date,documents.review_end_date,users.name_ar AS creator_name_ar,users.name AS creator_name , documents.document_status_id ,document_statuses.document_status_name_ar , document_statuses.document_status_name_en')
            ->join('users','users.id','documents.added_by')
            ->join('document_statuses','document_statuses.id','documents.document_status_id')
            ->whereRaw('(documents.organization_id = '.$organizationId .')')
            ->limit(config('committeeDashboard.maxDocumentsNumberForBoardDashboard'))->orderBy('documents.id','desc')->get();
    }

    public function getLimitOfDocumentsForCommittee($committeeId){
        return $this->model->selectRaw('distinct documents.id,documents.added_by,documents.organization_id,documents.document_subject_ar,
            documents.document_description_ar,documents.document_url,documents.document_name,documents.committee_id,documents.review_start_date,documents.review_end_date,users.name_ar AS creator_name_ar,users.name AS creator_name , documents.document_status_id ,document_statuses.document_status_name_ar , document_statuses.document_status_name_en')
            ->join('users','users.id','documents.added_by')
            ->join('document_statuses','document_statuses.id','documents.document_status_id')
            ->whereRaw('(documents.committee_id = '.$committeeId .')')
            ->limit(config('committeeDashboard.maxDocumentsNumberForCommitteeDashboard'))->orderBy('documents.id','desc')->get();
    }
}
