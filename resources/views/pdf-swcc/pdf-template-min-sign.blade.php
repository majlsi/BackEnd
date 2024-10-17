<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Saline Water Conversion Corporation (SWCC) - PDF</title>

  <!-- Custom styles for this template -->
  <link rel="stylesheet"  type="text/css" media="all" href="{{ asset('css/swcc/PDF.css') }}" >

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

body { font-family: XB Riyaz;
  text-align:left;
    direction:ltr;

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



<!-- signature -->
@foreach ($data['canSignParticipants']->chunk(8) as $key=>  $participantGroupPage)
@if($key > 0 )
<div class="new-page">
@endif

<table style="width:100%; margin:0 -10px" cellspacing="10" >
		<colgroup>
			<col span="2" style="background-color:#F9F9F9">

    </colgroup>

    @foreach ($participantGroupPage->chunk(2) as $participantGroup)
		<tr>
    @foreach ($participantGroup as $participant)
			<td style="width:50%;">
      <table style="width:100%;">
            <tr>
            <td>
            <b>
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
            </b>
          </td>
          </tr>
          <tr>
            <td>
            @if($participant['nickname_en'] != null && !empty($participant['nickname_en'])  )
                   ({{$participant['nickname_en']}})
               @else
                 @if($participant['nickname_ar'] != null && !empty($participant['nickname_ar'])  )
                     ({{$participant['nickname_ar']}})
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
  @if($key > 0 )
</div>
@endif      
@endforeach
<!-- signature -->

  </div>

  <!-- Page Template -->



</body>

</html>
