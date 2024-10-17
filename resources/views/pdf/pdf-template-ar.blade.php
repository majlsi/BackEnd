<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Mjlsi - PDF</title>
  
  <!-- Custom styles for this template -->
  <link rel="stylesheet"  type="text/css" media="all" href="{{ asset('css/PDF-ar.css') }}" >

<style>
@page { 
    margin: 0px;
    margin: 0px 0px 0px 0px !important;
    padding: 25px 15px 10px 15px !important;
}

@page {
/* 	size: 8.5in 11in; */ /* <length>{1,2} | auto | portrait | landscape */
	      /* 'em' 'ex' and % are not allowed; length values are width height */
	margin: 2%; /* <any of the usual CSS values for margins> */
	             /*(% of page-box width for LR, of height for TB) */
	margin-header: 1mm; /* <any of the usual CSS values for margins> */
	margin-footer: 2mm; /* <any of the usual CSS values for margins> */
/* 
	header: html_myHTMLHeaderOdd;
	footer: html_myHTMLFooterOdd; */
/* 	background: ... */
	/* background-image: url({{ URL::asset('img/bg-1.png') }}); */
/* 	background-position ... */
/* 	background-repeat  no-repeat; */
/* 	background-color ...
	background-gradient: ... */
}
  @page{
    border-top: 2mm solid #f6861f;
  }

/* body { margin: 0px; } */
.white-bg{
    width: 190mm;
 padding: 0;
 background-color: #333;
 border-top: 2mm solid #f6861f;
}
body { font-family: XB Riyaz; }
</style>
</head>

<body>
          @php
          setlocale(LC_ALL, 'ar_AE.utf8');
          @endphp
        <div class="imagelogo" style="display: block;position: absolute;left: 40px;">
          <img style="width:7em!important;max-height:2em!important;max-width:7em;border-radius: 50%;" src="{{ $data['meeting_mom_template_logo'] }}">
        </div>
        <div style="text-align: center;padding-top:2mm;">
          <a class="col" style="font-size: 11pt;">
          
          <!-- @if($data['organization']['organization_name_ar'] == null || empty($data['organization']['organization_name_ar'])  )
              {{$data['organization']['organization_name_en']}}
          @else
              {{$data['organization']['organization_name_ar']}}
          @endif -->
          @if($data['show_mom_header'] == 1)
          {{$data['meeting_title_ar']? $data['meeting_title_ar'] : $data['meeting_title_en']}}
          @endif
          </a>
        </div>
  <!-- Page Template -->
  <div class="white-page mx-auto">


    <div style="clear:both;"></div>

    <!-- <header class="masthead">
      <div class="media my-3">
        <div class="mr-3 mt-3 media-aside">
          <div class="align-self-top">
          <div class="month-header">{{ Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%B') }}</div>
            <div class="grey-lg-text"> {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%d') }} </div>
            <div class="week-day-text"> {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%A') }} </div>
          </div>
        </div>
        <div class="media-body">
          <h3 class="" style="margin: 4mm 4mm 2mm;font-size: 14pt;line-height: 18pt;">
          {{$data['meeting_title_ar']? $data['meeting_title_ar'] : $data['meeting_title_en']}}
          </h3>
          <p class="" style="margin:0 4mm;">{{ Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%A, %B %d, %Y, %I:%M %p') }} -
          {{ Carbon\Carbon::parse($data['meeting_schedule_to'])->formatLocalized('%A, %B %d, %Y, %I:%M %p') }}
          ( {{$data['time_zone']['description_ar']}} )
          </p>
          <p class="" style="margin: 1mm 4mm 4mm;">{{$data['meeting_code']}}</p>
        </div>
      </div>
    </header> -->
    <div style="clear:both;"></div>

    <div class="" style="page-break-inside: avoid;">
    تم -بعون الله تعالى- انعقاد  {{$data['meeting_title_ar']? $data['meeting_title_ar'] : $data['meeting_title_en']}} ل {{$data['committee_name_ar']? $data['committee_name_ar'] : $data['committee_name_en']}}
     يوم {{Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%A')}} بتاريخ {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->format('d/m/Y') }}م في تمام الساعة {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%I:%M %p') }} إلى الساعة {{ Carbon\Carbon::parse($data['meeting_schedule_to'])->formatLocalized('%I:%M %p') }}،
     ب {{$data['meeting_venue_ar']? $data['meeting_venue_ar'] : $data['meeting_venue_en']}}،
        <div style="clear:both;"></div>

      <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;">
        <span style="width:16%;" class="bold">
          <span style="margin: auto;"> وبرئاسة </span>
        </span>
        <span class="font-10" style="width: 80%;">
          <span>
            <span>
            @if($data['meeting_committee']['committee_head']['job_title_ar'] != null && !empty($data['meeting_committee']['committee_head']['job_title_ar'])  )
                  {{$data['meeting_committee']['committee_head']['job_title_ar']}}
              @else
                @if($data['meeting_committee']['committee_head']['job_title_en'] != null && !empty($data['meeting_committee']['committee_head']['job_title_en'])  )
                    {{$data['meeting_committee']['committee_head']['job_title_en']}}
                    @endif
          @endif

          @if($data['meeting_committee']['committee_head']['nickname_ar'] != null && !empty($data['meeting_committee']['committee_head']['nickname_ar'])  )
                  {{$data['meeting_committee']['committee_head']['nickname_ar']}}
              @else
                @if($data['meeting_committee']['committee_head']['nickname_en'] != null && !empty($data['meeting_committee']['committee_head']['nickname_en'])  )
                    {{$data['meeting_committee']['committee_head']['nickname_en']}}
                    @endif
          @endif

            @if($data['meeting_committee']['committee_head']['user_title_ar'] != null && !empty($data['meeting_committee']['committee_head']['user_title_ar'])  )
          {{$data['meeting_committee']['committee_head']['user_title_ar']}}: 
              @else
                  {{$data['meeting_committee']['committee_head']['user_title_en']}}: 
              @endif

          @if($data['meeting_committee']['committee_head']['name_ar'] == null || empty($data['meeting_committee']['committee_head']['name_ar'])  )
                {{$data['meeting_committee']['committee_head']['name']}}
            @else
                {{$data['meeting_committee']['committee_head']['name_ar']}}
            @endif
            </span>
      
        </span>
        </span>
      </div>
   
   
    <div style="clear:both;"></div>
    @if(count($data['meeting_participants']))
      <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;">
        <span style="width:16%;" class="bold">
          <span style="margin: auto;">  وبحضور أصحاب المعالي والسعادة أعضاء مجلس الإدارة  التالية أسمائهم:</span>
        </span>
        <br>
        <span class="font-10" style="width: 80%;">
          <span>
          @foreach ($data['meeting_participants'] as $indx=>$participant)
          <span>
            {{$indx +1 }}.
          @if($participant['user_title_ar'] != null && !empty($participant['user_title_ar'])  )
          {{$participant['user_title_ar']}}: 
              @else
                  {{$participant['user_title_en']}}: 
              @endif

              @if($participant['name_ar'] == null || empty($participant['name_ar'])  )
                 {{$participant['name']}}
             @else
                 {{$participant['name_ar']}}
             @endif

           @if($participant['nickname_ar'] != null && !empty($participant['nickname_ar'])  )
                  ({{$participant['nickname_ar']}})
              @else
                @if($participant['nickname_en'] != null && !empty($participant['nickname_en'])  )
                    ({{$participant['nickname_en']}})
                    @endif
          @endif
      <br>
          @if($participant['job_title_ar'] != null && !empty($participant['job_title_ar'])  )
                  {{$participant['job_title_ar']}}
              @else
                @if($participant['job_title_en'] != null && !empty($participant['job_title_en'])  )
                    {{$participant['job_title_en']}}
                    @endif
          @endif
            </span>
            @if(!$loop->last)
            <br>
            @endif

          @endforeach

          </span>
        </span>
      </div>
      @endif
      <div style="clear:both;"></div>
      <br>
      @if(isset($data['meeting_note_ar']) )
      <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;">
        <span style="width:16%;" class="bold">
          <span style="margin: auto;">ملاحظات اﻷجتماع</span>
        </span>
        <span class="font-10" style="width: 80%;">
        {{$data['meeting_note_ar']}}
        </span>
      </div>
      @endif
    </div>
  

    <div style="clear:both;min-height:1mm"></div>
    @if(count($data['meeting_agendas'])) 
    
    <div class="bold" style="padding-top:2mm;font-weight: 600;">
      <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;">جدول اﻷعمال</h4>
      @foreach ($data['meeting_agendas'] as $agenda)
      <div  class="clear page-break"
        style="page-break-inside: avoid;
        background: #fff;
        padding: 0 8px;
        padding: 2mm 2mm 0mm 3mm;
        margin-bottom: 3mm;
        clear: both;
        display: block;">
        <div style="text-align:right;border-bottom: 1px dashed #ddd;margin: 0mm 0 2mm;padding: 0mm 0 0;">
          <h4 style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 3mm 0 0mm;">
          
          {{$agenda['agenda_title_ar']? $agenda['agenda_title_ar'] : $agenda['agenda_title_en']}}
          <span style="font-weight: 500;font-size: 11pt;line-height: 14pt;margin: 2mm 0 4mm;">
           (دقيقة {{$agenda['agenda_time_in_min']}}) </span>
        </h4>
          
        </div>

        <div style="clear:both;"></div>

        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
          <span style="width:16%;" class="bold">
            <span style="margin: auto;">المقدمين</span>
          </span>
          
          <span class="font-10" style="font-weight: 500;width: 80%;">
          <span>
          @foreach ($agenda['presenters_agenda'] as $agendaPresenter)
            <span>
            @if($agendaPresenter['user_title_ar'] != null && !empty($agendaPresenter['user_title_ar'])  )
                  {{$agendaPresenter['user_title_ar']}}
              @else
                  {{$agendaPresenter['user_title_en']}}
              @endif

              @if($agendaPresenter['name_ar'] == null || empty($agendaPresenter['name_ar'])  )
                {{$agendaPresenter['name']}}
            @else
                {{$agendaPresenter['name_ar']}}
            @endif

              @if($agendaPresenter['job_title_ar'] != null && !empty($agendaPresenter['job_title_ar'])  )
                  ({{$agendaPresenter['job_title_ar']}})
              @else
                @if($agendaPresenter['job_title_en'] != null && !empty($agendaPresenter['job_title_en'])  )
                    ({{$agendaPresenter['job_title_en']}})
                @endif
              @endif

            </span>

            @if(!$loop->last)
            ,
            @endif

            @endforeach
</span>
          </span>
        
        </div>

        <div style="clear:both;"></div>
        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
          <span style="width:16%;" class="bold">
            <span style="margin: auto;">الهدف</span>
          </span>
          <span class="font-10" style="font-weight: 500;width: 80%;">
          {{ $agenda['agenda_purpose']['purpose_name_ar']}}
           
          </span>
        </div>

        <div style="clear:both;"></div>

        @if(count($agenda['agenda_votes'])) 
        <div class="m-demo mb-2" style="border-top: 1px dashed #ddd;margin: 1mm 0 5mm;padding: 2mm 0 0;">
          <div class="m-demo__preview--btn"><!--class="m-demo__preview"-->
            <h5 style="margin: 0 0 2mm;background-color: #ddd;display: inline-block;padding: 0 2mm;line-height: 16px;">
              القرارات </h5>
              @foreach ($agenda['agenda_votes'] as $agendaVote)
            <p class="mt-0" style="color: #333;margin: 0;">
            {{ $agendaVote['vote_subject_ar']? $agendaVote['vote_subject_ar'] : $agendaVote['vote_subject_en']}}
            </p>
            <div class="btn-group" role="group">

              <label class="m-vote-btn-group">

                <label for="test1">
                  <i class="checkmark"></i>
                  نعم: </label>
                <span>{{$agendaVote['num_agree_votes']}}</span>
              </label>
              <label class="m-vote-btn-group">
                <label for="test2">
                  <i class="close black"></i>
                  لا: </label>
                <span>{{$agendaVote['num_disagree_votes']}}</span>
              </label>
              <label class="m-vote-btn-group">
                <label for="test3">
                  <i class="circle"></i> امتنع عن اخذ قرار: </label>
                <span>
                {{$agendaVote['num_abstained_votes']}}
                </span>
              </label>

            </div>

            @endforeach

          </div>
        </div>
      
      @endif
      <!---->
      @if(count(array_filter($agenda['agenda_user_comments'],function($item){return $item['is_organizer'] == true ; })))  
      <div class="bold" style="padding-top:2mm;font-weight: 600;padding-bottom: 10px;">
          <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;">التعليقات</h4>   
          @foreach (array_filter($agenda['agenda_user_comments'],function($item){return $item['is_organizer'] == true ; }) as $agendaUserComment)
          <div class="streamItem" style="background: #fff;-webkit-box-shadow: 0 1px 4px rgba(0,0,0,.04);box-shadow: 0 1px 4px rgba(0,0,0,.04);border: 1px solid rgba(0,0,0,.09);-webkit-border-radius: 3px;border-radius: 3px;padding: 15px 20px 18px;margin: 10px 0 10px;">
              <div class="cardChromeless u-marginTop20 u-paddingTop10 u-paddingBottom15 u-paddingLeft20 u-paddingRight20">
                <div class="postArticle postArticle--short js-postArticle js-trackPostPresentation js-trackPostScrolls">
                <div class="u-clearfix u-marginBottom15 u-paddingTop5">
                  <div class="postMetaInline u-floatLeft">
                  <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
          
          <div class="font-10" style="font-weight: 500;width: 80%;">
            <span style="clear: both;">
            @if($agendaUserComment['name_ar'] == null || empty($agendaUserComment['name_ar'])  )       
              {{ $agendaUserComment['name']}}
            @else
            {{ $agendaUserComment['name_ar']}}
            @endif
          </span>
    
              <br>
       
                
                    <time datetime="2018-06-20T11:19:02.928Z" style="font-size: 12px;">
                    {{ Carbon\Carbon::parse($agendaUserComment['created_at'])->format('M d, Y, g:i A') }}
                    </time>
          </div>
          
        </div>
              </div>
            </div>
            <div>
           
              <div class="postArticle-content js-postField">
                <section class="section section--body section--first section--last">
                  <div class="section-divider">
                      <div style="clear:both;height: 5px;"></div>
                  </div>
                  <div class="section-content">
                    <div class="section-inner sectionLayout--insetColumn">
                      <p name="b17a" id="b17a" class="graf graf--p graf--leading graf--trailing">
                       {!! $agendaUserComment['comment_text'] !!}
                       
                      </p>
                    </div>
                  </div>
                </section>
              </div>
          
          </div>
        </div>
      </div>
                </div>
                @endforeach
        </div>
        @endif

    <!---->



      </div>

      @endforeach
     

    </div>
   
    <div style="clear:both;"></div>


    
  

  
    @endif
    <!-- <div class="mom" style="page-break-inside: avoid;">
      <h4 style="    font-weight: 600;
        font-size: 15pt;
        line-height: 14pt;
        margin: 6mm 0 3mm;
        letter-spacing: 0;" class="graf graf--h4 graf-after--p">Min of Meeting</h4>

      <p name="9744" id="9744" class="graf graf--p graf-after--p">As you might expect, dual track design has two tracks
        <em class="markup--em markup--p-em">(a Sherlock Holmes level deduction, I know, don’t everybody gasp at
          once)</em>:</p>


      <ol class="postList">
        <li name="69d6" id="69d6" class="graf graf--li graf-after--h4">
          In the beginning there was Don Norman, and Don invented UX.
        </li>
        <li name="8a81" id="8a81" class="graf graf--li graf-after--li">
          Other innovators like Jesse James Garrett fleshed out Don Norman’s ideas
          and the classic UX process was born.
        </li>
        <li name="7822" id="7822" class="graf graf--li graf-after--li">
          Classic UX worked well with the standard waterfall development
          cadence and everybody was happy. UX was still a niche practice though.
        </li>
      </ol>
      <p>
        Meeting Description is here. The committee
        manager will discuss the financial plan for this
        year. This will contain three agendas and two
        votes.
      </p>
      <p>
        The committee
        manager will discuss the financial plan for this
        year. This will contain three agendas and two
        votes. Meeting Description is here. The committee
        manager will discuss the financial plan for this
        year. This will contain three agendas and two
        votes. Meeting Description is here. The committee
        manager will discuss the financial plan for this
        year. This will contain three agendas and two
        votes.
      </p>

    </div> -->

  </div>

  <!-- Page Template -->





</body>

</html>