<?php

namespace Repositories;


class ModuleRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\Module';
    }

 

	public function getRoleRights($roleId)
    {
        return $this->model->select('module_name as title','module_name_ar as title_ar', 'icon','id')->with(['submenu' => function ($query) use ($roleId){
                $query->select('rights.*','rights.right_url as page' ,'rights.right_name as title','rights.right_name_ar as title_ar')
                ->join('role_rights', 'rights.id', '=', 'role_rights.right_id')
                ->where('rights.in_menu', '=', 1)
                ->where('role_rights.role_id', '=', $roleId)
                ->where('role_rights.deleted_at', '=', null)
				->orderby('rights.right_order_number', 'asc');

            }])->whereHas('submenu',function ($query) use ($roleId) {
                $query->join('role_rights', 'rights.id', '=', 'role_rights.right_id')
                ->where('role_rights.role_id', '=', $roleId)
                ->where('role_rights.deleted_at', '=', null)
                ->orderby('rights.right_order_number', 'asc');
            })
			->orderby('modules.menu_order', 'asc')
			->get();
    }

    public function getAvailableAllRights($rightTypeId){
        if($rightTypeId == config('rightTypes.forAdmin')){
            $query = $this->model->selectRaw('*')
            ->has('rights')
            ->with('rights');
			
        }else if($rightTypeId == config('rightTypes.forOrganizationAdmin')){
            $query = $this->model->selectRaw('*')
            ->with(['rights' => function ($query) {
                $query->whereRaw('(right_type_id = '.config('rightTypes.forOrganizationAdmin').' OR right_type_id IS NULL)')
               ->whereNull('rights.deleted_at');
            }])
            ->whereHas('rights',function ($query) {
                $query->whereRaw('(right_type_id = '.config('rightTypes.forOrganizationAdmin').' OR right_type_id IS NULL)')
               ->whereNull('rights.deleted_at');
            });
          
        }
        return $query = $query ->orderby('modules.menu_order', 'asc')->get();
        
    }

}