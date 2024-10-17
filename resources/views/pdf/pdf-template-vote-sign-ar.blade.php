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
    height: 30mm;
    padding: 0;
    background-color: #fff;
}
body {
  font-family: XB Riyaz;

  }

		th,td {
			padding: 20px;
			text-align: right;
			border-radius: 10px;
		}


  @media print{
            body {
                -webkit-print-color-adjust: exact;
            }
            td {
                padding: 5mm;
                text-align: right;
                border-radius: 30mm!important;
                background: rgb(249, 249, 249)!important;
                -webkit-print-color-adjust: exact;
            }
            td table td {
              padding:0;
              padding-bottom: 2mm;
            }

            .new-page
              {
              display: block; page-break-before: always;
              }

        }
</style>
</head>

<body>
@php
          setlocale(LC_ALL, 'ar_AE.utf8');
@endphp

<div class="imagelogo" style="display: block;position: absolute;left: 40px;">
          <img style="width:7em!important;max-height:2em!important;max-width:7em;border-radius: 50%;" src="{{ URL::asset('img/Mjlsi Logo-01.png') }}">
 </div>
          <div style="text-align: center;padding-top:2mm;">
            <a class="col" style="font-size: 11pt;">
            @if($organization['organization_name_ar'] == null || empty($organization['organization_name_ar'])  )
                {{$organization['organization_name_en']}}
            @else
                {{$organization['organization_name_ar']}}
             @endif
            </a>
          </div>
    <div class="mx-auto">

     <div style="clear:both;"></div>
        <h3 style="text-align: center;">
            @if($data['vote_subject_ar'] == null || empty($data['vote_subject_ar'])  )
                {{$data['vote_subject_en']}}
            @else
                {{$data['vote_subject_ar']}}
             @endif
        </h3>

        <p class="mt-0" style="color: #333;margin: 0;">
              {{$data['vote_description']}}
        </p>
        <br>
        <span style="width:16%;" class="bold">
          <span style="margin: auto;">   التصويت سيكون فى خلال الفترة:</span>
        </span>
        <br><br>

        <p class="mt-0" style="color: #333;margin: 0;">
          من {{ Carbon\Carbon::parse($data['vote_schedule_from'])->format('M d, Y, g:i A') }} إلى {{ Carbon\Carbon::parse($data['vote_schedule_to'])->format('M d, Y, g:i A') }}
        </p>

    </div>
    <br>
    <div style="clear:both;"></div>
    @if(count($data['voters']))
      <div class="list-row" style="page-break-inside: avoid;width:100%;margin-bottom: 2mm;">
        <span style="width:16%;" class="bold">
          <span style="margin: auto;"> أصحاب المعالي والسعادة أعضاء مجلس الإدارة المنوط بهم التصويت:</span>
        </span>
        <br>
        <span class="font-10" style="width: 80%;">
          <span>
          @foreach ($data['voters'] as $indx=>$voterPerson)
          <span>
            {{$indx +1 }}.
          @if($voterPerson['user_title_ar'] != null && !empty($voterPerson['user_title_ar'])  )
          {{$voterPerson['user_title_ar']}}: 
              @else
                  {{$voterPerson['user_title_en']}}: 
              @endif

              @if($voterPerson['name_ar'] == null || empty($voterPerson['name_ar'])  )
                 {{$voterPerson['name']}}
             @else
                 {{$voterPerson['name_ar']}}
             @endif

           @if($voterPerson['nickname_ar'] != null && !empty($voterPerson['nickname_ar'])  )
                  ({{$voterPerson['nickname_ar']}})
              @else
                @if($voterPerson['nickname_en'] != null && !empty($voterPerson['nickname_en'])  )
                    ({{$voterPerson['nickname_en']}})
                    @endif
          @endif
      <br>
          @if($voterPerson['job_title_ar'] != null && !empty($voterPerson['job_title_ar'])  )
                  {{$voterPerson['job_title_ar']}}
              @else
                @if($voterPerson['job_title_en'] != null && !empty($voterPerson['job_title_en'])  )
                    {{$voterPerson['job_title_en']}}
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
<!-- signature -->
@foreach ($data['voters']->chunk(8) as $key=>  $voter)

<div class="new-page">


<table style="width:100%; margin:0 -10px" cellspacing="10" >
		<colgroup>
			<col span="2" style="background-color:#F9F9F9">

    </colgroup>

    @foreach ($voter->chunk(2) as $usersGroup)
		<tr>
    @foreach ($usersGroup as $user)
			<td style="width:50%;">
      <table style="width:100%;">
            <tr>
            <td>
            <b>
            @if($user['user_title_ar'] != null && !empty($user['user_title_ar'])  )
            {{$user['user_title_ar']}}:
            @else
                {{$user['user_title_en']}}:
            @endif

            @if($user['name_ar'] == null || empty($user['name_ar'])  )
                 {{$user['name']}}
             @else
                 {{$user['name_ar']}}
             @endif
            </b>
          </td>
          </tr>
          <tr>
            <td>
            @if($user['nickname_ar'] != null && !empty($user['nickname_ar'])  )
                {{$user['nickname_ar']}}
            @else
              @if($user['nickname_en'] != null && !empty($user['nickname_en'])  )
                  {{$user['nickname_en']}}
                  @endif
          @endif
          </td>
          </tr>

          <tr>
            <td bgcolor="#FF0000" class="white-bg">
            <div class="light-card">

            </div>
          </td>
          </tr>
      </table>
      </td>
      @endforeach


		</tr>
		@endforeach

  </table>
  @if($key > 0 ){
</div>
}
@endif
      @endforeach
<!-- signature -->
</div>
  <!-- Page Template -->




</body>

</html>
