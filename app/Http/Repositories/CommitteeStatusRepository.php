<?php

namespace Repositories;

class CommitteeStatusRepository extends BaseRepository
{


    public function model()
    {
        return 'Models\CommitteeStatus';
    }

    public function getcommitteesStatuses(int $organizationId)
    {
        return $this->model->selectRaw('committee_statuses.*, 
        COUNT(committees.id) as number_of_committees')
            ->leftJoin('committees', function ($join) use ($organizationId) {
                $join->on('committee_statuses.id', '=', 'committees.committee_status_id')
                    ->where('organization_id', $organizationId);
            })
            ->groupBy('committee_statuses.id')
            ->get();
    }
}
