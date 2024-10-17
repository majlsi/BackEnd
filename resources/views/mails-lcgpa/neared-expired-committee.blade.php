<!DOCTYPE html>
<html>

<head>
    <title>New Request</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
    <style>
        html,
        body {
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
    </style>
</head>

<body>
    <div class="container" style="text-align: left; display: table; width:100%; vertical-align: middle">
        <div class="content" style="text-align: left;background-size: 100%;display:block;border-radius: 6px;border-width: 6px 1px 1px;border-style: solid;border-color: #34607B rgb(236, 236, 236) rgb(236, 236, 236);border-image: initial;padding: 25px 0px;margin: 0;box-shadow: rgba(0, 0, 0, 0.16) 0px 3px 6px;">
            <div class="ltr-content padding-15" style="padding: 0 15px;text-align: left;direction: ltr;">
                <img src="{{ URL::asset('img/logo-lcgpa.png') }}" style="max-width: 135px;margin: 26px 33px;">
            </div>
            @if ($languageId == config('languages.ar'))
                <div class="rtl-content padding-15"
                    style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 400;font-size: 14px;text-align: right;direction: rtl;">
                    <p dir="RTL"> عزيزى السيد / السيدة <b style="font-weight: 800;">{{ $nameAr }}</b>, </p>
                    <p>عزيزي المستخدم لقد تبقى عشر أيام لانتهاء عمل لجنة {{ $committeeNameAr }} وتجميد كافة الصلاحيات
                        لطلب فك التجميد امل رفع طلب لمدير النظام.</p>
                    <br>
                    <p dir="RTL" style="font-weight: 700;margin-bottom: 10px;"> في حالة وجود أي استفسارات ، لا
                        تتردد في الاتصال بنا على Support@mjlsi.com في أي وقت. كنا نحب أن نسمع منك. </p>
                    <p dir="RTL" style="font-weight: 700;margin-bottom: 10px;"> شكرا جزيلا مع تحياتى, </p>
                    <p dir="RTL" style="font-weight: 700;margin-top: 0;"> مسئول الدعم <br> فريق مجلسى </p>
                </div>
            @endif
            <!-- <hr style="border: 0.5px solid #ddd;margin: 28px 0;"> -->
            @if ($languageId == config('languages.en'))
                <div class="ltr-content padding-15"
                    style="padding: 0 30px;text-align: left;direction: ltr;font-weight: 500;font-size: 14px;">
                    <p> Hello <b style="font-weight: 800;">{{ $nameEn }}</b>, </p>
                    <p>Dear user, there are ten days left until the {{ $committeeNameEn }} committee completes its work
                        and freezes all permissions to request unfreezing. I hope to submit a request to the system
                        administrator.</p>
                    <br style="height: 12px;">
                    <p style="font-weight: 700;margin-bottom: 10px;"> In case you have any inquiries , don’t hesitate to
                        contact us at Support@mjlsi.com any time. We would love to hear from you. </p>
                    <p style="font-weight: 700;margin-bottom: 10px;"> Thanks and Regards, </p>
                    <p style="font-weight: 700;margin-top: 0;"> Customer Support Representative, <br> Mjlsi Team </p>
                </div>
            @endif
        </div>
    </div>
</body>

</html>
