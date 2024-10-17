<?php

namespace Helpers;

use Models\Directory;
use Storages\StorageFactory;

class StorageHelper
{
    private $storage;

    public function __construct()
    {
        $this->storage = StorageFactory::createStorage();
    }

    public function createMeetingDirectory($meeting, $user)
    {
        $suffix = '';
        if(isset($meeting['version_number'])){
            $suffix = '_ver_'. $meeting['version_number'];
        }
        $directory = new Directory();
        if (isset($meeting['meeting_title_en'])) {
            $directory->directory_name = $meeting['meeting_title_en'] . $suffix;
            $directory->directory_name_ar  = $meeting['meeting_title_en'] . $suffix;
        }
        if (isset($meeting['meeting_title_ar'])) {
            $directory->directory_name = $meeting['meeting_title_ar'] . $suffix;
            $directory->directory_name_ar =  $meeting['meeting_title_ar'] . $suffix;

        }
        $directory->directory_owner_id = $user->id;
        $directory->order = 0;
        $directory_name = $directory->directory_name ?? $directory->directory_name_ar;
        $path = $this->storage->createDirectory($directory_name);
        $directory->directory_path = $path;
        return $directory;
    }

    public function createMeetingAgendaDirectory($meetingAgenda, $meeting)
    {
        $parent_directory = $meeting->directory;
        $directory = new Directory();
        if (isset($meetingAgenda['agenda_title_en'])) {
            $directory->directory_name = $meetingAgenda['agenda_title_en'];
            $directory->directory_name_ar = $meetingAgenda['agenda_title_en'];

        }
        if (isset($meetingAgenda['agenda_title_ar'])) {
            $directory->directory_name =$meetingAgenda['agenda_title_ar'];
            $directory->directory_name_ar = $meetingAgenda['agenda_title_ar'];
        }
        $directory->directory_owner_id = $meeting->created_by;
        $directory->order = 0;
        $directory_name = $directory->directory_name ?? $directory->directory_name_ar;
        $path = $this->storage->createDirectory($directory_name, $parent_directory->directory_path);
        $directory['parent_directory_id'] = $meeting['directory_id'];
        $directory->directory_path = $path;
        return $directory;
    }

    public function mapUploadFile($file,$urls,$index ,$user,$directory_id){
        $fileData = [];
        
        $fileName  = $file->getClientOriginalName();
        $result = substr($fileName,0,strrpos($fileName,'.'));

        $fileData['file_name'] = $result;
        $fileData['file_name_ar'] = $result;
        $fileData['file_owner_id'] = $user->id;
        $fileData['organization_id'] = $user->organization_id;
        $fileData['order'] = $index;
        $fileData["file_path"]  = $urls[$index];
        $fileData["directory_id"]  = $directory_id;

        $fileData["file_size"]  = $this->storage->getSize($urls[$index]);

        $fileData["file_type_id"]  = $this->storage->getFileType($urls[$index]);
        return $fileData;
    }


    public function mapFileFromAttachment($fileName,$url,$index ,$user,$directory_id)
    {
        $fileData = [];
        $result = substr($fileName,0,strrpos($fileName,'.'));
        $fileData["file_size"]  = $this->storage->getSize($url);
        $fileData['file_name'] = $result;
        $fileData['file_name_ar'] = $result;
        $fileData['file_owner_id'] = $user->id;
        $fileData['organization_id'] = $user->organization_id;
        $fileData['order'] = $index;
        $fileData['file_path'] = $url;
        $fileData['directory_id']= $directory_id;
        $fileData["file_type_id"]  = $this->storage->getFileType($url);
        return  $fileData; 
    }

    public function mapSystemFile($fileName,$url,$index ,$user){
        $fileData = [];
        
        $result = substr($fileName,0,strrpos($fileName,'.'));

        $fileData['file_name'] = $result;
        $fileData['file_name_ar'] = $result;
        $fileData['file_owner_id'] = $user->id;
        $fileData['organization_id'] = $user->organization_id;
        $fileData['order'] = $index;
        $fileData['file_path'] = $url;
        $fileData["is_system"]  = 1;

        $fileData["file_size"]  = $this->storage->getSize($url);

        $fileData["file_type_id"]  = $this->storage->getFileType($url);
        return $fileData;
    }

    public function createDocumentDirectory($document, $user)
    {
        $directory = new Directory();
        
        $directory->directory_name = $document['document_subject_ar'];
        $directory->directory_name_ar  = $document['document_subject_ar'];

        $directory->directory_owner_id = $user->id;
        $directory->order = 0;
        $directory_name = $directory->directory_name ?? $directory->directory_name_ar;
        $path = $this->storage->createDirectory($directory_name);
        $directory->directory_path = $path;
        return $directory;
    }
        
    public function createCircularDecisionDirectory($vote, $user)
    {
        $directory = new Directory();
        if (isset($vote['vote_subject_ar'])) {
            $directory->directory_name = $vote['vote_subject_ar'];
            $directory->directory_name_ar  = $vote['vote_subject_ar'];
        } else if (isset($vote['vote_subject_en'])) {
            $directory->directory_name = $vote['vote_subject_en'];
            $directory->directory_name_ar = $vote['vote_subject_en'];
        }
        $directory->directory_owner_id = $user->id;
        $directory->order = 0;
        $directory_name = $directory->directory_name ?? $directory->directory_name_ar;
        $path = $this->storage->createDirectory($directory_name);
        $directory->directory_path = $path;
        return $directory;
    }

    public function createCommitteeDirectory($committee)
    {
        // $parent_directory = $committee->directory;
        $directory = new Directory();
        if (isset($committee['committee_name_ar'])) {
            $directory->directory_name =$committee['committee_name_ar'];
            $directory->directory_name_ar =$committee['committee_name_ar'];

        }
        if (isset($committee['committee_name_en'])) {
            $directory->directory_name =$committee['committee_name_en'];
            $directory->directory_name_ar = $committee['committee_name_en'];
        }
        $directory->directory_owner_id = $committee['committee_head_id'];
        $directory->order = 0;
        $directory_name = $directory->directory_name ?? $directory->directory_name_ar;
        $path = $this->storage->createDirectory($directory_name);
        $directory['parent_directory_id'] = $committee['directory_id'] ?? null;
        $directory->directory_path = $path;
        
        return $directory;
    }
}
