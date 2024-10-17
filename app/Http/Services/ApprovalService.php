<?php

namespace Services;

use Exception;
use Helpers\ApprovalHelper;
use Helpers\SecurityHelper;
use Helpers\StorageHelper;
use Illuminate\Database\DatabaseManager;
use Jobs\HandleApprovalDocument;
use Repositories\ApprovalRepository;
use \Illuminate\Database\Eloquent\Model;
use Models\Approval;
use Models\ApprovalMember;
use Repositories\ApprovalMemberRepository;
use Repositories\FileRepository;
use stdClass;
use File;
use Helpers\UploadHelper;
use Helpers\SignatureHelper;

class ApprovalService extends BaseService
{
    private ApprovalHelper $approvalHelper;
    private UploadHelper $uploadHelper;
    private StorageHelper $storageHelper;
    private SecurityHelper $securityHelper;
    private FileRepository $fileRepository;
    private ApprovalMemberRepository $approvalMemberRepository;
    private SignatureHelper $signatureHelper;

    public function __construct(
        DatabaseManager $database,
        ApprovalRepository $repository,
        ApprovalHelper $approvalHelper,
        UploadHelper $uploadHelper,
        StorageHelper $storageHelper,
        SecurityHelper $securityHelper,
        FileRepository $fileRepository,
        ApprovalMemberRepository $approvalMemberRepository,
        SignatureHelper $signatureHelper
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->approvalHelper = $approvalHelper;
        $this->uploadHelper = $uploadHelper;
        $this->storageHelper = $storageHelper;
        $this->securityHelper = $securityHelper;
        $this->fileRepository = $fileRepository;
        $this->approvalMemberRepository = $approvalMemberRepository;
        $this->signatureHelper = $signatureHelper;
    }

    public function prepareCreate(array $data)
    {
        try {
            $user = $this->securityHelper->getCurrentUser();
            $data['status_id'] = config("approvalStatuses.new", 1);
            $data['created_by'] = $user['id'];
            $data['organization_id'] = $user['organization_id'];

            $storageFile = $this->storageHelper->mapSystemFile($data['attachment_name'], $data['attachment_url'], 0, $user);
            $attachmentFile = $this->fileRepository->create($storageFile);
            $data['file_id'] = $attachmentFile->id;

            $approval = $this->repository->create($data);
            $members = $this->approvalHelper->prepareApprovalMembers($data["members"]);

            $approval->members()->createMany($members);
            UploadHelper::convertApprovalDocumentToImages($approval);

            return $approval;
        } catch (Exception $e) {
            return null;
        }
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $user = $this->securityHelper->getCurrentUser();
        $isAttachChanged = $data['attachment_url'] != $model->attachment_url;
        if ($isAttachChanged) {

            $storageFile = $this->storageHelper
                ->mapSystemFile($data['attachment_name'], $data['attachment_url'], 0, $user);

            $attachmentFile = $this->fileRepository->create($storageFile);
            $data['file_id'] = $attachmentFile->id;
        }
        $this->repository->update($data, $model->id);

        $this->handleUpdateApproverMembers($model, $data);

        if ($isAttachChanged) {
            $approval = $this->repository->find($model->id);
            UploadHelper::convertApprovalDocumentToImages($approval);
        }
        return $model;
    }

    public function handleUpdateApproverMembers($model, $data)
    {
        // Get the existing members from the database
        $existingMembers = $model->members()->pluck('member_id')->toArray();

        // Prepare the new members
        $newMembers = $this->approvalHelper->prepareApprovalMembers($data["members"]);

        // Find the common members (those that exist in both existing and new members)
        $commonMembers = array_intersect($existingMembers, array_column($newMembers, 'member_id'));

        // Find the missing members (those that exist in the existing members but not in the new members)
        $missingMembers = array_diff($existingMembers, $commonMembers);

        // Find the new members (those that exist in the new members but not in the existing members)
        $newMemberIds = array_diff(array_column($newMembers, 'member_id'), $commonMembers);
        $newMembersToAdd = array_filter($newMembers, function ($member) use ($newMemberIds) {
            return in_array($member['member_id'], $newMemberIds);
        });

        // Delete the missing members
        if (!empty($missingMembers)) {
            ApprovalMember::where('approval_id', $model->id)->whereIn('member_id', $missingMembers)->delete();
        }

        // Add the new members
        $model->members()->createMany($newMembersToAdd);
    }

    public function prepareDelete(int $id)
    {
        $approval = $this->getById($id);
        if (!isset($approval)) {
            return;
        }
        $approval->members()->delete();
        return $this->repository->delete($id);
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
            $filter->SortBy = "approvals.id";
        } else if ($filter->SortBy == 'id') {
            $filter->SortBy = "approvals.id";
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
        return $this->repository->getApprovalsPagedList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $user->organization_id, $user->id);
    }

    public function getApprovalData(Approval $approval)
    {
        $approval = $this->repository->find($approval->id);
        $members = $this->approvalMemberRepository->getApprovalMembers($approval->id);
        return $this->approvalHelper->mapApprovalData($approval, $members);
    }

    public function getApprovalDocumentSlides($document)
    {
        $path = public_path() . '/uploads/approvals/' . $document->id;
        $data = [];
        if (File::isDirectory($path)) {
            $filesInFolder = \File::files($path);
            natsort($filesInFolder);
            foreach ($filesInFolder as $imgPath) {
                $images = [];
                $imageDetails = pathinfo($imgPath);
                $imagedetails = getimagesize($imgPath);
                $width = $imagedetails[0];
                $height = $imagedetails[1];
                $imageName = $imageDetails['basename'];
                $images = "/uploads/approvals/$document->id/" . urlencode($imageName);
                if ($imageDetails['extension'] == 'json') {
                    $data['document_notes'] = $images;
                } else {
                    $data['document_images'][] = $images;
                    $data['document_images_with_size'][] = ['url' => $images, 'width' => $width, 'height' => $height];
                }
            }
        }
        return $data;
    }

    public function updateApprovalSignaturesPlaces($data)
    {
        $approvalMembers = $this->approvalHelper->prepareApprovalMembersUpdate($data);
        $approvalMembersIds = array_map(function ($approvalMember) {
            return $approvalMember['id'];
        }, $approvalMembers);

        $this->approvalMemberRepository->updatedSignaturePositions($approvalMembers);

        $dbApprovalMembers = $this->approvalMemberRepository->findManyByIds($approvalMembersIds);

        $fileUrl = $this->approvalMemberRepository->find($approvalMembers[0]['id'])->approval->attachment_url;
        if (strtolower(pathinfo(storage_path($fileUrl), PATHINFO_EXTENSION)) != 'pdf') {
            $fileUrl = $this->uploadHelper->convertFileToPdf(
                public_path() . '/' . $fileUrl,
                '/uploads/approvals',
                pathinfo($fileUrl, PATHINFO_FILENAME)
            );
            $fileUrl=public_path() .$fileUrl;
        }
        $pdf = fopen($fileUrl, 'r');
        $user = $this->securityHelper->getCurrentUser();
        $langId = $dbApprovalMembers[0]->approval->approvalSender->language_id;

        if($dbApprovalMembers[0]->approval->signature_document_id){
            $docId = $this->signatureHelper->updateApprovalDocument($user->organization, $pdf, $dbApprovalMembers[0]->approval->signature_document_id, $dbApprovalMembers, $langId);
            return $dbApprovalMembers;
        } else {
            $docId = $this->signatureHelper->createApprovalDocument($user->organization, $pdf, $dbApprovalMembers, $langId);
            $updated = ['signature_document_id' => $docId];
            return $this->repository->update($updated, $dbApprovalMembers[0]->approval_id);
        }
    }

    public function loginUserToDigitalSignature($approval) {
        $user = $this->securityHelper->getCurrentUser();
        $userToken = $this->signatureHelper->loginUser($user->organization, $user->email, $approval->signature_document_id);
        return ['token' => $userToken, 'timeZone' => $user->organization->timeZone->diff_hours];
    }

    public function getApprovalByDocumentId($documentId) {
        return $this->repository->getApprovalBySignatureDocumentId($documentId);
    }

    public function signApproval($data, $approval, $user) {
        $approvalMember = $this->approvalMemberRepository->getApprovalMemberByUserAndApproval($user->id, $approval->id);
        $member = $this->approvalMemberRepository->update(
            ['is_signed' => $data['is_signed'], 'signature_comment' => $data['comment']],
            $approvalMember->id
        );
        $allApprovalMembers = $approval->members;
        $signedMembers = [];
        foreach ($allApprovalMembers as $approvalMember) {
            if (!is_null($approvalMember->is_signed)) {
                $signedMembers[] = $approvalMember;
            }
        }

        if (count($allApprovalMembers) == count($signedMembers)) {
            $this->repository->update(['status_id' => config('approvalStatuses.completed')], $approval->id);
        } elseif (count($allApprovalMembers) > count($signedMembers) && !empty($signedMembers)) {
            $this->repository->update(['status_id' => config('approvalStatuses.awaiting')], $approval->id);
        } 
        return $member;
    }
}
