<?php

namespace App\Http\Controllers;

use Helpers\LdapHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Services\LdapService;
use Services\UserService;
use Illuminate\Support\Facades\Validator;
use Services\ChatService;
use Models\User;
class LdapUserController extends Controller {

    private $userService;
    private $ldapService;
    private $ldapHelper;
    private $chatService;

    public function __construct(LdapService $ldapService,UserService $userService, LdapHelper $ldapHelper,ChatService $chatService) {
        $this->ldapService = $ldapService;
        $this->userService = $userService;
        $this->ldapHelper = $ldapHelper;
        $this->chatService = $chatService;
    }

    public function getLdapUser(Request $request){
        $data = urldecode($request->input('userName'));
        try
        {
        $ldapUser = $this->ldapService->getLdapUser($data);
        if ($ldapUser){
            $user=$this->userService->getUserByEmail($ldapUser[0]['userprincipalname'][0]);
            if(!$user)
            {
                $ldapUserData=$this->ldapHelper->prepareLdapUserData($ldapUser);
                $validator = Validator::make($ldapUserData, User::rules('save'), User::messages('save'));
                if ($validator->fails()) {
                    return response()->json(['error' => array_values($validator->errors()->toArray())], 400);
                }
                $user = $this->userService->create(['user_data' => $ldapUserData, 'profile_image' => []]);
                $this->chatService->createChatUsers([$user]);
            }
            return response()->json($user, 200);
        } 
            return response()->json(['message' => "Can't find this User in our system.",'message_ar' => 'لم نستطع العثور على هذا العضو.'], 200);
        }catch(\Exception $e){
            Log::error('LDAP search error: ' . $e->getMessage());
            return response()->json(['error' => 'LDAP search error'], 500);
        }
    }


    public function getLdapIntegrationFeatureVariable()
    {
        $variableValue = config('customSetting.ldapIntegration');
        return response()->json(['ldapIntegration' => $variableValue], 200);
    }
}