<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Repositories\MeetingRecommendationRepository;
use Repositories\MeetingRepository;


class MeetingRecommendationService extends BaseService
{
    private $meetingRepository;
    public function __construct(
        DatabaseManager $database,
        MeetingRecommendationRepository $repository,
        MeetingRepository $meetingRepository
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->meetingRepository = $meetingRepository;
    }

    public function prepareCreate(array $data)
    {
        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function getMeetingRecommendationsForMeeting($meetingId)
    {
        $meetingRecommendations = $this->repository->getMeetingRecommendationsForMeeting($meetingId)->toArray();
        return $meetingRecommendations;
    }
    public function getRecommendationForMeeting(int $meetingId, int $meetingRecommendationId)
    {
        $meetingRecommendations = $this->repository->getRecommendationsForMeeting($meetingId, $meetingRecommendationId)->toArray();
        return $meetingRecommendations;
    }

    public function updateMeetingRecommendations($meetingRecommendations, $meetingId)
    {
        $numberOfUpdatedRecommendations = 0;
        $haveNewRecommendationsAdded = false;
        $haveDeletedRecommendations = false;
        $meeting = $this->meetingRepository->find($meetingId);
        $RecommendationsOfMeetings = $meeting->meetingRecommendations()->orderBy('id')->get()->toArray();
        
        if(count($RecommendationsOfMeetings) == count($meetingRecommendations)){
            $numberOfUpdatedRecommendations = count($RecommendationsOfMeetings);
        } else if (count($RecommendationsOfMeetings) < count($meetingRecommendations)){
            $numberOfUpdatedRecommendations = count($RecommendationsOfMeetings);
            $haveNewRecommendationsAdded = true;
        } else if (count($RecommendationsOfMeetings) > count($meetingRecommendations)){
            $numberOfUpdatedRecommendations = count($meetingRecommendations);
            $haveDeletedRecommendations = true;
        }
        foreach ($meetingRecommendations as $index => $meetingRecommendation) {
            $meetingRecommendation['meeting_id'] =  $meetingId;
            if ($index < $numberOfUpdatedRecommendations) {
                $meetingRecommendationModel = $this->getById($RecommendationsOfMeetings[$index]['id']);
                $this->repository->update($meetingRecommendation, $RecommendationsOfMeetings[$index]['id']);
            } else {
                $meetingRecommendationModel = $this->repository->create($meetingRecommendation);
            }
        }
        if ($haveDeletedRecommendations) { // delete meeting Recommendations
            for ($i=$numberOfUpdatedRecommendations; $i < count($RecommendationsOfMeetings); $i++) { 
                $targetMeetingRecommendations = $this->repository->find($RecommendationsOfMeetings[$i]['id']);
                $this->repository->delete($targetMeetingRecommendations->id);
            }
        }
        return $this->repository->getMeetingRecommendationsForMeeting($meetingId);
    }
}
