<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportAddCommitteeRequests implements FromCollection,WithHeadings,WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $data;
    protected $requestTypeId;
    protected $lang;
    protected $organizationId;

    public function __construct( $organizationId,$lang,$requestTypeId)
    {
        $this->lang = $lang;
        $this->requestTypeId = $requestTypeId;
        $this->organizationId = $organizationId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return app('Repositories\RequestRepository')->getCommitteeRequestPagesQueryForExcel($this->organizationId, $this->requestTypeId);

    }
    public function headings(): array
    {
        return [
            __('requests-excel.columns.committee_ar', [], $this->lang),
            __('requests-excel.columns.committee_en', [], $this->lang),
            __('requests-excel.columns.committee_head', [], $this->lang),
            __('requests-excel.columns.committee_organizer', [], $this->lang),
            __('requests-excel.columns.committee_responsible', [], $this->lang),
            __('requests-excel.columns.decision_number', [], $this->lang),
            __('requests-excel.columns.decision_date', [], $this->lang),
            __('requests-excel.columns.committee_reason', [], $this->lang),

            __('requests-excel.columns.committee_Status', [], $this->lang),
            __('requests-excel.columns.committee_Type', [], $this->lang),
            __('requests-excel.columns.committee_start_date', [], $this->lang),
            __('requests-excel.columns.committee_expired_date', [], $this->lang),
            __('requests-excel.columns.request_status', [], $this->lang),
        ];
    }

    public function map($row): array
    {
        $approvalStatus = ($row->is_approved === 1) ? 'approve' : (($row->is_approved === 0) ? 'reject' : 'new');
        return [
            $row->request_body['committee_name_ar'] ?? null,
            $row->request_body['committee_name_en'] ?? null,
            $row->request_body['committee_head_name'] ?? null,
            $row->request_body['committee_organiser_name'] ?? null,
            $row->request_body['committee_responsible_name'] ?? null,
            $row->request_body['decision_number'] ?? null,
            $row->request_body['decision_date'] ?? null,
            $row->request_body['committee_reason'] ?? null,
            $row->request_body['committee_status_name_' . $this->lang] ?? null,
            $row->request_body['committee_type_name_' . $this->lang] ?? null,
            $row->request_body['committee_start_date'] ?? null,
            $row->request_body['committee_expired_date'] ?? null,
            $approvalStatus
        ];
    }
}
