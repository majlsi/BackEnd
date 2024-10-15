<?php

namespace Services;

use Repositories\StakeholderRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Repositories\UserRepository;
use stdClass;

class StakeholderService extends BaseService
{
    public function __construct(
        DatabaseManager $database,
        StakeholderRepository $repository,
        UserRepository $userRepository
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->userRepository = $userRepository;
    }
    private $userRepository;

    public function prepareCreate(array $data)
    {
        $stakeholderData = $data['stakeholder_data'];
        return $this->repository->create($stakeholderData);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function filteredStakeholders($filter, $roleId = null, $organizationId = null)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }

        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "ASC";
        }

        return $this->repository->filteredStakeholders($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $roleId, $organizationId);
    }

    public function activateDeactivateStakeHolder($stakeholderId, $isActive)
    {
        $this->repository->update(['is_active' => $isActive], $stakeholderId);
    }

    public function getStakeholderById($id)
    {
        return $this->repository->getStakeholderById($id);
    }

    public function getTotalShares($organizationId)
    {
        $usersIds = $this->userRepository->findWhere(['organization_id'=> $organizationId], ['id'])->pluck('id')->toArray();
        return $this->repository->getTotalShares($usersIds);
    }

    public function getStakeholdersInUsersIds($ids)
    {
        return $this->repository->getStakeholdersInUsersIds($ids);
    }
    public function validateStakeholdersFromExcel($stakeholders, $organizationId, $lang)
    {
        $data = [];
        $total_share = $this->getTotalShares($organizationId)->total_share ?? 0;
        $organizationUsers = $this->userRepository->findWhere(['deleted_at' => Null], ['email'])->toArray();
        foreach ($stakeholders as &$stakeholder) {
            $s = [
                'name' => $stakeholder[0],
                'email' => $stakeholder[1],
                'date_of_birth' => $stakeholder[2],
                'identity_number' => $stakeholder[3],
                'share' => $stakeholder[4],
                'status' => true,
                'errorMessage' => ''
            ];
            $s = $this->validateStakeholderFromExcel($s, $lang, $organizationUsers);
            $s = $this->validateEmailIsDublicate($s, $stakeholders, $lang);
            $s = $this->validateShare($stakeholders, $total_share, $s, $lang);
            array_push($data, $s);
        }
        return $data;
    }

    private function validateEmailIsDublicate($s, $stakeholders, $lang)
    {
        $occurences = 0;
        if (isset($s['email'])) {
            foreach ($stakeholders as $stakeholder) {
                if ($stakeholder[1] == $s['email']) {
                    $occurences++;
                }
            }
            if ($occurences != 1) {
                $stakeholder['errorMessage'] =
                    $lang == config('languages.ar') ?
                    Lang::get('validation.custom.email.unique', [], 'ar') :
                    Lang::get('validation.custom.email.unique', [], 'en');
                $s['status'] = false;
            }
        }
        return $s;
    }

    private function validateShare($stakeholders, $share, $s, $lang)
    {
        foreach ($stakeholders as $stakeholder) {
            if (isset($stakeholder[4])) {
                $share += $stakeholder[4];
            }
        }
        if ($share <= 0 || $share > 100) {
            if ($share - $s['share'] >= 0 && $share - $s['share'] < 100) {
                $s['errorMessage'] =
                    $lang == config('languages.ar') ?
                    Lang::get('validation.custom.share.total_not_valid', [], 'ar') :
                    Lang::get('validation.custom.share.total_not_valid', [], 'en');
                $s['status'] = false;
            }
        }
        return $s;
    }
    private function validateStakeholderFromExcel($stakeholder, $lang, $organizationUsers)
    {
        // name is required
        if (!isset($stakeholder['name'])) {
            $stakeholder['errorMessage'] =
                $lang == config('languages.ar') ?
                Lang::get('validation.custom.name.required', [], 'ar') :
                Lang::get('validation.custom.name.required', [], 'en');
            $stakeholder['status'] = false;
            return $stakeholder;
        }
        // email is required
        if (!isset($stakeholder['email'])) {
            $stakeholder['errorMessage'] =
                $lang == config('languages.ar') ?
                Lang::get('validation.custom.email.required', [], 'ar') :
                Lang::get('validation.custom.email.required', [], 'en');
            $stakeholder['status'] = false;
            return $stakeholder;
        } else {
            if (!filter_var($stakeholder['email'], FILTER_VALIDATE_EMAIL)) {
                $stakeholder['errorMessage'] =
                    $lang == config('languages.ar') ?
                    Lang::get('validation.custom.email.email', [], 'ar') :
                    Lang::get('validation.custom.email.email', [], 'en');
                $stakeholder['status'] = false;
                return $stakeholder;
            }
            // check email exists in database
            $users = array_filter($organizationUsers, function ($user) use ($stakeholder) {
                return $user['email'] == $stakeholder['email'];
            });
            if (count($users) > 0) {
                $stakeholder['errorMessage'] =
                    $lang == config('languages.ar') ?
                    Lang::get('validation.custom.email.unique', [], 'ar') :
                    Lang::get('validation.custom.email.unique', [], 'en');
                $stakeholder['status'] = false;
                return $stakeholder;
            }
        }
        // date of birth is required
        if (!isset($stakeholder['date_of_birth'])) {
            $stakeholder['errorMessage'] =
                $lang == config('languages.ar') ?
                Lang::get('validation.custom.date_of_birth.required', [], 'ar') :
                Lang::get('validation.custom.date_of_birth.required', [], 'en');
            $stakeholder['status'] = false;
            return $stakeholder;
        } else {
            $date_arr = explode('/', $stakeholder['date_of_birth']);
            if(count($date_arr) != 3 || !checkdate($date_arr[1], $date_arr[0], $date_arr[2])) {
                $stakeholder['errorMessage'] =
                    $lang == config('languages.ar') ?
                    Lang::get('validation.custom.date_of_birth.date', [], 'ar') :
                    Lang::get('validation.custom.date_of_birth.date', [], 'en');
                $stakeholder['status'] = false;
                return $stakeholder;
            }
        }
        // Identity number is required
        if (!isset($stakeholder['identity_number'])) {
            $stakeholder['errorMessage'] =
                $lang == config('languages.ar') ?
                Lang::get('validation.custom.identity_number.required', [], 'ar') :
                Lang::get('validation.custom.identity_number.required', [], 'en');
            $stakeholder['status'] = false;
            return $stakeholder;
        }

        // Share must be between 0 and 100
        if (!isset($stakeholder['share'])) {
            $stakeholder['errorMessage'] =
                $lang == config('languages.ar') ?
                Lang::get('validation.custom.share.required', [], 'ar') :
                Lang::get('validation.custom.share.required', [], 'en');
            $stakeholder['status'] = false;
            return $stakeholder;
        } else {
            if ($stakeholder['share'] < 0) {
                $stakeholder['errorMessage'] =
                    $lang == config('languages.ar') ?
                    Lang::get('validation.custom.share.min', [], 'ar') :
                    Lang::get('validation.custom.share.min', [], 'en');
                $stakeholder['status'] = false;
                return $stakeholder;
            } elseif ($stakeholder['share'] > 100) {
                $stakeholder['errorMessage'] =
                    $lang == config('languages.ar') ?
                    Lang::get('validation.custom.share.max', [], 'ar') :
                    Lang::get('validation.custom.share.max', [], 'en');
                $stakeholder['status'] = false;
                return $stakeholder;
            }
        }

        return $stakeholder;
    }

    public function validateColumns($columns, $lang)
    {
        $error= [];
        if (
            !in_array(__('excel.columns.name', [], $lang), $columns) ||
            !in_array(__('excel.columns.email', [], $lang), $columns) ||
            !in_array(__('excel.columns.date_of_birth', [], $lang), $columns) ||
            !in_array(__('excel.columns.identity_number', [], $lang), $columns) ||
            !in_array(__('excel.columns.share', [], $lang), $columns)
        ) {
            $error = ['error' => 'Invalid Template', 'error_ar' => 'ملف غير صالح'];
        }
        return $error;
    }
    public function validateTotalShare($currentValue, $prevValue, $organizationId)
    {
        $totalShares = $this->getTotalShares($organizationId);
        $totalShares = $totalShares->total_share;
        $totalShares += $currentValue - $prevValue;
        return $totalShares <= 100.0 && $totalShares >= 0.0;
    }

    public function getMeetingParticipantsShare($meetingId)
    {
        return $this->repository->getMeetingParticipantsShare($meetingId);
    }

    public function getMeetingAttendanceShare($meetingId)
    {
        return $this->repository->getMeetingAttendanceShare($meetingId);
    }
}
