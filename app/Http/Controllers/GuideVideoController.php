<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\GuideVideoService;
use Models\GuideVideo;
use Helpers\SecurityHelper;
use Helpers\GuideVideoHelper;
use Validator;

class GuideVideoController extends Controller {

    private $guideVideoService;
    private $securityHelper;
    private $guideVideoHelper;

    public function __construct(GuideVideoService $guideVideoService, SecurityHelper $securityHelper,
        GuideVideoHelper $guideVideoHelper) {
        $this->guideVideoService = $guideVideoService;
        $this->securityHelper = $securityHelper;
        $this->guideVideoHelper = $guideVideoHelper;
    }

    public function index(){
        $guideVideos = $this->guideVideoService->getAll()->load('videoIcon');
        $guideVideos = $this->guideVideoHelper->getGuideVideosWithTutorialSteps($guideVideos);
        return response()->json($guideVideos, 200);
    }

    public function show($id){
        return response()->json($this->guideVideoService->getById($id), 200);
    }

    public function store(Request $request) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        $validator = Validator::make($data, GuideVideo::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        return response()->json($this->guideVideoService->create($data), 200);
    }

    public function update(Request $request, $id) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $video = $this->guideVideoService->getById($id);
        
        $validator = Validator::make($data, GuideVideo::rules('update'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $this->guideVideoService->update($id,$data);
        return response()->json(['message' => 'Video duide updated successfully'], 200);
    }  
    
    public function destroy($id) {
        try{
            $user = $this->securityHelper->getCurrentUser();
            $this->guideVideoService->delete($id);
            return response()->json(['message' => "success"], 200);
        }catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this video!",
            'error_ar' => "لا يمكن حذف هذه الفيديو"], 400);
        }
    }

    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->guideVideoService->getPagedList($filter),200);
    }

    public function getTutorialStepsList(){
        return response()->json(array_values(config('tutorialSteps')), 200);
    }
}