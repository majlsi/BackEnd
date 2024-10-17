
@php
    setlocale(LC_ALL, 'ar_AE.utf8');
@endphp
<div class="imagelogo" style="display: block;position: absolute;left: 40px;">
    <img style="width:7em!important;max-height:2em!important;max-width:7em;" src="{{ $data['meeting_mom_template_logo'] }}">
</div>
<p style="text-align:center;">
    @if($data['show_mom_header'] == 1)
        {{$data['meeting_title_ar']? $data['meeting_title_ar'] : $data['meeting_title_en']}}
    @endif
</p>
<div class="white-page mx-auto">
    <div style="clear:both;"></div>
    <div style="clear:both;"></div>
    <div class="" style="page-break-inside: avoid; margin-top:2rem;">
        <p style="text-align:right;direction:rtl;">{!! $data["introduction_template_ar"] !!}</p>
        <div style="clear:both;"></div>
        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;text-align:right;direction:rtl;">
            <span style="width:16%;" class="bold">
                <span style="margin: auto;text-align:right;direction:rtl;"><strong> وبرئاسة </strong></span>
            </span>
            <span class="font-10" style="width: 80%;text-align:right;direction:rtl;">
                <span>
                    <span>
                        @if($data['meeting_committee']['committee_head']['job_title_ar'] != null && !empty($data['meeting_committee']['committee_head']['job_title_ar'])  )
                            {{$data['meeting_committee']['committee_head']['job_title_ar']}}
                        @else @if($data['meeting_committee']['committee_head']['job_title_en'] != null && !empty($data['meeting_committee']['committee_head']['job_title_en'])  )
                            {{$data['meeting_committee']['committee_head']['job_title_en']}}
                            @endif
                        @endif
                        @if($data['meeting_committee']['committee_head']['nickname_ar'] != null && !empty($data['meeting_committee']['committee_head']['nickname_ar'])  )
                            {{$data['meeting_committee']['committee_head']['nickname_ar']}}
                        @else @if($data['meeting_committee']['committee_head']['nickname_en'] != null && !empty($data['meeting_committee']['committee_head']['nickname_en'])  )
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
            <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;text-align:right;direction:rtl;">
                <span style="width:16%;" class="bold">
                <span style="margin: auto;text-align:right;direction:rtl;">  <strong>{!! $data["member_list_introduction_template_ar"] !!}</strong></span>
                </span>
                <span class="font-10" style="width: 80%;text-align:right;direction:rtl;">
                    <ol style="text-align:right;direction:rtl;">
                        @foreach ($data['meeting_participants'] as $indx=>$participant)
                            <li style="text-align:right;direction:rtl;">
                                @if ($data['show_participant_title'] == 1)
                                @if($participant['user_title_ar'] != null && !empty($participant['user_title_ar'])  )
                                    {{$participant['user_title_ar']}}: 
                                @else @if($participant['user_title_en'] != null && !empty($participant['user_title_en'])  )
                                    {{$participant['user_title_en']}}: 
                                    @endif
                                @endif
                                @endif
                                @if($participant['name_ar'] == null || empty($participant['name_ar'])  )
                                    {{$participant['name']}}
                                @else
                                    {{$participant['name_ar']}}
                                @endif
                                @if ($data['show_participant_nickname'] == 1)
                                @if($participant['nickname_ar'] != null && !empty($participant['nickname_ar'])  )
                                    ({{$participant['nickname_ar']}})
                                @else @if($participant['nickname_en'] != null && !empty($participant['nickname_en'])  )
                                    ({{$participant['nickname_en']}})
                                    @endif
                                @endif
                                @endif
                                @if ($data['show_participant_job'] == 1)
                                <br>
                                @if($participant['job_title_ar'] != null && !empty($participant['job_title_ar'])  )
                                    {{$participant['job_title_ar']}}
                                @else @if($participant['job_title_en'] != null && !empty($participant['job_title_en'])  )
                                    {{$participant['job_title_en']}}
                                    @endif
                                @endif
                                @endif  
                            </li>
                        @endforeach
                        @foreach ($data['guests'] as $guest)
                            <li style="text-align:right;direction:rtl;">
                            @if ($data['show_participant_title'] == 1)
                            {{ $guest['full_name'] ?? $guest['email'] }}
                            @endif
                            @if ($data['show_participant_job'] == 1)
                            <br> ضيف
                            @endif
                            </li>
                        @endforeach
                    </ol>
                </span>
            </div>
        @endif
        <div style="clear:both;"></div>
        @if(isset($data['meeting_note_ar']) )
            <br>
            <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;">
                <span style="width:16%;" class="bold">
                    <span style="margin: auto;;text-align:right;">ملاحظات اﻷجتماع</span>
                </span>
                <span class="font-10" style="width: 80%;;text-align:right;direction:rtl;">
                    {{$data['meeting_note_ar']}}
                </span>
            </div>
        @endif
    </div>
    <div style="clear:both;min-height:1mm"></div>
    @if(count($data['meeting_agendas']) && $data['show_agenda_list'] == 1)
        <div class="bold" style="padding-top:2mm;font-weight: 550;">
            <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;text-align:right;direction:rtl;">جدول اﻷعمال</h4>
                @foreach ($data['meeting_agendas'] as $agenda)
                    <div  class="clear page-break"
                        style="page-break-inside: avoid;
                        background: #fff;
                        padding: 0 8px;
                        padding: 2mm 2mm 0mm 3mm;
                        margin-bottom: 3mm;
                        clear: both;
                        display: block;text-align:right;direction:rtl;">
                        <div style="text-align:right;direction:rtl;">
                            <h4 style="font-weight: 500;font-size: 15pt;line-height: 14pt;margin: 3mm 0 5mm;text-align:right;direction:rtl;">
                                {{$agenda['agenda_title_ar']? $agenda['agenda_title_ar'] : $agenda['agenda_title_en']}}
                                @if ($data['show_timer'] == 1)
                                    <span style="font-weight: 500;font-size: 11pt;line-height: 14pt;margin: 2mm 0 4mm;">
                                    (دقيقة {{$agenda['agenda_time_in_min']}}) </span>
                                @endif
                            </h4>
                        </div>
                        <div style="clear:both;"></div>
                        @if (($data['show_presenters']  == 1 )  || ($data['show_purpose'] == 1))
                            <div style="border-bottom: 1px dashed #ddd;margin: 0mm 0 2mm;padding: 0mm 0 0;">
                            </div>
                        @endif
                        @if ($data['show_presenters'] == 1)
                        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
                            <span style="width:16%;" class="bold">
                                <span style="margin: auto;"><strong>المقدمين</strong></span>
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
                                            @else @if($agendaPresenter['job_title_en'] != null && !empty($agendaPresenter['job_title_en'])  )
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
                        @endif
                        <div style="clear:both;"></div>
                        @if ($data['show_purpose'] == 1)
                        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
                            <span style="width:16%;" class="bold">
                                <span style="margin: auto;"><strong>الهدف</strong></span>
                            </span>
                            <span class="font-10" style="font-weight: 500;width: 80%;">
                                {{ $agenda['agenda_purpose']['purpose_name_ar']}}
                            </span>
                        </div>
                        @endif
                        <div style="clear:both;"></div>
                        @if(count($agenda['agenda_votes'])) 
                            <div class="m-demo mb-2" style="border-top: 1px dashed #ddd;margin: 1mm 0 5mm;padding: 2mm 0 0;">
                                <div class=" m-demo__preview--btn"><!--class="m-demo__preview"-->
                                    <h5 style="margin: 0 0 2mm;background-color: #ddd;display: inline-block;padding: 0 2mm;line-height: 16px;text-align:right;direction:rtl;">
                                        <strong> القرارات </strong></h5>
                                    @foreach ($agenda['agenda_votes'] as $agendaVote)
                                        <p class="mt-0" style="color: #333;margin: 0;text-align:right;direction:rtl;">
                                            {{ $agendaVote['vote_subject_ar']? $agendaVote['vote_subject_ar'] : $agendaVote['vote_subject_en']}}
                                            @if($data['show_vote_status'] == 1)
                                            &nbsp;<span>({{$agendaVote['vote_result_status_name_ar']? $agendaVote['vote_result_status_name_ar'] : $agendaVote['vote_result_status_name_en']}})</span>
                                            @endif
                                        </p>
                                        @if($data['show_vote_results'] == 1)
                                        <div style="text-align:right;direction:rtl;">
                                            <label style="margin: 0 0 0 4mm;min-width:10px;">
                                                <label for="test1">
                                                    <i class="checkmark"></i>
                                                    نعم: </label>
                                                <span>{{$agendaVote['num_agree_votes']}}</span>
                                            </label>
                                            <label style="margin: 0 4mm 0 4mm;min-width:50px;">
                                                <label for="test2">
                                                    <i class="close black"></i>
                                                    لا: </label>
                                                <span>{{$agendaVote['num_disagree_votes']}}</span>
                                            </label>
                                            <label style="margin: 0 4mm 0 4mm;min-width:120px;">
                                                <label for="test3">
                                                    <i class="circle"></i> امتنع عن اخذ قرار: </label>
                                                <span>
                                                    {{$agendaVote['num_abstained_votes']}}
                                                </span>
                                            </label>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if(count(array_filter($agenda['agenda_user_comments'],function($item){return $item['is_organizer'] == true ; })))  
                        <div class="bold" style="padding-top:2mm;font-weight: 600;padding-bottom: 10px;">
                            <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;">التعليقات</h4>   
                            @foreach (array_filter($agenda['agenda_user_comments'],function($item){return $item['is_organizer'] == true ; }) as $agendaUserComment)
                                    <div class="streamItem" style="background: #fff;-webkit-box-shadow: 0 1px 4px rgba(0,0,0,.04);box-shadow: 0 1px 4px rgba(0,0,0,.04);border: 1px solid rgba(0,0,0,.09);-webkit-border-radius: 3px;border-radius: 3px;padding: 15px 20px 18px;margin: 10px 0 10px;">
                                        <div class="cardChromeless u-marginTop20 u-paddingTop10 u-paddingBottom15 u-paddingLeft20 u-paddingRight20">
                                            <div class="postArticle postArticle--short js-postArticle js-trackPostPresentation js-trackPostScrolls">
                                                <div class="u-clearfix u-marginBottom15 u-paddingTop5">
                                                    <div class="postMetaInline u-floatLeft">
                                                        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;text-align:right;direction:rtl;">
                                                            <div class="font-10" style="font-weight: 500;width: 80%;">
                                                                <span style="clear: both;">
                                                                    @if($agendaUserComment['name_ar'] == null || empty($agendaUserComment['name_ar'])  )       
                                                                        {{ $agendaUserComment['name']}}
                                                                    @else
                                                                        {{ $agendaUserComment['name_ar']}}
                                                                    @endif
                                                                </span>
                                                                <br>                
                                                                <time datetime="2018-06-20T11:19:02.928Z" style="font-size: 12px;text-align:right;direction:rtl;">
                                                                    {{ Carbon\Carbon::parse($agendaUserComment['created_at'])->format('M d, Y, g:i A') }}
                                                                </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="postArticle-content js-postField">
                                                <section class="section section--body section--first section--last">
                                                    <div class="section-divider">
                                                        <div style="clear:both;height: 5px;"></div>
                                                    </div>
                                                    <div class="section-content" style="text-align:right;direction:rtl;">
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
                            @endforeach
                        </div>
                        @endif
                    </div>
                @endforeach
        </div>
        <div style="clear:both;"></div>
    @endif
    <div style="clear:both;min-height:1mm"></div>

    @if($data['isRecommendationVisible'] && $data['show_recommendation'])
    <div class="bold" style="padding-top:2mm;font-weight: 600;">
    <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;">
        التوصيات
    </h4>
    @if(count($data['meeting_recommendations']))
    @foreach ($data['meeting_recommendations'] as $recommend)
    <div class="clear page-break" style="page-break-inside: avoid;background: #fff;padding: 0 8px;
            padding: 2mm 2mm 0mm 3mm;margin-bottom: 3mm;clear: both;display: block;">
        <div style="text-align:right;">
        <h4 style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 3mm 0 5mm;">
            {{$recommend['recommendation_text']}}
        </h4>
        </div>
        <div style="clear:both;"></div>
        <div style="border-bottom: 1px dashed #ddd;margin: 0mm 0 2mm;padding: 0mm 0 0;"></div>
        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
        <span style="width:16%;" class="bold">
            <span style="margin: auto;">
            الجهة الملزمة
            </span>
        </span>
        <span class="font-10" style="font-weight: 500;width: 80%;">
            {{$recommend['responsible_party']}}
        </span>
        </div>
        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
        <span style="width:16%;" class="bold">
            <span style="margin: auto;">
            المسؤول
            </span>
        </span>
        <span class="font-10" style="font-weight: 500;width: 80%;">
            {{$recommend['responsible_user']}}
        </span>
        </div>
        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
        <span style="width:16%;" class="bold">
            <span style="margin: auto;">
            تاريخ التنفيذ
            </span>
        </span>
        <span class="font-10" style="font-weight: 500;width: 80%;">
            {{$recommend['formatted_recommendation_date']}}
        </span>
        </div>
    </div>
    @endforeach
    @else
    <h3>لا يوجد توصيات</h3>
    @endif
    </div>
    @endif

    <div style="clear:both;min-height:1mm"></div>
    @if ($data['show_conclusion'] == 1)
        <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 9mm 0 3mm;text-align:right;direction:rtl;">الخاتمة</h4>
        <p style="text-align:right;direction:rtl;">
            {!! $data["conclusion_template_ar"] !!}
        </p>
    @endif
</div>