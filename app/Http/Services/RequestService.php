<?php

namespace Services;

use Helpers\SecurityHelper;
use Helpers\StorageHelper;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Models\Request;
use Repositories\FileRepository;
use Repositories\OrganizationRepository;
use Repositories\RequestRepository;
use stdClass;


class RequestService extends BaseService
{
    private SecurityHelper $securityHelper; 
    private OrganizationRepository $organizationRepository; 
    private StorageHelper $storageHelper;
    private FileRepository $fileRepository;
    public function __construct(DatabaseManager $database, RequestRepository $repository,SecurityHelper $securityHelper,OrganizationRepository $organizationRepository,StorageHelper $storageHelper,FileRepository $fileRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->securityHelper = $securityHelper;
        $this->storageHelper = $storageHelper;
        $this->fileRepository=$fileRepository;
        $this->organizationRepository = $organizationRepository;
    }

    public function prepareCreate(array $data)
    {
       return $this->repository->create($data);
    }

    public function getPagedList($filter)
    {
        $user = $this->securityHelper->getCurrentUser();

        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "requests.id";
        } else if ($filter->SortBy == 'id') {
            $filter->SortBy = "requests.id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        if (!property_exists($filter, "PageNumber")) {
            $filter->PageNumber = "1";
        }
        if (!property_exists($filter, "PageSize")) {
            $filter->PageSize = "10";
        }
        return $this->repository->getRequestsPagedList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $user->organization_id, $user->id);
    }

  //! get all pending committees
  public function getPagedPendingList($filter,$user)
  {
      

      if (isset($filter->SearchObject)) {
          $params = (object) $filter->SearchObject;
      } else {
          $params = new stdClass();
      }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "requests.id";
        } else if ($filter->SortBy == 'id') {
            $filter->SortBy = "requests.id";
        } else if ($filter->SortBy == 'name') {
            $filter->SortBy = "requests.request_body->committee_head_name";
        } else if ($filter->SortBy == 'committee_name_en') {
            $filter->SortBy = "requests.request_body->committee_name_en";
        } else if ($filter->SortBy == 'committee_name_ar') {
            $filter->SortBy = "requests.request_body->committee_name_ar";
        } else if ($filter->SortBy == 'committee_type_name_ar') {
            $filter->SortBy = "requests.request_body->committee_type_name_ar";
        } else if ($filter->SortBy == 'committeee_members_count') {
            $filter->SortBy = "requests.request_body->committeee_members_count";
        }
      if (!property_exists($filter, "SortDirection")) {
          $filter->SortDirection = "DESC";
      }
      if (!property_exists($filter, "PageNumber")) {
          $filter->PageNumber = "1";
      }
      if (!property_exists($filter, "PageSize")) {
          $filter->PageSize = "10";
      }
      $pendingRequests= $this->repository->getRequestBodyPagedList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $user->organization_id, $user->id);
      $resultsArray = $pendingRequests->Results->toArray();
      foreach ($resultsArray as $key => $request) {
            $resultsArray[$key]['request_body']['request_id'] = $request['id'];
      }
      $requestBodies = array_column($resultsArray, 'request_body');
      $pendingRequests->Results = $requestBodies;
      return $pendingRequests;
  }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function addCommitteeRequest(array $data)
    {
        $this->prepareCreateCommitteeRequest($data);
        return $this->repository->create($data);
    }
    public function prepareCreateCommitteeRequest (array $data)
    {
        $organization = $this->organizationRepository->find($data['request_body']['organization_id'], array('*'));

        if (isset($data['governance_regulation_url'])) {
            // create file for goverance regulation
            $result = explode('/', $data['governance_regulation_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['governance_regulation_url'], 0, $organization->systemAdmin);
            $attachmentFile = $this->fileRepository->create($storageFile);
            $data['file_id']  =  $attachmentFile->id;
        }
        if (isset($data['decision_document_url'])) {
            // create file for decision document
            $result = explode('/', $data['decision_document_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['decision_document_url'], 0, $organization->systemAdmin);
            $attachmentFile = $this->fileRepository->create($storageFile);
            $data['decision_document_id']  =  $attachmentFile->id;
        }
    }

    public function addUserToCommitteeRequest($data)
    {    
        $requests = [];
        $request=new Request();
        $request->request_type_id = $data['request_type_id'];
        $request->created_by = $data['created_by'];
        $request['organization_id']=$data['organization_id'];
        $memberUsers = $data['request_body']['member_users'];
        $firstUser=$data['request_body']['member_users'][0];
        $organization = $this->organizationRepository->find($data['organization_id'], array('*'));
        if (isset($firstUser['evidence_document_url'])) {
            // create file for evidence document
            $result = explode('/', $firstUser['evidence_document_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $firstUser['evidence_document_url'], 0, $organization->systemAdmin);
            $attachmentFile = $this->fileRepository->create($storageFile);
            $data['evidence_document_id']  =  $attachmentFile->id;
        }
        foreach ($memberUsers as $memberUser) {
            $request->request_body=[
                'user_committee_id'=>$data['user_committee_id'],
                'committee_name_en'=>$data['committee_name_en'],
                'committee_name_ar'=>$data['committee_name_ar'],
                'user_id' => $memberUser['id'],
                'committee_user_start_date' => isset($memberUser['committee_user_start_date']) ? $memberUser['committee_user_start_date'] : null,
                'committee_user_expired_date' => isset($memberUser['committee_user_expired_date']) ? $memberUser['committee_user_expired_date'] : null,
                'name_ar' => isset($memberUser['name_ar'])? $memberUser['name_ar']:null,
                'name' => isset($memberUser['name'])? $memberUser['name']:null,
                'email' => $memberUser['email'],
            ];
            $request->evidence_document_url=isset($memberUser['evidence_document_url']) ? $memberUser['evidence_document_url'] : null;
            $request->evidence_document_id=isset($data['evidence_document_id'])?$data['evidence_document_id']:null;
            $newRequest=$this->repository->create($request->toArray());
            $requests[] = $newRequest;
      }
      return  $requests;
    }
    public function getCommitteeUsersList($committeeId,$user)
    {
       return $this->repository->getUsersRequestsByCommitteeId($committeeId,$user);
    }
    public function deleteUserfromCommitteeRequest($data)
    {    

        return $this->repository->create($data);
       
    }

    public function getCommitteeRequestsPagedList($filter, $requestTypeId, $user)
    {
        if (!property_exists($filter, "SortBy") || $filter->SortBy == 'id') {
            $filter->SortBy = "requests.id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        if (!property_exists($filter, "PageNumber")) {
            $filter->PageNumber = "1";
        }
        if (!property_exists($filter, "PageSize")) {
            $filter->PageSize = "10";
        }
        return $this->repository->getCommitteeRequestsPagesPagedList(
            $filter->PageNumber,
            $filter->PageSize,
            $filter->SortBy,
            $filter->SortDirection,
            $user->organization_id,
            $requestTypeId
        );
    }

    public function getRequestDetails($id)
    {
        $request = $this->repository->find($id,['*']);

        return $request;
    }
}