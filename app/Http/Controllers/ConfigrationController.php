<?php

namespace App\Http\Controllers;

use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Models\Configration;
use Services\ConfigrationService;
use Validator;

class ConfigrationController extends Controller
{

    private $configrationService;
    private $securityHelper;

    public function __construct(ConfigrationService $configrationService, SecurityHelper $securityHelper)
    {
        $this->configrationService = $configrationService;
        $this->securityHelper = $securityHelper;
    }

    public function index()
    {
        return response()->json($this->configrationService->getAll(), 200);
    }

    public function getFirstConfigration($id)
    {
        $configrations =$this->configrationService->getById($id);

        foreach ($configrations->toArray() as $key => $value) {
            if (in_array($key, [config('configColumns.8'),config('configColumns.9'),config('configColumns.10')])) {
             
                $result = explode('/',$value);
                $nameWithoutTime = explode('_', end($result), 2);
                if (count($nameWithoutTime)> 1) {
                    $result[count($result) - 1] = $nameWithoutTime[1];
                }
                $configrations[$key] = implode('/', $result);
            }
        }
       
        return response()->json($configrations , 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if ($user->role_id == config('roles.admin')) {
            $validator = Validator::make($data, Configration::rules('update', $id));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $this->configrationService->update($id, $data);
            return response()->json(['message' => ['Configration updated successfully.']], 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getConfigColumn($column)
    {
        $configration = $this->configrationService->getById(1, [config('configColumns.' . $column)]);

        if (in_array($column, [8, 9, 10])) {
            $result = explode('/', $configration[config('configColumns.' . $column)]);
            $nameWithoutTime = explode('_', end($result), 2);
            if (count($nameWithoutTime)> 1) {
                $result[count($result) - 1] = $nameWithoutTime[1];
            }
            $configration['name'] = implode('/', $result);
        }
        return response()->json($configration, 200);
    }
}
