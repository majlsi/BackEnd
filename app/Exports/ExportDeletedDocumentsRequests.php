<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExportDeletedDocumentsRequests implements FromCollection,WithHeadings,WithMapping
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
            __('requests-excel.columns.file_name', [], $this->lang),
            __('requests-excel.columns.file_name_ar', [], $this->lang),

            __('requests-excel.columns.delete_reason', [], $this->lang),
            __('requests-excel.columns.request_status', [], $this->lang),
        ];
    }

    public function map($row): array
    {
        $approvalStatus = ($row->is_approved === 1) ? 'approve' : (($row->is_approved === 0) ? 'reject' : 'new');
        return [
            $row->request_body['committee_name_ar'] ?? null,
            $row->request_body['committee_name_en'] ?? null,
            $row->request_body['file']['file_name'] ?? null,
            $row->request_body['file']['file_name_ar'] ?? null,
            $row->request_body['reason'] ?? null,

            $approvalStatus
        ];
    }
}
