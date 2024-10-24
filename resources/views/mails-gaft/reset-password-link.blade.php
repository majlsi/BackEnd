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
            style="text-align: left;background-size: 100%;display: inline-block;border-radius: 6px;border-width: 6px 1px 1px;border-style: solid;border-color: #017069;border-image: initial;padding: 25px 0px;margin: 0;box-shadow: rgba(0, 0, 0, 0.16) 0px 3px 6px;">
            <div class="ltr-content padding-15" style="padding: 0 15px;text-align: left;direction: ltr;">
            <img src="{{ URL::asset('img/logo-gaft.png') }}" style="max-width: 100px;margin: 26px 33px;">
            </div>
            <div class="rtl-content padding-15"
                style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 400;font-size: 14px;text-align: right;direction: rtl;">
                <p dir="RTL">
                    مرحبا
                    <b style="font-weight: 800;">{{$nameAr}}</b>,
                </p>
                <p dir="RTL">
                    لقد تلقينا طلبا لاعاده ادخال كلمة المرور الخاصة لحسابك : {{$email}}.
                    نحن هنا للمساعدة!
                </p>
                <p dir="RTL">
                    ببساطة عليك فتح الرابط أدناه لتغيير كلمة المرور الخاصة بك:
                </p>
                <a dir="RTL"
                    href="{{ config('resetpassword.url_frontend') }}/reset-password?token={{$token}}">{{ config('resetpassword.url_frontend') }}/reset-password?token={{$token}}</a>

                <p dir="RTL">
                    إذا لم تقم بطلب تغيير كلمة المرور الخاصة بك. يمكنك تجاهل هذه الرسالة.
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
                    فريق الهيئة العامة للتجارةالخارجية

                </p>
            </div>
            <hr style="border: 0.5px solid #ddd;margin: 28px 0;">
            <div class="ltr-content padding-15"
                style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 500;font-size: 14px;">
                <p>
                    Hello
                    <b style="font-weight: 800;">{{$nameEn}}</b>,
                </p>

                <p>
                    We received a request to reset your password for your  account: {{$email}}. We're here to help!
                </p>
                <p>
                    Simply click on the link below to set a new password:
                </p>
                <a
                    href="{{ config('resetpassword.url_frontend') }}/reset-password?token={{$token}}">{{ config('resetpassword.url_frontend') }}/reset-password?token={{$token}}
                </a>
                <p>
                    If you didn't request a password reset, you can ignore this message.
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
                    Saudi General Authority of Foreign Trade Team
                </p>
            </div>
        </div>
    </div>
    
    
    </body>
</html>
