@php
setlocale(LC_ALL, 'ar_AE.utf8');
@endphp
<div class="imagelogo" style="display: block;">
    <img style="width: 250px;" src="{{ asset('img/swcc-logo.png') }}">
</div>
<h2 class="text-center">
    محضر الاجتماع
</h2>
@if ($data['show_mom_header'] == 1)
<div class="meeting-data">
    <p>
        {!! $data['introduction_template_ar'] !!}
    </p>
</div>
<br>
<div class="meeting-time">
    <table>
        <tr>
            <th>التاريخ</th>
            <th colspan="3">الوقت</th>
            <th>الموقع</th>
        </tr>
        <tbody>
            <tr>
                <td class="text-center">
                    اليوم
                    {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%d/%m/%Y') }}
                </td>
                <td class="text-center"
                    style="border-right: transparent !important;border-left: transparent !important;">
                    {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%r') }}
                </td>
                <td class="text-center"
                    style="border-right: transparent !important;border-left: transparent !important;">
                    -
                </td>
                <td class="text-center"
                    style="border-right: transparent !important;border-left: transparent !important;">
                    {{ Carbon\Carbon::parse($data['meeting_schedule_to'])->formatLocalized('%r') }}
                </td>
                <td class="text-center">
                    {{ $data['meeting_venue_ar'] }}
                </td>
            </tr>
        </tbody>
    </table>
</div>
<br>
@endif
@if (count($data['meeting_agendas']) && $data['show_agenda_list'] == 1)
<div class="agenda">
    <table>
        <tr>
            <th>أجندة الاجتماع</th>
        </tr>
        <tbody>
            <tr>
                <td class="text-right">
                    <ul>
                        @foreach ($data['meeting_agendas'] as $agenda)
                        <li>
                            {{ $agenda['agenda_title_ar'] ? $agenda['agenda_title_ar'] : $agenda['agenda_title_en'] }}
                        </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<br>
@endif
<div class="participants div-container" style="width: 100% !important;">
    <table>
        <tr class="double-header">
            <th colspan="{{ $data['show_participant_job'] == 1 ? 2 : 1 }}">
                المؤسسة العامة لتحلية المياه المالحة
            </th>
        </tr>
        @if ($data['show_participant_job'] == 1)
        <tr class="participants-names">
            @else
        <tr style="width: 100%;">
            @endif
            <th>
                الاسم
            </th>
            @if ($data['show_participant_job'] == 1)
            <th>
                المنصب
            </th>
            @endif
        </tr>
        <tbody>
            @foreach ($data['meeting_participants'] as $participantGroupPage)
            <tr>
                <td>{{ $participantGroupPage['name_ar'] }}</td>
                @if ($data['show_participant_job'] == 1)
                <td>{{ $participantGroupPage['job_title_ar'] }}</td>
                @endif
            </tr>
            @endforeach
            @foreach ($data['guests'] as $guest)
            <tr>
                <td>{{ $guest['full_name'] ?? $guest['email'] }}</td>
                @if ($data['show_participant_job'] == 1)
                <td>ضيف</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<br>
@if ($data['isRecommendationVisible'] && $data['show_recommendation'])
<div class="recommendations">
    <table>
        <tr>
            <th style="width: 55% !important;">ما تم التوافق عليه والتوصيات</th>
            <th style="width: 15% !important;">تاريخ التنفيذ</th>
            <th style="width: 15% !important;">المسؤول</th>
            <th style="width: 15% !important;">مسؤول المتابعة والتقارير</th>
        </tr>
        <tbody>
            @foreach ($data['meeting_recommendations'] as $key => $recommend)
            <tr>
                <td class="text-right">
                    {{ $key + 1 . '. ' . $recommend['recommendation_text'] }}
                </td>
                <td class="text-center">
                    {{ $recommend['formatted_recommendation_date'] }}
                </td>
                <td class="text-center">
                    {{ $recommend['responsible_user'] }}
                </td>
                <td class="text-center">
                    {{ $recommend['responsible_party'] }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<br>
@endif