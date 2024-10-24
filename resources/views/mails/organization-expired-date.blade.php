<!DOCTYPE html>
<html>
    <head>
        <title>Expired date of organization</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
       

        <style>
            html, body {
                height: 100%;
            }

            body {
                
            }

            .container {
                text-align: left;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: left;display: inline-block;border-radius: 6px;border: 1px solid #ececec;padding: 25px 0;margin: 15px;border-top: 6px solid #f5851f;box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.16);
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
    <body style="margin: 0;padding: 0;width: 100%;color: #333;display: table;font-weight: 100;
                font-family: Kawak, DIN NEXT,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Noto Sans,Ubuntu,Droid Sans,Helvetica Neue,sans-serif !important;">
       
       <div class="container" style="text-align: left; display: table-cell; vertical-align: middle">
            <div class="content" style="text-align: left;background-size: 100%;display: inline-block;border-radius: 6px;border-width: 6px 1px 1px;border-style: solid;border-color: rgb(245, 133, 31) rgb(236, 236, 236) rgb(236, 236, 236);border-image: initial;padding: 25px 0px;margin: 0;box-shadow: rgba(0, 0, 0, 0.16) 0px 3px 6px;">
                <div class="ltr-content padding-15" style="padding: 0 15px;text-align: left;direction: ltr;">
                    <img src="{{ URL::asset('img/logo.png') }}" style="max-width: 135px;margin: 26px 33px;">
                </div>
                @if($languageId == config('languages.ar'))
                <div class="rtl-content padding-15"
                    style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 400;font-size: 14px;text-align: right;direction: rtl;">
                    <p dir="RTL">
                        عزيزى السيد / السيدة
                        <b style="font-weight: 800;">{{$organization['system_admin_name_ar']}}</b>,
                    </p>
                    <p>
                        سوف تنتهى صلاحية المنشأة فى: {{$organization['expiry_date_to']}}
                    </p>
                    <br>
                    <p dir="RTL" style="font-weight: 700;margin-bottom: 10px;">
                    في حالة وجود أي استفسارات ، لا تتردد في الاتصال بنا على Support@mjlsi.com في أي وقت. كنا نحب أن نسمع منك.
                    </p>
                    <p dir="RTL" style="font-weight: 700;margin-bottom: 10px;">
                        شكرا جزيلا مع تحياتى,
                    </p>
                    <p dir="RTL" style="font-weight: 700;margin-top: 0;">
                        مسئول الدعم
                        <br>
                        فريق مجلسى
                    </p>
                   
                </div>
                @endif
                <!-- <hr style="border: 0.5px solid #ddd;margin: 28px 0;"> -->
                @if($languageId == config('languages.en'))
                <div class="ltr-content padding-15" style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 500;font-size: 14px;">
                    <p>
                        Dear Ms. / Mr.
                        <b style="font-weight: 800;">{{$organization['system_admin_name']}}</b>,
                    </p>
                    <h2 style="font-size: 30px;font-weight: 600;margin-bottom: 13px;">Admin Mjlsi</h2>

                    <p>
                       Your organization will expire at: {{$organization['expiry_date_to']}}
                    </p>
                    <br style="height: 12px;">

                    <p style="font-weight: 700;margin-bottom: 10px;">
                    In case you have any inquiries , don’t hesitate to contact us at Support@mjlsi.com any time. We would love to hear from you.
                    </p>
                    <p style="font-weight: 700;margin-bottom: 10px;">
                        Thanks and Regards,
                    </p>
                    <p style="font-weight: 700;margin-top: 0;">

                        Customer Support Representative,
                        <br>
                        Mjlsi Team
                    </p>
                </div>
                @endif
            </div>
        </div>
       
    </body>
</html>
