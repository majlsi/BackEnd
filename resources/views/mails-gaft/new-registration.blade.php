<!DOCTYPE html>
<html>
    <head>
        <title>New Registration</title>

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
        </style>


    </head>
    <body style="margin: 0;padding: 0;width: 100%;color: #333;display: table;font-weight: 100;
                font-family: Kawak, DIN NEXT,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Noto Sans,Ubuntu,Droid Sans,Helvetica Neue,sans-serif !important;">
       
       <div class="container" style="text-align: left; display: table-cell; vertical-align: middle">
            <div class="content" style="text-align: left;background-size: 100%;display: inline-block;border-radius: 6px;border-width: 6px 1px 1px;border-style: solid;border-color: #017069!important ;border-image: initial;padding: 25px 0px;margin: 0;box-shadow: rgba(0, 0, 0, 0.16) 0px 3px 6px;">
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
                    يوجد طلب تسجيل جديد من "{{$organizatioNameAr? $organizatioNameAr : $organizatioNameEn}}".
                    </p>
                </div>
                <hr style="border: 0.5px solid #ddd;margin: 28px 0;">
                <div class="ltr-content padding-15" style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 500;font-size: 14px;">
                    <p>
                        Dear Ms. / Mr.
                        <b style="font-weight: 800;">{{$nameEn}}</b>,
                    </p>
                    <h2 style="font-size: 30px;font-weight: 600;margin-bottom: 13px;">Admin Mjlsi!</h2>

                    <p>
                        There is a new registration request from "{{$organizatioNameEn? $organizatioNameEn : $organizatioNameAr}}".
                    </p>
                </div>
            </div>
        </div>
       
    </body>
</html>
