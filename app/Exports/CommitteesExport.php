<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Models\Committee;
use Illuminate\Support\Facades\DB;

class CommitteesExport implements FromCollection,WithHeadings,WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;
    protected $lang;
    protected $organizationId;

    public function __construct($data,$lang, $organizationId)
    {
        $this->data = $data;
        $this->lang = $lang;
        $this->organizationId = $organizationId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if ($this->data === null) {
            // Export all committees
            return Committee::where('organization_id', $this->organizationId)
            ->whereRaw('(committees.committee_code NOT LIKE "' . config('committee.stakeholders') . '"
            or committees.committee_code is null )')
            ->with('committeeHead')
            ->with('committeeOrganiser')
            ->with('committeeResponsible')
            ->with('committeeStatus')
            ->with('committeeType')
            ->with(['memberUsers' => function ($query) {
                $query->selectRaw('users.*,committee_users.committee_user_start_date,
                committee_users.committee_user_expired_date');
            }])
            ->get();
        } else {
            if (isset($this->data->id)) {
                // Export a single committee
                return Committee::where('id', $this->data->id)
                    ->with('committeeHead')
                    ->with('committeeOrganiser')
                    ->with('committeeResponsible')
                    ->with('committeeStatus')
                    ->with('committeeType')
                    ->with(['memberUsers' => function ($query) {
                        $query->selectRaw('users.*,committee_users.committee_user_start_date,
                    committee_users.committee_user_expired_date');
                    }])
                    ->get();
            } elseif (isset($this->data->user_id)) {
                // Export my committees
                return Committee::where('organization_id', $this->organizationId)
                    ->whereRaw('(committees.committee_code NOT LIKE "' . config('committee.stakeholders') . '"
                    or committees.committee_code is null )')
                    ->with('committeeHead')
                    ->with('committeeOrganiser')
                    ->with('committeeResponsible')
                    ->with('committeeStatus')
                    ->with('committeeType')
                    ->with(['memberUsers' => function ($query) {
                        $query->selectRaw(
                            'users.*,committee_users.committee_user_start_date,
                            committee_users.committee_user_expired_date'
                        );
                    }])
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('committee_users')
                            ->whereRaw('committee_users.committee_id = committees.id')
                            ->where('committee_users.user_id', $this->data->user_id);
                    })
                    ->get();
            }
        }
    }
    public  function map($committee):array
    {

            return
            [
                $committee->id,
                $committee->committee_name_ar,
                $committee->committee_name_en,
                $committee->committeeHead?$committee->committeeHead->name_ar:'',
                $committee->committeeOrganiser?$committee->committeeOrganiser->name_ar:'',
                $committee->committeeResponsible?$committee->committeeResponsible->name_ar:'',
                $committee->committeee_members_count,
                $committee->committee_start_date,
                $committee->committee_expired_date,
                $committee->decision_number,
                $committee->decision_date,
                $committee->committee_reason,
                $committee->committeeStatus?$committee->committeeStatus->committee_status_name_ar:'',
                $committee->committeeType?$committee->committeeType->committee_type_name_ar:'',
                implode(', ', $committee->memberUsers->pluck('name_ar')->toArray()),
                implode(', ', $committee->worksDone->pluck('work_done')->toArray()),
                implode(', ', $committee->recommendations->pluck('recommendation_body')->toArray()),
            ];
    }

    public function headings(): array
    {
        return [
            'id',
            __('committee-excel.columns.committee_ar', [], $this->lang),
            __('committee-excel.columns.committee_en', [], $this->lang),
            __('committee-excel.columns.committee_head', [], $this->lang),
            __('committee-excel.columns.committee_organizer', [], $this->lang),
            __('committee-excel.columns.committee_responsible', [], $this->lang),
            __('committee-excel.columns.committee_members_count', [], $this->lang),
            __('committee-excel.columns.committee_start_date', [], $this->lang),
            __('committee-excel.columns.committee_expired_date', [], $this->lang),
            __('committee-excel.columns.decision_number', [], $this->lang),
            __('committee-excel.columns.decision_date', [], $this->lang),
            __('committee-excel.columns.committee_reason', [], $this->lang),
            __('committee-excel.columns.committee_Status', [], $this->lang),
            __('committee-excel.columns.committee_Type', [], $this->lang),
            __('committee-excel.columns.committee_members', [], $this->lang),
            __('committee-excel.columns.committee_work_done', [], $this->lang),
            __('committee-excel.columns.committee_recommendation', [], $this->lang),
            // 'Committee Arabic name',
            // 'committee Head',
            // 'committee Organizer',
            // 'committee Responsible',
            // 'committee members count',
            // 'committee start date',
            // 'committee expired date',
            // 'decision number',
            // 'decision date',
            // 'committee reason',
            // 'committee Status',
            // 'committee Type',
            // 'committee members'
        ];
    }
}
