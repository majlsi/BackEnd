<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Models\Committee;
use Illuminate\Support\Facades\DB;

class ExportBlacklistedUsers implements FromCollection,WithHeadings,WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;
    protected $lang;
    protected $organizationId;

    public function __construct($lang, $organizationId)
    {
        $this->lang = $lang;
        $this->organizationId = $organizationId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return app('Repositories\UserRepository')->getAllDeActiveAndBlockedUsers($this->organizationId)->load('role');

    }
    public  function map($user):array
    {
        $roleName = $this->lang === 'en' ? $user->role->role_name ??$user->role->role_name_ar : $user->role->role_name_ar??$user->role->role_name;
        $is_active = ($user->is_active === 1) ? __('excel.columns.active', [], $this->lang) : __('excel.columns.deActive', [], $this->lang);
        $is_blocked = ($user->is_blocked === 1) ? __('excel.columns.blocked', [], $this->lang) : __('excel.columns.unBlocked', [], $this->lang);
            return
            [
                $user->name,
                $user->name_ar,
                $user->email,
                $user->phone,
                $roleName,
                $is_active,
                $is_blocked,
                $user->blacklist_reason,
        
            ];
    }

    public function headings(): array
    {
        return [
            __('excel.columns.name', [], $this->lang),
            __('excel.columns.name_ar', [], $this->lang),
            __('excel.columns.email', [], $this->lang),
            __('excel.columns.phone', [], $this->lang),
            __('excel.columns.role', [], $this->lang),
            __('excel.columns.active', [], $this->lang),
            __('excel.columns.blocked', [], $this->lang),
            __('excel.columns.blacklist_reason', [], $this->lang),
        ];
    }
}
