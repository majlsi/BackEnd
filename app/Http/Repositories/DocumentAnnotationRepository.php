<?php

namespace Repositories;

class DocumentAnnotationRepository extends BaseRepository {


    public function model() {
        return 'Models\DocumentAnnotation';
    }

    public function getDocumentAnnotationByDocumentId($documentId,$userId){
        return $this->model->selectRaw('document_annotations.*,users.name,users.name_ar,CASE WHEN (document_users.user_id = '.$userId.') THEN 1 ELSE 0 END AS can_edit,document_users.user_id,
            images.image_url AS user_image_url')
            ->join('document_users','document_users.id','document_annotations.document_user_id')
            ->join('users','users.id','document_users.user_id')
            ->leftJoin('images','images.id','users.profile_image_id')
            ->where('document_users.document_id',$documentId)
            ->orderBy('page_number')
            ->get();
    }
}
