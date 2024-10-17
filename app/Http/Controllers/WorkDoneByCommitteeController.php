<?php

namespace App\Http\Controllers;

use Helpers\WorkDoneByCommitteeHelper;
use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Models\WorksDoneByCommittee;
use Services\WorkDoneByCommitteeService;
use Illuminate\Support\Facades\Validator;

class WorkDoneByCommitteeController extends Controller
{

    private WorkDoneByCommitteeService $workDoneByCommitteeService;
    private WorkDoneByCommitteeHelper $workDoneByCommitteeHelper;
    private SecurityHelper $securityHelper;

    public function __construct(

        WorkDoneByCommitteeService $workDoneByCommitteeService,
        SecurityHelper $securityHelper,
        WorkDoneByCommitteeHelper $workDoneByCommitteeHelper,

    ) {
        $this->workDoneByCommitteeService = $workDoneByCommitteeService;
        $this->securityHelper = $securityHelper;
        $this->workDoneByCommitteeHelper = $workDoneByCommitteeHelper;
    }

    //! method: post  ==> /works-done
    // body-> (work_done, committee_id) 
    public function store(Request $request)
    {
        $data=$request->all();
        $errors = [];
        $validator = Validator::make($data, WorksDoneByCommittee::rules('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        $newWorkDoneByCommittee =  $this->workDoneByCommitteeService->create($data);

        return response()->json($newWorkDoneByCommittee, 200);


    }


    //! method: put  ==>   works-done/{id}
    // {id}-> works_done_id
    // body-> (work_done, committee_id) 
    public function update(Request $request,$id)
    {
        $data=$request->all();
        $errors = [];
        $validator = Validator::make($data, WorksDoneByCommittee::rules('update'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
       $this->workDoneByCommitteeService->update($id,$data);
       $updatedWorkDoneByCommittee = $this->workDoneByCommitteeService->getById($id); 
        return response()->json($updatedWorkDoneByCommittee, 200);


    }


    //! method: delete  ==>   works-done/{id}
    // {id}-> works_done_id
    public function destroy($id)
    {
        $workDoneByCommittee=$this->workDoneByCommitteeService->getById($id);
        if(!$workDoneByCommittee)
        {
            return response()->json(['error' => 'there is no work with this id', 'error_ar' => 'لا يوجد عمل '], 404);
        }
        $this->workDoneByCommitteeService->delete($id);
        return response()->json(['message' => 'data deleted successfully'], 200);

    }

    //! method: get  ==>   works-done/{committee_id}
    public function getAllWorksDoneByCommittee($committee_id)
    {
        $workDoneByCommittee=$this->workDoneByCommitteeService->getWorksDoneByCommitteeId($committee_id);
        return response()->json($workDoneByCommittee, 200);

    }

}
