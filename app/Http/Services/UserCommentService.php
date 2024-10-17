<?php

namespace Services;

use Repositories\UserCommentRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Helpers\UserCommentHelper;
class UserCommentService extends BaseService
{

    public function __construct(DatabaseManager $database, UserCommentRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }

    public function prepareCreate(array $data)
    {
       return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        return $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function getUserComment($meetingAgendaId,$userId){
        return $this->repository->getUserComment($meetingAgendaId,$userId);
    }

    public function addOrUpdateUserComment($meetingAgendaId,$userId,$commentText,$isOrganizer){
        //$userComment = $this->repository->getUserComment($meetingAgendaId,$userId);
        //if($userComment){
            /** update user comment */
            //$userCommentCreated = $this->repository->update(['comment_text' => $commentText],$userComment->id);
       // } else {
            /** create user comment */
            $userCommentData = UserCommentHelper::prepareDataOnCreate($meetingAgendaId,$userId,$commentText,$isOrganizer);
            $userCommentCreated = $this->repository->create($userCommentData);
        //}
        return $userCommentCreated;
    }

    public function getUserCommentById($userCommentId){
        return $this->repository->getUserCommentById($userCommentId);
    }
    
}

