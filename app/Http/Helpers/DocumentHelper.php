<?php

namespace Helpers;
use Carbon\Carbon;

class DocumentHelper
{

    public function __construct()
    {   

    }

    public function prepareDocumentData($data, $user, $isCreateNew)
    {
        $document = [];

        if(isset($data['document_subject_ar'])){
            $document['document_subject_ar'] = trim($data['document_subject_ar']);
        }
        if(isset($data['document_description_ar'])){
            $document['document_description_ar'] = trim($data['document_description_ar']);
        }
        if(isset($data['document_url']) && ($isCreateNew || (isset($data['review_end_date'])&& $data['review_end_date'] >= Carbon::now()->setTime(0, 0, 0)))) {
            $document['document_url'] = $data['document_url'];
        }
        if(isset($data['document_name']) && ($isCreateNew || (isset($data['review_end_date'])&& $data['review_end_date'] >= Carbon::now()->setTime(0, 0, 0)))){
            $document['document_name'] = $data['document_name'];
        }
        if(isset($data['committee_id'])){
            $document['committee_id'] = $data['committee_id'];
        }
        if(isset($data['review_start_date'])){
            $document['review_start_date'] = $data['review_start_date'];
        }
        if(isset($data['review_end_date'])){
            $document['review_end_date'] = $data['review_end_date'];
        }
        if(isset($data['document_users_ids'])){
            $document['document_users_ids'] = $data['document_users_ids'];
        }

        if($isCreateNew){
            $document['added_by'] = $user->id;
            $document['organization_id'] = $user->organization_id;
            $document['document_status_id'] = config('documentStatuses.new');
        }

        return $document;
    }
}
