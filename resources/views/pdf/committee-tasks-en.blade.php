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
    /* margin: 0px;
    margin: 0px 0px 0px 0px !important; */
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
	background-image: url({{ URL::asset('img/bg-1.png') }});
/* 	background-position ... */
/* 	background-repeat  no-repeat; */
/* 	background-color ...
	background-gradient: ... */
}
  @page{
    border-top: 2mm solid #f6861f;
    header: page-header;
    margin-top: 70pt;
  }

/* body { margin: 0px; } */
.white-bg{
    width: 190mm;
 padding: 0;
 background-color: #333;
 border-top: 2mm solid #f6861f;
}
body { font-family: XB Riyaz; 
    text-align:left;
    direction:ltr;}
    .new-page
    {
        display: block; page-break-before: always;
    }
    td {
        padding: 5mm;
        text-align: right;
        border-radius: 30mm!important;
        background: rgb(249, 249, 249)!important;
        -webkit-print-color-adjust: exact;
        border: 1px solid #ddd;
    }
    td table td {
        padding:0;
        padding-bottom: 2mm;
    }
    .tasks {
        border: 1px solid #ddd;
        border-spacing: 0;
        border-collapse: collapse;
    }
    .tasks th {
        padding: 5mm;
        text-align: right;
        background: rgb(249, 249, 249)!important;
        -webkit-print-color-adjust: exact;
        border: 1px solid #ddd;
        text-align:left;
        direction:ltr;
    }
    .tasks td {
        background: #fff!important;
        -webkit-print-color-adjust: exact;
        text-align:left;
        direction:ltr;
    }
    .committee {
        /* margin-top: 40pt; */
    }
</style>
</head>

<body>
    <htmlpageheader name="page-header">
        <div class="col" style="display: block;position: absolute;left: 40px;margin-top: 20pt;font-size: 11pt;">
                
            @if($data['organization']['organization_name_en'] == null || empty($data['organization']['organization_name_en'])  )
                {{$data['organization']['organization_name_ar']}}
            @else
                {{$data['organization']['organization_name_en']}}
            @endif
                
        </div>
        <div class="imagelogo" style="display: block;position: absolute;right: 40px;margin-top: 20pt;">
            <img style="width:7em!important;max-height:2em!important;max-width:7em;" src="{{ URL::asset('img/Mjlsi Logo-01.png') }}">
        </div>
    </htmlpageheader>
    <div class="white-page mx-auto">


        <div style="clear:both;"></div>

        @foreach ($data['committees'] as $indx=>$committee)
        @if($indx > 0)
        <div class="new-page"></div>
        @endif
        <div class="committee">
            <h3> Committee : {{isset($committee['committee_name_en']) && $committee['committee_name_en'] ? $committee['committee_name_en'] : $committee['committee_name_ar']}}</h3>
            <div>

            <table class="tasks_count" style="width:100%; margin:0 -10px" cellspacing="10" >
                <colgroup>
			              <col span="2" style="background-color:#F9F9F9">
                </colgroup>
                <tr>
                    <td style="text-align:center;width:25%;">
                        <div class="icon">
                            <img style="width:3em!important;max-height:0.5em!important;max-width:3em;" src="{{ URL::asset('img/file.png') }}">
                        </div>
                        <br>
                        <span> New Tasks: &nbsp;{{$committee['new_tasks']}}</span>
                    </td>
                    <td style="text-align:center;width:25%;">
                        <div class="icon">
                            <img style="width:3em!important;max-height:0.5em!important;max-width:3em;" src="{{ URL::asset('img/in-progress.png') }}">
                        </div>
                        <br>
                        <span> In Progress Tasks: &nbsp;{{$committee['progress_tasks']}}</span>
                    </td>
                    <td style="text-align:center;width:25%;">
                        <div class="icon">
                            <img style="width:3em!important;max-height:0.5em!important;max-width:3em;" src="{{ URL::asset('img/check.png') }}">
                        </div>
                        <br>
                        <span> Done Tasks: &nbsp;{{$committee['done_tasks']}}</span>
                    </td>
                    <td style="text-align:center;width:25%;">
                        <div class="icon">
                            <img style="width:3em!important;max-height:0.5em!important;max-width:3em;" src="{{ URL::asset('img/list.png') }}">
                        </div>
                        <br>
                        <span> Total of Tasks: &nbsp;{{$committee['done_tasks'] + $committee['progress_tasks'] + $committee['new_tasks']}}</span>
                    </td>
                </tr>
            </table>
            <table class="tasks_count" style="width:100%; margin:0 -10px" cellspacing="10" >
                <colgroup>
			              <col span="2" style="background-color:#F9F9F9">
                </colgroup>
                <tr>
                    <td style="text-align:center;width:25%;">
                        <div class="icon">
                            <img style="width:3em!important;max-height:0.5em!important;max-width:3em;" src="{{ URL::asset('img/file.png') }}">
                        </div>
                        <br>
                        <span> Delay tasks: &nbsp;{{$committee['delay_tasks']}}</span>
                    </td>
                    <td style="text-align:center;width:25%;">
                        <div class="icon">
                            <img style="width:3em!important;max-height:0.5em!important;max-width:3em;" src="{{ URL::asset('img/in-progress.png') }}">
                        </div>
                        <br>
                        <span> Week tasks: &nbsp;{{$committee['tasks_of_week']}}</span>
                    </td>
                    <td style="text-align:center;width:25%;">
                        <div class="icon">
                            <img style="width:3em!important;max-height:0.5em!important;max-width:3em;" src="{{ URL::asset('img/check.png') }}">
                        </div>
                        <br>
                        <span> Month tasks: &nbsp;{{$committee['tasks_of_month']}}</span>
                    </td>
                    <td style="text-align:center;width:25%;">
                        <div class="icon">
                            <img style="width:3em!important;max-height:0.5em!important;max-width:3em;" src="{{ URL::asset('img/list.png') }}">
                        </div>
                        <br>
                        <span> Later tasks: &nbsp;{{$committee['later_tasks']}}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div style="clear:both;"></div>
        <br>

            <table class="tasks" style="width:100%; margin:0 -10px" cellspacing="10" >
                <tr>
                    <th style="width:20%;"> Task Description </th>
                    <th style="width:20%;"> Task Code </th>
                    <th style="width:20%;"> Assigned To </th>
                    <th style="width:20%;">Task Start Date </th>
                    <th style="width:20%;">Status</th>
                </tr>
                @foreach ($committee['tasks'] as $task)
                <tr>
                    <td style="width:20%;">{{$task['description']}}</td>
                    <td style="width:20%;">{{$task['serial_number']}}</td>
                    <td style="width:20%;">
                        @if($task['user_title_ar'] || $task['user_title_en'])
                        <span>{{isset($task['user_title_en']) && $task['user_title_en'] ? $task['user_title_en'] : $task['user_title_ar']}} /&nbsp;</span>
                        @endif
                        <span>{{isset($task['user_name']) && $task['user_name'] ? $task['user_name'] : $task['user_name_ar']}}</span>
                        <br>
						<span>{{isset($task['job_title_en'])? $task['job_title_en'] : $task['job_title_ar']}}</span>
                    </td>
                    <td style="width:20%;">{{Carbon\Carbon::parse($task['start_date'])->formatLocalized('%B %d, %Y')}}</td>
                    <td style="width:20%;">{{$task['task_status_name_en']}}</td>
                </tr>
                @endforeach
            </table>
        @endforeach
    </div>
</body>

</html>