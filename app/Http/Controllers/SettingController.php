<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\SettingService;
use Validator;
use Models\Setting;

class SettingController extends Controller {

    private $settingService;


    public function __construct(SettingService $settingService) {
        $this->settingService = $settingService;
    }

    public function index(){
        return response()->json($this->settingService->getAll(),200);
    }

    public function updateSettings(Request $request) {
        $data = $request->all();
        $message = [];

        foreach( $data as $setting){
            $validator = Validator::make($setting,Setting::rules('update'));

            if($validator->fails()){
                $message = array_merge($message,$validator->errors()->all());
            }
        }

        if(!empty($message)){
            return response()->json(['error' => $message],400);
        }

        $settings = $this->settingService->updateSettings($data);
        
        return response()->json($settings,200); 
    }
    
    public function getIntroductionVideoUrl(){
        $settings = $this->settingService->getById(config('settings.introductionVideoUrl'));
        return response()->json($settings,200); 
    }

    public function getSupportEmail(){
        $settings = $this->settingService->getById(config('settings.supportEmail'));
        return response()->json($settings,200); 
    }

}
