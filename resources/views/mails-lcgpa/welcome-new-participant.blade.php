<!DOCTYPE html>
<html>
    <head>
        <title>Reset Password</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #333;
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
        </style>
    </head>
    <body>
    <div class="container" style="text-align: left; display: table-cell; vertical-align: middle">
        <div class="content"
            style="text-align: left;background-size: 100%;display: inline-block;border-radius: 6px;border-width: 6px 1px 1px;border-style: solid;border-color: #34607B rgb(236, 236, 236) rgb(236, 236, 236);border-image: initial;padding: 25px 0px;margin: 0;box-shadow: rgba(0, 0, 0, 0.16) 0px 3px 6px;">
            <div class="ltr-content padding-15" style="padding: 0 15px;text-align: left;direction: ltr;">
                <img src="{{ URL::asset('img/logo-lcgpa.png') }}" style="max-width: 135px;margin: 26px 33px;">
            </div>
            <div class="rtl-content padding-15"
                    style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 400;font-size: 14px;text-align: right;direction: rtl;">
                    <p dir="RTL">
                        عزيزى السيد / السيدة
                        <b style="font-weight: 800;">{{$nameAr}}</b>,
                    </p>
                    <h2 style="font-size: 30px;font-weight: 600;margin-bottom: 20px;">
                        مرحبا بك في مجلسي!
                    </h2>
                    <p dir="RTL">
                        شكرا جزيلا لاهتمامك وثقتك بنا.
                    </p>
                    <p>
                        لقد قامت مؤسستك {{$organizationNameAr}} بتسجيل حسابك بنجاح.
                    </p>
                    <br>
                  
                <p dir="RTL">
                    ببساطة عليك فتح الرابط أدناه لتفعيل حسابك و لتغيير كلمة المروو الخاصة بك:
                </p>
                <a dir="RTL"
                    href="{{ config('resetpassword.url_frontend') }}/reset-password?token={{$token}}">{{ config('resetpassword.url_frontend') }}/reset-password?token={{$token}}</a>

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
          
            <hr style="border: 0.5px solid #ddd;margin: 28px 0;">
            <div class="ltr-content padding-15"
                style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 500;font-size: 14px;">
                <p>
                        Dear Ms. / Mr.
                        <b style="font-weight: 800;">{{$nameEn}}</b>,
                    </p>
                    <h2 style="font-size: 30px;font-weight: 600;margin-bottom: 13px;">Welcome to Mjlsi</h2>

                    <p>Thank you for your interest and enquiry.</p>
                    <p>
                        Your Registration has been successfully submitted by {{$organizationNameEn}}.
                    </p>
                    <br style="height: 12px;">

                    <p style="font-weight: 700;margin-bottom: 10px;">
                    In case you have any inquiries , don’t hesitate to contact us at Support@mjlsi.com any time. We would love to hear from you.
                    </p>
                    <p>
                    Simply click on the link below to activate your account and set a new password:
                </p>
                <a
                    href="{{ config('resetpassword.url_frontend') }}/reset-password?token={{$token}}">{{ config('resetpassword.url_frontend') }}/reset-password?token={{$token}}
                </a>
                    <p style="font-weight: 700;margin-bottom: 10px;">
                        Thanks and Regards,
                    </p>
                    <p style="font-weight: 700;margin-top: 0;">

                        Customer Support Representative,
                        <br>
                        Mjlsi Team
                    </p>
            </div>
        </div>
    </div>
    
    
    </body>
</html>
