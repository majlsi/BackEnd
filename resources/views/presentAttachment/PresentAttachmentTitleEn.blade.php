
@if($presentationStatusesId  == config('presentationStatuses.present') )  
Present Meeting Attachment

@elseif($presentationStatusesId  == config('presentationStatuses.end'))
End presenting Meeting Attachment

@endif