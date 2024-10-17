<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>lijani - PDF</title>
  
  <!-- Custom styles for this template -->
  <link rel="stylesheet"  type="text/css" media="all" href="{{ asset('css/PDF.css') }}" >

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
    /* background-image: url(../img/bg-1.png);
    background-size: 100%;
    background-repeat: no-repeat; */
  }

/* body { margin: 0px; } */
.white-bg{
    width: 190mm;
 padding: 0;
 background-color: #333;
 border-top: 2mm solid #f6861f;
 /*    background-image: url(../img/bg-1.png);
    background-size: 100%;
    background-repeat: no-repeat; */
}
body { font-family: XB Riyaz; }
</style>
</head>

<body>
<div class="imagelogo" style="display: block;position: absolute;right: 40px;">
          <img style="width:7em!important;max-height:2em!important;max-width:7em;border-radius: 50%;" src="{{ $data['meeting_mom_template_logo'] }}">
        </div>
      <div style="text-align: center;">

        <a class="col" style="font-size: 11pt;">
        <!-- @if($data['organization']['organization_name_en'] == null || empty($data['organization']['organization_name_en'])  )      
             {{$data['organization']['organization_name_ar']}}
        @else
             {{$data['organization']['organization_name_en']}}    
        @endif -->
        @if($data['show_mom_header'] == 1)
        @if($data['meeting_title_en'] == null || empty($data['meeting_title_en'])  )       
             {{$data['meeting_title_ar']}}
        @else
            {{$data['meeting_title_en']}}    
        @endif
        @endif
        </a>
      </div>

  <!-- Page Template -->
  <div class="white-page mx-auto">

    <!-- <header class="masthead">
      <div class="media my-3">
        <div class="mr-3 mt-3 media-aside">
          <div class="align-self-top">
            <div class="month-header">{{ Carbon\Carbon::parse($data['meeting_schedule_from'])->format('M') }}</div>
            <div class="grey-lg-text"> {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->format('d') }} </div>
            <div class="week-day-text"> {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->format('D') }} </div>
          </div>
        </div>
        <div class="media-body">
          <h3 class="" style="margin: 4mm 4mm 2mm;font-size: 14pt;line-height: 18pt;">
          @if($data['meeting_title_en'] == null || empty($data['meeting_title_en'])  )       
             {{$data['meeting_title_ar']}}
        @else
             {{$data['meeting_title_en']}}    
        @endif
          </h3>
          <p class="" style="margin:0 4mm;">{{ Carbon\Carbon::parse($data['meeting_schedule_from'])->format('D, F d, Y, g:i A') }} - 
          {{ Carbon\Carbon::parse($data['meeting_schedule_to'])->format('D, F d, Y, g:i A') }} 
          @if($data['time_zone']['description_en'] == null || empty($data['time_zone']['description_en'])  )       
            ( {{$data['time_zone']['description_ar']}} )
        @else
            ( {{$data['time_zone']['description_en']}}  )  
        @endif
          </p>
          <p class="" style="margin: 1mm 4mm 4mm;">{{$data['meeting_code']}}</p>
        </div>
      </div>
    </header> -->
    <div style="clear:both;"></div>

    <div class="" style="page-break-inside: avoid;">
    Done -with help of Allah- holding of  @if($data['meeting_title_en'] == null || empty($data['meeting_title_en'])  )       
             {{$data['meeting_title_ar']}}
        @else
             {{$data['meeting_title_en']}}   
        @endif of {{$data['committee_name_en']? $data['committee_name_en'] : $data['committee_name_ar']}}
       on {{Carbon\Carbon::parse($data['meeting_schedule_from'])->format('l')}}  {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->format('d/m/Y') }} from {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->format('g:i A') }} to {{ Carbon\Carbon::parse($data['meeting_schedule_to'])->format('g:i A') }},
     in @if($data['meeting_venue_en'] == null || empty($data['meeting_venue_en'])  )       
             {{$data['meeting_venue_ar']}}
        @else
             {{$data['meeting_venue_en']}}    
        @endif,
        <div style="clear:both;"></div>
        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;">
        <span style="width:16%;" class="bold">
          <span style="margin: auto;">Headed By </span>
        </span>
        <span class="font-10" style="width: 80%;">
          <span>
            <span>
  
            @if($data['meeting_committee']['committee_head']['job_title_en'] != null && !empty($data['meeting_committee']['committee_head']['job_title_en'])  )
                  {{$data['meeting_committee']['committee_head']['job_title_en']}}
              @else
                @if($data['meeting_committee']['committee_head']['job_title_ar'] != null && !empty($data['meeting_committee']['committee_head']['job_title_ar'])  )
                    {{$data['meeting_committee']['committee_head']['job_title_ar']}}
                    @endif
          @endif

          @if($data['meeting_committee']['committee_head']['nickname_en'] != null && !empty($data['meeting_committee']['committee_head']['nickname_en'])  )
                  {{$data['meeting_committee']['committee_head']['nickname_en']}}
              @else
                @if($data['meeting_committee']['committee_head']['nickname_ar'] != null && !empty($data['meeting_committee']['committee_head']['nickname_ar'])  )
                    {{$data['meeting_committee']['committee_head']['nickname_ar']}}
                    @endif
           @endif
  
            @if($data['meeting_committee']['committee_head']['user_title_en'] != null && !empty($data['meeting_committee']['committee_head']['user_title_en'])  )
               {{$data['meeting_committee']['committee_head']['user_title_en']}}: 
              @else
                  {{$data['meeting_committee']['committee_head']['user_title_ar']}}: 
              @endif

              @if($data['meeting_committee']['committee_head']['name'] == null || empty($data['meeting_committee']['committee_head']['name'])  )       
                {{$data['meeting_committee']['committee_head']['name_ar']}}
            @else
                {{$data['meeting_committee']['committee_head']['name']}}  
            @endif
            </span>

   
            
          </span>
        </span>
      </div>
    

     <div style="clear:both;"></div>
     @if(count($data['meeting_participants'])) 
       <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;">
         <span style="width:16%;" class="bold">
           <span style="margin: auto;">In the presence of their Excellencies the following members of the Board of Directors: </span>
		 </span>
		 <br>
         <span class="font-10" style="width: 80%;">
           <span>
           @foreach ($data['meeting_participants'] as $indx=>$participant)
           <span>
           {{$indx +1 }}.
             @if($participant['user_title_en'] != null && !empty($participant['user_title_en'])  )
                    {{$participant['user_title_en']}}: 
               @else
                   {{$participant['user_title_ar']}}: 
               @endif
 
               @if($participant['name'] == null || empty($participant['name'])  )       
                 {{$participant['name_ar']}}
             @else
                 {{$participant['name']}}  
             @endif
             @if($participant['nickname_en'] != null && !empty($participant['nickname_en'])  )
                   ({{$participant['nickname_en']}})
               @else
                 @if($participant['nickname_ar'] != null && !empty($participant['nickname_ar'])  )
                     ({{$participant['nickname_ar']}})
                     @endif
           @endif
           <br>
           @if($participant['job_title_en'] != null && !empty($participant['job_title_en'])  )
                   {{$participant['job_title_en']}}
               @else
                 @if($participant['job_title_ar'] != null && !empty($participant['job_title_ar'])  )
                     {{$participant['job_title_ar']}}
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
  
      @if(isset($data['meeting_note_ar']) || isset($data['meeting_note_en']) )  
      <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;">
        <span style="width:16%;" class="bold">
          <span style="margin: auto;">Notes: </span>
        </span>
        <span class="font-10" style="width: 80%;">
        @if($data['meeting_note_en'] == null || empty($data['meeting_note_en'])  )       
             {{$data['meeting_note_ar']}}
        @else
             {{$data['meeting_note_en']}}    
        @endif
        </span>
      </div>
      @endif
    </div> 

    <div style="clear:both;min-height:1mm"></div>
    @if(count($data['meeting_agendas'])) 
    
    <div class="bold"  style="padding-top:2mm;font-weight: 600;">
      <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;">Agendas</h4>
      @foreach ($data['meeting_agendas'] as $agenda)
      <div class="clear page-break"
        style="page-break-inside: avoid;
        background: #fff;
        padding: 0 8px;
        padding: 2mm 2mm 0mm 3mm;
        margin-bottom: 3mm;
        clear: both;
        display: block;">
        <div style="text-align:left;border-bottom: 1px dashed #ddd;margin: 0mm 0 2mm;padding: 0mm 0 0;">
          <h4 style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 3mm 0 0mm;">
          
          @if($agenda['agenda_title_en'] == null || empty($agenda['agenda_title_en'])  )       
            {{$agenda['agenda_title_ar']}} 
            @else
            {{$agenda['agenda_title_en']}}
            @endif
            <span style="font-weight: 500;font-size: 11pt;line-height: 14pt;margin: 2mm 0 4mm;">({{$agenda['agenda_time_in_min']}} Min)</span>
          </h4>
        </div>

        <div style="clear:both;"></div>

        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
          <span style="width:16%;" class="bold">
            <span style="margin: auto;">Presenters:</span>
          </span>
          
          <span class="font-10" style="font-weight: 500;width: 80%;">
          <span>
          @foreach ($agenda['presenters_agenda'] as $agendaPresenter)
          <span> 
        
          @if($agendaPresenter['user_title_en'] != null && !empty($agendaPresenter['user_title_en'])  )
          {{$agendaPresenter['user_title_en']}}
              @else
                  {{$agendaPresenter['user_title_ar']}}
              @endif

              @if($agendaPresenter['name'] == null || empty($agendaPresenter['name'])  )       
                {{$agendaPresenter['name_ar']}}
            @else
                {{$agendaPresenter['name']}}  
            @endif
          @if($agendaPresenter['job_title_en'] != null && !empty($agendaPresenter['job_title_en'])  )
                  ({{$agendaPresenter['job_title_en']}})
              @else
                @if($agendaPresenter['job_title_ar'] != null && !empty($agendaPresenter['job_title_ar'])  )
                    ({{$agendaPresenter['job_title_ar']}})
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
            <span style="margin: auto;">Purpose: </span>
          </span>
          <span class="font-10" style="font-weight: 500;width: 80%;">
            @if($agenda['agenda_purpose']['purpose_name_en'] == null || empty($agenda['agenda_purpose']['purpose_name_en'])  )       
              {{ $agenda['agenda_purpose']['purpose_name_ar']}}
            @else
            {{ $agenda['agenda_purpose']['purpose_name_en'] }}
            @endif
           
          </span>
        </div>

        <div style="clear:both;"></div>

        @if(count($agenda['agenda_votes'])) 
        <div class="m-demo mb-2" style="border-top: 1px dashed #ddd;margin: 1mm 0 5mm;padding: 2mm 0 0;">
          <div class="m-demo__preview--btn"><!--class="m-demo__preview"-->
            <h5 style="margin: 0 0 2mm;background-color: #ddd;display: inline-block;padding: 0 2mm;line-height: 16px;">
              Decisions </h5>
              @foreach ($agenda['agenda_votes'] as $agendaVote)
            <p class="mt-0" style="color: #333;margin: 0;">
            @if($agendaVote['vote_subject_en'] == null || empty($agendaVote['vote_subject_en'])  )       
              {{ $agendaVote['vote_subject_ar']}}
            @else
              {{ $agendaVote['vote_subject_en'] }}
            @endif
            </p>
            <div class="btn-group" role="group">

              <label class="m-vote-btn-group">

                <label for="test1">
                  <i class="checkmark"></i>
                  Yes: </label>
                <span>{{$agendaVote['num_agree_votes']}}</span>
              </label>
              <label class="m-vote-btn-group">
                <label for="test2">
                  <i class="close black"></i>
                  No: </label>
                <span>{{$agendaVote['num_disagree_votes']}}</span>
              </label>
              <label class="m-vote-btn-group">
                <label for="test3">
                  <i class="circle"></i> Abstained: </label>
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
          <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;">Comments</h4>   
          @foreach (array_filter($agenda['agenda_user_comments'],function($item){return $item['is_organizer'] == true ; }) as $agendaUserComment)
          <div class="streamItem" style="background: #fff;-webkit-box-shadow: 0 1px 4px rgba(0,0,0,.04);box-shadow: 0 1px 4px rgba(0,0,0,.04);border: 1px solid rgba(0,0,0,.09);-webkit-border-radius: 3px;border-radius: 3px;padding: 15px 20px 18px;margin: 10px 0 10px;">
              <div class="cardChromeless u-marginTop20 u-paddingTop10 u-paddingBottom15 u-paddingLeft20 u-paddingRight20">
                <div class="postArticle postArticle--short js-postArticle js-trackPostPresentation js-trackPostScrolls">
                <div class="u-clearfix u-marginBottom15 u-paddingTop5">
                  <div class="postMetaInline u-floatLeft">
                  <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
          
          <div class="font-10" style="font-weight: 500;width: 80%!important;display:block;">
            <span style="clear: both;"> 
            @if($agendaUserComment['name'] == null || empty($agendaUserComment['name'])  )       
              {{ $agendaUserComment['name_ar']}}
            @else
            {{ $agendaUserComment['name']}}
            @endif</span>
            <br>
            <time datetime="2018-06-20T11:19:02.928Z" style="font-size: 12px;"> {{ Carbon\Carbon::parse($agendaUserComment['created_at'])->format('M d, Y, g:i A') }}</time>
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

      </div>

      @endforeach
     

    </div>
   
    <div style="clear:both;"></div>

   
  
    @endif
 

  </div>

  <!-- Page Template -->





</body>

</html>
