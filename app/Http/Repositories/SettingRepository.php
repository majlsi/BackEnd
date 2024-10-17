<?php

namespace Repositories;

class SettingRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\Setting';
    }

    public function getValueByKey(string $key){
        return $this->model->where('setting_key' , $key)->first();
    }

}
