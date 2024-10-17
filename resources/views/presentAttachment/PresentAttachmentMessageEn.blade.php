
@if($presentationStatusesId  == config('presentationStatuses.present') )  
{{$meetingPresenterNameEn}} start presenting {{$meetingTitleEn}} {{$meetingTypeNameEn}} meeting attachment now, Click to join the presentation.

@elseif($presentationStatusesId  == config('presentationStatuses.end'))
{{$meetingPresenterNameEn}} end presenting {{$meetingTitleEn}} {{$meetingTypeNameEn}} meeting attachment now

@endif