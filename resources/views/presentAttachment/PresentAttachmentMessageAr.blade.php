@if($presentationStatusesId  == config('presentationStatuses.present') )  
لقد تم بدء عرض مرفقات اجتماع {{$meetingTitleAr}} {{$meetingTypeNameAr}} من قبل {{$meetingPresenterNameAr}}, اضعط لكى تنضم للعرض
@elseif($presentationStatusesId  == config('presentationStatuses.end'))
لقد تم انهاء عرض مرفقات اجتماع {{$meetingTitleAr}} {{$meetingTypeNameAr}} من قبل {{$meetingPresenterNameAr}}
@endif