<div class="imagelogo" style="display: block;position: absolute;right: 40px;">
    <img style="width:7em!important;max-height:2em!important;max-width:7em;" src="{{ $data['meeting_mom_template_logo'] }}">
</div>
<p style="text-align:center;">
    @if($data['show_mom_header'] == 1)
        @if($data['meeting_title_en'] == null || empty($data['meeting_title_en'])  )       
             {{$data['meeting_title_ar']}}
        @else
            {{$data['meeting_title_en']}}
        @endif
    @endif
</p>
<div class="white-page mx-auto">
    <div style="clear:both;"></div>
    <div class="" style="page-break-inside: avoid; margin-top:2rem;">
        <p style="text-align:left;direction:ltr;">{!! $data["introduction_template_en"] !!}</p>
        <div style="clear:both;"></div>
        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;text-align:left;direction:ltr;">
            <span style="width:16%;" class="bold">
                <span style="margin: auto;text-align:left;direction:ltr;"><strong>Headed By</strong> </span>
            </span>
            <span class="font-10" style="width: 80%;text-align:left;direction:ltr;">
                <span>
                    <span>
                        @if($data['meeting_committee']['committee_head']['job_title_en'] != null && !empty($data['meeting_committee']['committee_head']['job_title_en'])  )
                            {{$data['meeting_committee']['committee_head']['job_title_en']}}
                        @else @if($data['meeting_committee']['committee_head']['job_title_ar'] != null && !empty($data['meeting_committee']['committee_head']['job_title_ar'])  )
                                {{$data['meeting_committee']['committee_head']['job_title_ar']}}
                            @endif
                        @endif
                        @if($data['meeting_committee']['committee_head']['nickname_en'] != null && !empty($data['meeting_committee']['committee_head']['nickname_en'])  )
                            {{$data['meeting_committee']['committee_head']['nickname_en']}}
                        @else @if($data['meeting_committee']['committee_head']['nickname_ar'] != null && !empty($data['meeting_committee']['committee_head']['nickname_ar'])  )
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
            <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;text-align:left;direction:ltr;">
                <span style="width:16%;" class="bold">
                    <span style="margin: auto;text-align:left;direction:ltr;"><strong>{!! $data["member_list_introduction_template_en"] !!} </strong></span>
                </span>
                <span class="font-10" style="width: 80%;text-align:left;direction:ltr;">
                    <ol style="text-align:left;direction:ltr;">
                        @foreach ($data['meeting_participants'] as $indx=>$participant)
                            <li style="text-align:left;direction:ltr;">
                                @if ($data['show_participant_title'] == 1)
                                @if($participant['user_title_en'] != null && !empty($participant['user_title_en'])  )
                                    {{$participant['user_title_en']}}: 
                                @else @if($participant['user_title_ar'] != null && !empty($participant['user_title_ar'])  )
                                    {{$participant['user_title_ar']}}: 
                                    @endif
                                @endif
                                @endif
                                @if($participant['name'] == null || empty($participant['name'])  )       
                                    {{$participant['name_ar']}}
                                @else
                                    {{$participant['name']}}  
                                @endif
                                @if ($data['show_participant_nickname'] == 1)
                                @if($participant['nickname_en'] != null && !empty($participant['nickname_en'])  )
                                    ({{$participant['nickname_en']}})
                                @else @if($participant['nickname_ar'] != null && !empty($participant['nickname_ar'])  )
                                        ({{$participant['nickname_ar']}})
                                    @endif
                                @endif
                                @endif
                                @if ($data['show_participant_job'] == 1)
                                <br>
                                @if($participant['job_title_en'] != null && !empty($participant['job_title_en'])  )
                                    {{$participant['job_title_en']}}
                                @else @if($participant['job_title_ar'] != null && !empty($participant['job_title_ar'])  )
                                        {{$participant['job_title_ar']}}
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
                            <br> guest
                            @endif
                            </li>
                        @endforeach
                    </ol>
                </span>
            </div>
        @endif
        <div style="clear:both;"></div>
        @if(isset($data['meeting_note_ar']) || isset($data['meeting_note_en']) ) 
            <br> 
            <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;margin-bottom: 2mm;">
                <span style="width:16%;" class="bold">
                    <span style="margin: auto;">Notes: </span>
                </span>
                <span class="font-10" style="width: 80%;text-align:left;direction:ltr;">
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
    @if(count($data['meeting_agendas'])  && $data['show_agenda_list'] == 1)
        <div class="bold"  style="padding-top:2mm;font-weight: 550;">
            <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;text-align:left;direction:ltr;">Agendas</h4>
            @foreach ($data['meeting_agendas'] as $agenda)
                <div class="clear page-break"
                    style="page-break-inside: avoid;
                    background: #fff;
                    padding: 0 8px;
                    padding: 2mm 2mm 0mm 3mm;
                    margin-bottom: 3mm;
                    clear: both;
                    display: block;text-align:left;direction:ltr;">
                    <div style="text-align:left;direction:ltr;">
                        <h4 style="font-weight: 500;font-size: 15pt;line-height: 14pt;margin: 3mm 0 0mm;text-align:left;direction:ltr;">
                            @if($agenda['agenda_title_en'] == null || empty($agenda['agenda_title_en'])  )       
                                {{$agenda['agenda_title_ar']}} 
                            @else
                                {{$agenda['agenda_title_en']}}
                            @endif
                            @if ($data['show_timer'] == 1)
                                <span style="font-weight: 500;font-size: 11pt;line-height: 14pt;margin: 2mm 0 4mm;">
                                    ({{$agenda['agenda_time_in_min']}} Min)</span>
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
                            <span style="margin: auto;"><strong>Presenters:</strong></span>
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
                                        @else @if($agendaPresenter['job_title_ar'] != null && !empty($agendaPresenter['job_title_ar'])  )
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
                    @endif
                    <div style="clear:both;"></div>
                    @if ($data['show_purpose'] == 1)
                    <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
                        <span style="width:16%;" class="bold">
                            <span style="margin: auto;"><strong>Purpose:</strong> </span>
                        </span>
                        <span class="font-10" style="font-weight: 500;width: 80%;">
                            @if($agenda['agenda_purpose']['purpose_name_en'] == null || empty($agenda['agenda_purpose']['purpose_name_en'])  )       
                                {{ $agenda['agenda_purpose']['purpose_name_ar']}}
                            @else
                                {{ $agenda['agenda_purpose']['purpose_name_en'] }}
                            @endif
                        </span>
                    </div>
                   @endif
                    <div style="clear:both;"></div>
                    @if(count($agenda['agenda_votes'])) 
                        <div class="m-demo mb-2" style="border-top: 1px dashed #ddd;margin: 1mm 0 5mm;padding: 2mm 0 0;">
                            <div class=" m-demo__preview--btn"><!--class="m-demo__preview"-->
                                <h5 style="margin: 0 0 2mm;background-color: #ddd;display: inline-block;padding: 0 2mm;line-height: 16px;text-align:left;direction:ltr;">
                                    <strong>Decisions</strong> </h5>
                                @foreach ($agenda['agenda_votes'] as $agendaVote)
                                    <p class="mt-0" style="color: #333;margin: 0;text-align:left;direction:ltr;">
                                        @if($agendaVote['vote_subject_en'] == null || empty($agendaVote['vote_subject_en'])  )       
                                            {{ $agendaVote['vote_subject_ar']}}
                                        @else
                                            {{ $agendaVote['vote_subject_en'] }}
                                        @endif
                                        @if($data['show_vote_status'] == 1)
                                            &nbsp;<span>({{$agendaVote['vote_result_status_name_en']? $agendaVote['vote_result_status_name_en'] : $agendaVote['vote_result_status_name_ar']}})</span>
                                        @endif
                                    </p>
                                    @if($data['show_vote_results'] == 1)
                                    <div style="text-align:left;direction:ltr;">
                                        <label style="margin: 0 2mm 0 0;">
                                            <label for="test1">
                                                <i class="checkmark"></i>
                                                Yes: </label>
                                            <span>{{$agendaVote['num_agree_votes']}}</span>
                                        </label>
                                        <label style="margin: 0 2mm 0 0;">
                                            <label for="test2">
                                                <i class="close black"></i>
                                                No: </label>
                                            <span>{{$agendaVote['num_disagree_votes']}}</span>
                                        </label>
                                        <label style="margin: 0 2mm 0 0;">
                                            <label for="test3">
                                                <i class="circle"></i> Abstained: </label>
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
                            <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;">Comments</h4>   
                            @foreach (array_filter($agenda['agenda_user_comments'],function($item){return $item['is_organizer'] == true ; }) as $agendaUserComment)
                                <div class="streamItem" style="background: #fff;-webkit-box-shadow: 0 1px 4px rgba(0,0,0,.04);box-shadow: 0 1px 4px rgba(0,0,0,.04);border: 1px solid rgba(0,0,0,.09);-webkit-border-radius: 3px;border-radius: 3px;padding: 15px 20px 18px;margin: 10px 0 10px;">
                                    <div class="cardChromeless u-marginTop20 u-paddingTop10 u-paddingBottom15 u-paddingLeft20 u-paddingRight20">
                                        <div class="postArticle postArticle--short js-postArticle js-trackPostPresentation js-trackPostScrolls">
                                            <div class="u-clearfix u-marginBottom15 u-paddingTop5">
                                                <div class="postMetaInline u-floatLeft">
                                                    <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;text-align:left;direction:ltr;">
                                                        <div class="font-10" style="font-weight: 500;width: 80%!important;display:block;">
                                                            <span style="clear: both;"> 
                                                                @if($agendaUserComment['name'] == null || empty($agendaUserComment['name'])  )       
                                                                    {{ $agendaUserComment['name_ar']}}
                                                                @else
                                                                    {{ $agendaUserComment['name']}}
                                                                @endif</span>
                                                            <br>
                                                            <time datetime="2018-06-20T11:19:02.928Z" style="font-size: 12px;text-align:left;direction:ltr;"> {{ Carbon\Carbon::parse($agendaUserComment['created_at'])->format('M d, Y, g:i A') }}</time>
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
                                                        <div class="section-content" style="text-align:left;direction:ltr;">
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
    <div style="clear:both;min-height:1mm"></div>

    @if($data['isRecommendationVisible'] && $data['show_recommendation'])
    <div class="bold" style="padding-top:2mm;font-weight: 600;">
    <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 6mm 0 3mm;">
        Recommendations
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
            Resposible Party
            </span>
        </span>
        <span class="font-10" style="font-weight: 500;width: 80%;">
            {{$recommend['responsible_party']}}
        </span>
        </div>
        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
        <span style="width:16%;" class="bold">
            <span style="margin: auto;">
            Resposible member
            </span>
        </span>
        <span class="font-10" style="font-weight: 500;width: 80%;">
            {{$recommend['responsible_user']}}
        </span>
        </div>
        <div class="list-row" style="page-break-inside: avoid;display:inline-block;width:100%;">
        <span style="width:16%;" class="bold">
            <span style="margin: auto;">
            Start Date
            </span>
        </span>
        <span class="font-10" style="font-weight: 500;width: 80%;">
            {{$recommend['recommendation_date']}}
        </span>
        </div>
    </div>
    @endforeach
    @else
    <h3>There is no recommendations</h3>
    @endif
    </div>
    @endif

    <div style="clear:both;min-height:1mm"></div>
    @if ($data['show_conclusion'] == 1)
        <h4 class="section-heading" style="font-weight: 600;font-size: 15pt;line-height: 14pt;margin: 9mm 0 3mm;text-align:left;direction:ltr;">Conclusion</h4>
        <p style="text-align:left;direction:ltr;">{!! $data["conclusion_template_en"] !!}</p>
    @endif
</div>