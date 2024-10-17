<!DOCTYPE html>
<html>
    <head>
        <title>Expired date for organizations</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
       

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato', sans-serif;
            }

            .container {
                text-align: left;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: left;
                display: inline-block;
            }

            .title {
                font-size: 72px;
                margin-bottom: 40px;
            }

            .content .rtl-content {
                text-align: right;

            }
            .padding-15{
                padding: 0 15px;
            }
            .organizations {
                border: 1px solid #ddd;
                border-spacing: 0;
                border-collapse: collapse;
            }
            .organizations th {
                padding: 5mm;
                text-align: right;
                background: rgb(249, 249, 249)!important;
                -webkit-print-color-adjust: exact;
                border: 1px solid #ddd;
            }
            .organizations td {
                background: #fff!important;
                -webkit-print-color-adjust: exact;
            }
        </style>


    </head>
    <body>  
        <div class="container" style="text-align: left; display: table; width:100%; vertical-align: middle">
            <div class="content"
                style="text-align: left;background-size: 100%;display:block;border-radius: 6px;border-width: 6px 1px 1px;border-style: solid;border-color: #017069;border-image: initial;padding: 25px 0px;margin: 0;box-shadow: rgba(0, 0, 0, 0.16) 0px 3px 6px;">
                <div class="ltr-content padding-15" style="padding: 0 15px;text-align: left;direction: ltr;">
                <img src="{{ URL::asset('img/logo-gaft.png') }}" style="max-width: 100px;margin: 26px 33px;">
                </div>
                <div class="rtl-content padding-15"
                    style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 400;font-size: 14px;text-align: right;direction: rtl;">
                    <p dir="RTL">
                        عزيزى السيد / السيدة
                        <b style="font-weight: 800;">{{$nameAr}}</b>,
                    </p>
                    <h2 style="font-size: 30px;font-weight: 600;margin-bottom: 20px;">
                     ادمن مجلسي!
                    </h2>
                    <p>
                       يوجد منشآت سوف تنتهى صلاحياتها قريبا.
                    </p>
                    <table class="organizations" style="width:100%; margin:0 -10px" cellspacing="10" >
                        @foreach ($expiresOrganizations as $organization)
                        <tr>
                            <td>{{$organization['organization_name_ar'] ? $organization['organization_name_ar'] : $organization['organization_name_en']}}</td>
                            <td> إنتهاء الصلاحية فى: {{$organization['expiry_date_to']}}</td>
                        </tr>
                        @endforeach
                    </table>
                   
                </div>
                <hr style="border: 0.5px solid #ddd;margin: 28px 0;">
                <div class="ltr-content padding-15" style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 500;font-size: 14px;">
                    <p>
                        Dear Ms. / Mr.
                        <b style="font-weight: 800;">{{$nameEn}}</b>,
                    </p>
                    <h2 style="font-size: 30px;font-weight: 600;margin-bottom: 13px;">Admin Mjlsi</h2>

                    <p>
                       There are some organizations that will expire soon.
                    </p>
                    <table class="organizations" style="width:100%; margin:0 -10px" cellspacing="10" >
                        @foreach ($expiresOrganizations as $organization)
                        <tr>
                            <td>{{$organization['organization_name_en'] ? $organization['organization_name_en'] : $organization['organization_name_ar']}}</td>
                            <td> Expire date: {{$organization['expiry_date_to']}}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
       
    </body>
</html>
