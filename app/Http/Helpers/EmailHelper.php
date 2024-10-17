<?php

namespace Helpers;

use App\Mail\AcceptAddCommitteeMail;
use App\Mail\AcceptRequestMail;
use App\Mail\AddUserToCommitteeMail;
use App\Mail\AcceptUnfreezeCommitteeMail;
use App\Mail\AddCommitteeRequestMail;
use App\Mail\DeleteMemberFromCommitteeRequestMail;
use App\Mail\LoginVerificationCode;
use App\Mail\MeetingEnded;
use App\Mail\MeetingMom;
use App\Mail\NotificationChatMemberMail;
use App\Mail\MeetingMOMSignature;
use App\Mail\MeetingReminder;
use App\Mail\MettingAgendaPublish;
use App\Mail\MettingPublish;
use App\Mail\NewTask;
use App\Mail\EditTask;
use App\Mail\OrganizationActivationMail;
use App\Mail\ParticipantSignedNo;
use App\Mail\ParticipantSignedYes;
use App\Mail\RegistrationMail;
use App\Mail\RejectRequestMail;
use App\Mail\ResetPasswordLink;
use App\Mail\TaskExpired;
use App\Mail\TaskStatusChanged;
use App\Mail\UnFreezeCommitteeRequestMail;
use App\Mail\WelcomeNewParticipant;
use App\Mail\AdminsRegistrationMail;
use App\Mail\AddCommentToTaskMail;
use App\Mail\AddMemberToCommitteeRequestMail;
use App\Mail\AdminsOrganizationExpiredMail;
use App\Mail\DeleteDocumentRequestMail;
use App\Mail\ExpiredCommitteeMissingFinalOutputMail;
use App\Mail\MeetingRecommendationSended;
use App\Mail\NearedExpiredCommitteesMail;
use App\Mail\OrganizationExpiredMail;
use App\Mail\NewDocumentMail;
use App\Mail\NewCircularDecisionMail;
use App\Mail\NewShareDirectoryMail;
use App\Mail\NewShareFileMail;
use App\Mail\ReminderFinalCommitteeWorkMail;
use App\Mail\RemoveDirectoryAccessMail;
use App\Mail\RemoveFileAccessMail;
use Illuminate\Support\Facades\Mail;

class EmailHelper
{

    public static function sendResetPasswordLinkMail($email, $nameAr, $nameEn, $token)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new ResetPasswordLink($nameAr, $nameEn, $token, $email));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendWelcomeNewParticipantLinkMail($email, $nameAr, $nameEn, $organizationNameAr, $organizationNameEn, $token)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        if (!$organizationNameEn) {
            $organizationNameEn = $organizationNameAr;
        } else if (!$organizationNameAr) {
            $organizationNameAr = $organizationNameEn;
        }

        try {
            Mail::to($email)->send(new WelcomeNewParticipant($nameAr, $nameEn, $organizationNameAr, $organizationNameEn, $token, $email));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendRegistrationMail($email, $nameAr, $nameEn)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }
        try {
            Mail::to($email)->send(new RegistrationMail($nameAr, $nameEn));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendActivationEmail($users, $status)
    {
        foreach ($users as $user) {
            if ($user->organization_is_active === null) {
                if (!$user->name) {
                    $user->name = $user->name_ar;
                }else if (!$user->name_ar) {
                    $user->name_ar = $user->name;
                }
                try {
                    Mail::to($user->username)->send(new OrganizationActivationMail($user->name_ar, $user->name, $status));
                } catch (\Exception $e) {
                    report($e);
                }
            }
        }
    }
    public static function sendMeetingPublished($email, $nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $zoomJoinUrl,$microsoftTeamsJoinUrl, $languageId,$timeZone, $guestLink = null)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new MettingPublish($nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $zoomJoinUrl,$microsoftTeamsJoinUrl, $languageId,$timeZone, $guestLink));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendMeetingAgendaPublished($email, $nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $zoomJoinUrl, $microsoftTeamsJoinUrl, $languageId,$timeZone, $guestLink = null)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new MettingAgendaPublish($nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $zoomJoinUrl, $microsoftTeamsJoinUrl, $languageId,$timeZone, $guestLink));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendMeetingEnded($email, $emailData, $nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $pdf , $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new MeetingEnded($nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $pdf  ,$languageId, "انتهاء الإجتماع"));

        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendMOM($email, $emailData, $nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $pdfAr, $pdfEn, $languageId, $is_mom_pdf, $mom_pdf_name , $mom_pdf_url)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new MeetingMom($nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $pdfAr, $pdfEn, $languageId,  $is_mom_pdf, $mom_pdf_name , $mom_pdf_url,"محضر الإجتماع"));

        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendMeetingReminderMail($email, $nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $date)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new MeetingReminder($nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $date));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendMeetingSignature($email, $nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $meetingId, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new MeetingMOMSignature($nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $meetingId, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendParticipantSignedYesEmail($email, $nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $participantNameEn, $participantNameAr, $comment, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        if (!$participantNameEn) {
            $participantNameEn = $participantNameAr;
        }else if (!$participantNameAr) {
            $participantNameAr = $participantNameEn;
        }

        try {
            Mail::to($email)->send(new ParticipantSignedYes($nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $participantNameEn, $participantNameAr, $comment, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendParticipantSignedNoEmail($email, $nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $participantNameEn, $participantNameAr, $comment, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        if (!$participantNameEn) {
            $participantNameEn = $participantNameAr;
        }else if (!$participantNameAr) {
            $participantNameAr = $participantNameEn;
        }
        
        try {
            Mail::to($email)->send(new ParticipantSignedNo($nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $participantNameEn, $participantNameAr, $comment, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function sendNewTaskMail($email, $nameAr, $nameEn, $serial_number, $taskId, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }


        try {
            Mail::to($email)->send(new NewTask($nameAr, $nameEn, $serial_number, $taskId, $languageId));
        } catch (\Exception $e) {
            report($e);
        }

    }

    public function sendTaskExpiredMail($email, $nameAr, $nameEn,$serial_number, $taskId, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new TaskExpired($nameAr, $nameEn, $serial_number, $taskId, $languageId));
        } catch (\Exception $e) {
            report($e);
        }

    }

    public function sendTaskStatusChangedMail($email, $nameAr, $nameEn,  $serial_number, $taskStatusNameAr, $taskStatusNameEn, $changedByNameEn, $changedByNameAr, $taskId, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new TaskStatusChanged($nameAr, $nameEn, $serial_number, $taskStatusNameAr, $taskStatusNameEn, $changedByNameEn, $changedByNameAr, $taskId, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function sendEditTaskMail($email, $nameAr, $nameEn,$serial_number, $taskId, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new EditTask($nameAr, $nameEn, $serial_number, $taskId, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }


    public function sendLoginVerificationCode($email, $nameAr, $nameEn, $code,$token,$languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new LoginVerificationCode($nameAr, $nameEn, $code,$token, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
    
    public function sendAdminsRegistrationMail($admins,$user){
        foreach ($admins as $key => $admin) {
            
            if (!$admin->name) {
                $admin->name = $admin->name_ar;
            }else if (!$admin->name_ar) {
                $admin->name_ar = $admin->name;
            }

            try {
                Mail::to($admin->email)->send(new AdminsRegistrationMail($admin->name_ar,$admin->name, $user->organization->id,$user->organization->organization_name_ar,$user->organization->organization_name_en));
            } catch (\Exception $e) {
                report($e);
            }     
       }
    }

    public function sendAddCommentToTaskMail($email, $nameAr, $nameEn,$serial_number, $changedByNameEn, $changedByNameAr, $taskId, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        }else if (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new AddCommentToTaskMail($nameAr, $nameEn, $serial_number, $changedByNameEn, $changedByNameAr, $taskId, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function sendOrganizationExpiredMail($admins, $expiresOrganizations){
        foreach ($admins as $key => $admin) {
            
            if (!$admin->name) {
                $admin->name = $admin->name_ar;
            }else if (!$admin->name_ar) {
                $admin->name_ar = $admin->name;
            }

            try {
                Mail::to($admin->email)->send(new AdminsOrganizationExpiredMail($admin->name_ar,$admin->name, $expiresOrganizations));
            } catch (\Exception $e) {
                report($e);
            }     
       }
    }

    public function sendExpiredMailToOrganizationAdmin($expiresOrganizations) {
        foreach ($expiresOrganizations as $key => $organization) {
            if (!$organization['system_admin_name']) {
                $organization['system_admin_name'] = $organization['system_admin_name_ar'];
            }else if (!$organization['system_admin_name_ar']) {
                $organization['system_admin_name_ar'] = $organization['system_admin_name'];
            }

            try {
                Mail::to($organization['system_admin_email'])->send(new OrganizationExpiredMail($organization));
            } catch (\Exception $e) {
                report($e);
            }     
       }
    }

    public function sendNewDocumentCreatedMail($email, $nameAr, $nameEn, $documentSubject,$createdByNameEn,$createdByNameAr, $documentId ,$languageId){
        try {
            Mail::to($email)->send(new NewDocumentMail($nameAr, $nameEn, $documentSubject, $createdByNameEn, $createdByNameAr,$documentId , $languageId));

        } catch (\Exception $e) {
            report($e);
        }
    }
    
    public function sendNewCircularDecisionCreatedMail($email, $nameAr, $nameEn, $decisionSubjectAr, $decisionSubjectEn, $createdByNameEn, $createdByNameAr,$decisionId, $languageId){
        try {
            Mail::to($email)->send(new NewCircularDecisionMail($nameAr, $nameEn, $decisionSubjectAr, $decisionSubjectEn, $createdByNameEn, $createdByNameAr,$decisionId , $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
    public function sendNewShareDirectoryMail($email, $nameAr, $nameEn, $directoryNameAr, $directoryNameEn, $createdByNameEn, $createdByNameAr,$directoryId, $languageId){
        try {
            Mail::to($email)->send(new NewShareDirectoryMail($nameAr, $nameEn, $directoryNameAr, $directoryNameEn, $createdByNameEn, $createdByNameAr,$directoryId , $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function sendNewShareFileMail($email, $nameAr, $nameEn, $fileNameAr, $fileNameEn, $createdByNameEn, $createdByNameAr ,$languageId){
        try {
            Mail::to($email)->send(new NewShareFileMail($nameAr, $nameEn, $fileNameAr, $fileNameEn, $createdByNameEn, $createdByNameAr , $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function sendRemoveDirectoryAccessMail($email, $nameAr, $nameEn, $directoryNameAr, $directoryNameEn, $createdByNameEn, $createdByNameAr, $languageId){
        try {
            Mail::to($email)->send(new RemoveDirectoryAccessMail($nameAr, $nameEn, $directoryNameAr, $directoryNameEn, $createdByNameEn, $createdByNameAr, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function sendRemoveFileAccessMail($email, $nameAr, $nameEn, $fileNameAr, $fileNameEn, $createdByNameEn, $createdByNameAr,$languageId){
        try {
            Mail::to($email)->send(new RemoveFileAccessMail($nameAr, $nameEn, $fileNameAr, $fileNameEn, $createdByNameEn, $createdByNameAr , $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendDeleteDocumentRequest($email, $nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new DeleteDocumentRequestMail($nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
    public static function sendAddMemberToCommitteeRequest($email, $nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new AddMemberToCommitteeRequestMail($nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
    public static function sendAddCommitteeRequest($email, $nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new AddCommitteeRequestMail($nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
    public static function sendAcceptUnfreezeCommitteeRequest($email, $nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new AcceptUnfreezeCommitteeMail($nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
    public static function sendAcceptAddCommitteeRequest($email, $nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new AcceptAddCommitteeMail($nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
    public static function sendRejectRequest($email, $nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new RejectRequestMail($nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
    public static function sendUnFreezeCommitteeRequest($email, $nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new UnFreezeCommitteeRequestMail($nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendDeleteMemberFromCommitteeRequest($email, $nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new DeleteMemberFromCommitteeRequestMail($nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendEmailNearedExpiredCommittees($email, $nameAr, $nameEn, $committeeNameAr, $committeeNameEn, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new NearedExpiredCommitteesMail($nameAr, $nameEn, $committeeNameAr, $committeeNameEn, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendAcceptRequest($email, $nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new AcceptRequestMail($nameAr, $nameEn, $requestTitleAr, $requestTitleEn, $url, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function ExpiredCommitteeMissingFinalOutput(
        $email,
        $nameAr,
        $nameEn,
        $titleAr,
        $titleEn,
        $committeeHeadNameAr,
        $committeeHeadNameEn,
        $committeeNameAr,
        $committeeNameEn,
        $languageId
    ) {
        $nameEn = $nameEn ?? $nameAr;
        $nameAr = $nameAr ?? $nameEn;
        $committeeHeadNameEn = $committeeHeadNameEn ?? $committeeHeadNameAr;
        $committeeHeadNameAr = $committeeHeadNameAr ?? $committeeHeadNameEn;
        $committeeNameAr = $committeeNameAr ?? $committeeNameEn;
        $committeeNameEn = $committeeNameEn ?? $committeeNameAr;

        try {
            Mail::to($email)->send(new ExpiredCommitteeMissingFinalOutputMail(
                $nameAr,
                $nameEn,
                $titleAr,
                $titleEn,
                $committeeHeadNameAr,
                $committeeHeadNameEn,
                $committeeNameAr,
                $committeeNameEn,
                $languageId
            ));
        } catch (\Exception $e) {
            report($e);
        }
    }

    public static function sendMeetingRecommendation($email, $nameAr, $nameEn, $meetingTitleAr, $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)->send(new MeetingRecommendationSended($nameAr, $nameEn, $meetingTitleAr,
                $meetingTitleEn, $venueAr, $vanueEn, $dateFrom, $languageId, "ارسال توصيات الاجتماع")
            );
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function sendReminderFinalCommitteeWorkMail($members, $committee)
    {
        foreach ($members as $member) {
            $member['name'] = $member['name'] ?? $member['name_ar'];
            $member['name_ar'] = $member['name_ar'] ?? $member['name'];
            $committee->committee_name_en = $committee->committee_name_en ?? $committee->committee_name_ar;
            $committee->committee_name_ar = $committee->committee_name_ar ?? $committee->committee_name_en;

            try {
                Mail::to($member['email'])->send(new ReminderFinalCommitteeWorkMail(
                    $member['name_ar'], $member['name'],
                    $committee->committee_name_en, $committee->committee_name_ar,
                    $member['language_id'])
                );
            } catch (\Exception $e) {
                report($e);
            }
        }
    }

    public static function sendUserAboutAddingToCommitteeEmail($email, $nameAr, $nameEn, $emailTitleAr, $emailTitleEn, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new AddUserToCommitteeMail($nameAr, $nameEn, $emailTitleAr, $emailTitleEn, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
    public static function sendNotificationChatMemberMail($email, $nameAr, $nameEn, $emailTitleAr, $emailTitleEn, $languageId)
    {
        if (!$nameEn) {
            $nameEn = $nameAr;
        } elseif (!$nameAr) {
            $nameAr = $nameEn;
        }

        try {
            Mail::to($email)
                ->send(new NotificationChatMemberMail($nameAr, $nameEn, $emailTitleAr, $emailTitleEn, $languageId));
        } catch (\Exception $e) {
            report($e);
        }
    }
}
