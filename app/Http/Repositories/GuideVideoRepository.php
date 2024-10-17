<?php

namespace Repositories;

class GuideVideoRepository extends BaseRepository {

    public function model() {
        return 'Models\GuideVideo';
    }

    public function getPagedFaqList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection)
    {
        $query = $this->getAllVideosQuery($searchObj);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllVideosQuery($searchObj)
    {
        if (isset($searchObj->video_name_ar)) {
            $this->model = $this->model->whereRaw("(video_name_ar like ?)", array('%' . $searchObj->video_name_ar . '%'));
        }
        if (isset($searchObj->video_name_en)) {
            $this->model = $this->model->whereRaw("(video_name_en like ?)", array('%' . $searchObj->video_name_en . '%'));
        }
        if (isset($searchObj->video_url)) {
            $this->model = $this->model->whereRaw("(video_url like ?)", array('%' . $searchObj->video_url . '%'));
        }

        $this->model = $this->model->selectRaw('*')->with('videoIcon');
        return $this->model;
    }

    public function getLastVideo(){
        return $this->model
            ->orderBy('id', 'desc')
            ->first();
    }
}   