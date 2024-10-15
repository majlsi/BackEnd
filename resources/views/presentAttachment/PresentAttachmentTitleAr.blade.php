@if($presentationStatusesId  == config('presentationStatuses.present') )  
عرض مرفقات اﻷجتماع

@elseif($presentationStatusesId  == config('presentationStatuses.end'))
انهاء عرض مرفقات اﻷجتماع

@endif