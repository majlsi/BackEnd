<?php

namespace Helpers;
use Carbon\Carbon;

class DocumentAnnotationHelper
{

    public function __construct()
    {   

    }

    public function prepareDocumentAnnotationData($data,$user, $document, $isCreateNew)
    {
        $documentAnnotation = [];

        if(isset($data['page_number'])){
            $documentAnnotation['page_number'] = $data['page_number'];
        }
        if(isset($data['annotation_text'])){
            $documentAnnotation['annotation_text'] = trim($data['annotation_text']);
        }
        if(isset($data['x_upper_left'])){
            $documentAnnotation['x_upper_left'] = $data['x_upper_left'];
        }
        if(isset($data['y_upper_left'])){
            $documentAnnotation['y_upper_left'] = $data['y_upper_left'];
        }

        if($isCreateNew){
            $documentUser = $document->documentUsers->where('user_id',$user->id)->first();
            $documentAnnotation['document_user_id'] = $documentUser->id;
            $documentAnnotation['creation_date'] = Carbon::now();
        }

        return $documentAnnotation;
    }
}
