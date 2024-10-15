<?php

namespace Repositories;

class ProposalRepository extends BaseRepository {

    /**
     * Determine the model of the repository
     *
     */
    public function model() {
        return 'Models\Proposal';
    }



    public function getOrganizationProposals($organizationId){
        return $this->model->where('organization_id',$organizationId)
                           ->get();
    }

    public function getPagedProposalsList($pageNumber, $pageSize,$searchObj,$sortBy,$sortDirection){
        $query = $this->getAllProposalsQuery($searchObj);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllProposalsQuery($searchObj){
        if (isset($searchObj->proposal_title)) {
            $this->model = $this->model->whereRaw("(proposal_title like ?)",array('%' . $searchObj->proposal_title . '%'));
        }
        if (isset($searchObj->proposal_text)) {
            $this->model = $this->model->whereRaw("(proposal_text like ?)",array('%' . $searchObj->proposal_text . '%'));
        }

        if (isset($searchObj->created_by)) {
            $this->model = $this->model->where('proposals.created_by',$searchObj->created_by);
        }
        if (isset($searchObj->organization_id)) {
            $this->model = $this->model->where('proposals.organization_id',$searchObj->organization_id);
        }

        $this->model = $this->model->selectRaw('proposals.*,users.name,users.name_ar')
        ->leftJoin('users','users.id','proposals.created_by')
        ->orderBy('proposals.created_at','desc')
        ;
        return $this->model;
    }
}
