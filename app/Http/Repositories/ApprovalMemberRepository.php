<?php

namespace Repositories;
use Exception;

class ApprovalMemberRepository extends BaseRepository {


    public function model() {
        return 'Models\ApprovalMember';
    }

    public function getApprovalMembers($approvalId)
    {
        return $this->model
            ->selectRaw( "approval_members.id, users.id as user_id, users.name, users.name_ar,
            approval_members.signature_x_upper_left, approval_members.signature_y_upper_left,
            approval_members.signature_page_number, approval_members.is_signed,
            approval_members.signature_comment, approval_members.updated_at,images.image_url")
            ->join('users', 'users.id', 'approval_members.member_id')
            ->leftJoin('images', 'images.id', 'users.profile_image_id')
            ->where("approval_members.approval_id", $approvalId)->get();
    }

    public function updatedSignaturePositions($data) {
        return $this->model->upsert($data, ['id'], ['signature_x_upper_left','signature_y_upper_left','signature_page_number']);
    }

    public function findManyByIds($ids) {
        return $this->model->whereIn('id', $ids)->get();
    }

    public function getApprovalMemberByUserAndApproval($userId,$approvalId) {
        return $this->model->where(['member_id'=>$userId,'approval_id' => $approvalId])->first();
    }
}