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
    .sub-title {
        text-decoration: underline;
        font-weight: bold;
        font-size:13px;
    }
    p {
        margin: 5px 5px 5px 5px
    }

</style>
</head>

<body>
    <htmlpageheader name="page-header">
        <div class="imagelogo" style="display: block;position: absolute;right: 40px;margin-top: 20pt;">
            <img style="width:7em!important;max-height:2em!important;max-width:7em;" src="{{ URL::asset('img/Mjlsi Logo-01.png') }}">
        </div>
    </htmlpageheader>
    <div class="white-page mx-auto">
        <div style="clear:both;"></div>
        <h3 style="text-align: center;">Microsoft teams documentation</h3>

        <div class="row">
            <h3 class="sub-title"> Sign up for Teams:</h3>
            <ol>
                <li>
                    Go to <a target="_blank" href="https://www.microsoft.com/en/microsoft-365/microsoft-teams/group-chat-software">Get Teams for free </a> and choose the Sign up for free button .                    
                </li>
                <li>
                    <p>
                        Enter your email address.
                    </p>
                    <p>
                        <img style="width:7em!important;max-height:35em!important;max-width:35em;" src="{{ URL::asset('img/first_step.png') }}">
                    </p>    
                </li>
                <li>
                    <p>
                        Select use teams for work and organizations .
                    </p>
                    <p>
                        <img style="width:7em!important;max-height:35em!important;max-width:35em;" src="{{ URL::asset('img/2_step.png') }}">
                    </p>
                </li>
                <li>
                    <p>
                        Enter your password .
                    </p>
                    <p>
                        <img style="width:7em!important;max-height:35em!important;max-width:35em;" src="{{ URL::asset('img/step_3.png') }}">
                    </p>
                </li>
                <li>
                    <p>
                        In this step insert your data.
                    </p>
                    <p>
                        <img style="width:7em!important;max-height:35em!important;max-width:35em;" src="{{ URL::asset('img/step_4.png') }}">
                    </p>
                    <p>
                        Now you have account on microsoft teams.
                    </p>
                </li>
            </ol>
            <h3 class="sub-title"> Sign in o the Azure portal:</h3>
            <ol>
                <li>
                    Sign in to the <a target="_blank" href="https://portal.azure.com/">Azure portal </a> using your work Microsoft account..                    
                </li>
                <li>
                    <p>
                        From left side menu select Azure Active Directory. Under Manage, select App registrations.
                    </p>
                    <p>
                        <img style="width:7em!important;max-height:15em!important;max-width:15em;" src="{{ URL::asset('img/step_5.png') }}">
                        <img style="width:7em!important;max-height:15em!important;max-width:15em;" src="{{ URL::asset('img/step_6.png') }}">
                    </p>
                </li>
                <li>
                    Select New registration.
                </li>
                <li>
                    <p>
                        In Register an application, enter a meaningful application name to display to users, select 
                        "Accounts in any organizational directory (Any Azure AD directory - Multitenant) and personal Microsoft accounts (e.g. Skype, Xbox)"
                        then edit redirect URI to be 'https://mjlsi.com/app/login'
                    </p>
                    <p>
                        <img style="width:7em!important;max-height40em!important;max-width:40em;" src="{{ URL::asset('img/step_7.png') }}">
                    </p>
                    <p>When finished, select Register.</p>
                </li>
                <li>
                    <p>
                        Now you need to add online meeting permissions for this application. for add pernission select API permissions 
                        , press into add a permission, from request API permissions press into Microsoft graph.
                    </p>
                    <p>
                        <img style="width:7em!important;max-height35em!important;max-width:35em;" src="{{ URL::asset('img/step_8.png') }}">
                    </p>
                    <p> Select application permissions and check all online meeting permissions then press add permissions</p>
                    <p>
                        <img style="width:7em!important;max-height35em!important;max-width:35em;" src="{{ URL::asset('img/step_9.png') }}">
                    </p>
                    <p> To grant admin permission to your account press grant admin consent for your account then prees 'Yes'</p>
                    <p>
                        <img style="width:7em!important;max-height35em!important;max-width:35em;" src="{{ URL::asset('img/step_10.png') }}">
                    </p>
                </li>
                <li>
                    <p>
                        To create client secret, press 'Certificates & secrets' then 'New client secret' add your description and select never for expires 
                    </p>
                    <p>
                        <img style="width:7em!important;max-height35em!important;max-width:35em;" src="{{ URL::asset('img/step_11.png') }}">
                    </p>
                    <p>
                        Copy this value and add it for client secret input in Mjlsi organization profile.
                    </p>
                    <p>
                        <img style="width:7em!important;max-height35em!important;max-width:35em;" src="{{ URL::asset('img/step_12.png') }}">
                    </p>
                </li>
                <li>
                    <p>
                        To get Application Id and Tenant id , press 'Overview'.
                    </p>
                    <p>
                        <img style="width:7em!important;max-height35em!important;max-width:35em;" src="{{ URL::asset('img/step_13.png') }}">
                    </p>
                </li>
                <li>
                    <p>
                        To get user object ID, From left side menu select Azure Active Directory. Under Manage, select Users. Then select your account
                    </p>
                    <p>
                        <img style="width:7em!important;max-height35em!important;max-width:35em;" src="{{ URL::asset('img/step_14.png') }}">
                    </p>
                </li>

            </ol>
        <div>
       
    </div>
</body>

</html>