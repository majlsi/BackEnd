<?php

namespace Services;

use Repositories\SettingRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class SettingService extends BaseService
{

    public function __construct(DatabaseManager $database, SettingRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }

    public function prepareCreate(array $data)
    {
        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function updateSettings($settings){
        $this->openTransaction();

        foreach($settings as $setting){
            unset($setting['setting_key']);
            unset($setting['setting_key_ar']);
            $this->repository->update($setting,$setting['id']);    
        }

        $this->closeTransaction(); 
        return $this->repository->all();
    }

    public function getKeyValue($key){
        return $this->repository->getValueByKey($key);
    }

}
