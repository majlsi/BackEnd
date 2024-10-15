<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Saline Water Conversion Corporation (SWCC) - PDF</title>
  
  <!-- Custom styles for this template -->
  <link rel="stylesheet"  type="text/css" media="all" href="{{ asset('css/swcc/PDF-ar.css') }}" >

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
body { font-family: XB Riyaz; }
    .new-page
    {
        display: block; page-break-before: always;
    }
    
</style>
</head>

<body>
    @php
        setlocale(LC_ALL, 'ar_AE.utf8');
    @endphp
    <htmlpageheader name="page-header">
        <div class="imagelogo" style="display: block;position: absolute;left: 40px;margin-top: 20pt;">
            <img style="width:7em!important;max-height:2em!important;max-width:7em;" src="{{ URL::asset('img/logo_swcc.png') }}">
        </div>
    </htmlpageheader>
    <div class="white-page mx-auto">

        <div style="clear:both;"></div>
        <h3 style="text-align: center;">إعدادات مايكروسوفت تيمز</h3>

       
    </div>
</body>

</html>