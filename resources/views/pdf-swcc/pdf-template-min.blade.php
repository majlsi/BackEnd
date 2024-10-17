<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Saline Water Conversion Corporation (SWCC) - PDF</title>
  <style>
    @page {
      margin: 3% !important;
      margin-header: 1mm;
      margin-footer: 2mm;
      border-top: 2mm solid #f6861f;
    }

    body {
      padding: 25px 15px 10px 15px !important;
      font-family: XB Riyaz;
      direction: ltr;
    }

    table {
      border-collapse: collapse;
      width: 100% !important;
      page-break-inside: avoid;
    }

    .participants table {
      border-left: transparent;
      border-bottom: transparent;
      margin: 0px;
      padding: 0px;
    }

    table {
      border: transparent !important;
    }

    th,
    td {
      border: 1px solid black;
    }

    th {
      background-color: #0066cc;
      color: white;
      font-weight: bold;
      padding: 3px;
      text-align: center;
    }

    .double-header th {
      background-color: rgb(238, 234, 234);
      color: #0066cc;
      font-weight: normal;
      padding: 1px;
      text-align: center;
      width: 100%;
    }

    td {
      padding: 1px;
      text-align: center;
      font-size: 14px;
    }

    @media print {
      body {
        -webkit-print-color-adjust: exact;
      }

      td {
        padding: 3mm;
        text-align: left;
        border-radius: 30mm !important;
        -webkit-print-color-adjust: exact;
      }

      td table td {
        padding: 0;
        padding-bottom: 2mm;
      }

      .new-page {
        display: block;
        page-break-before: always;
      }

    }

    div {
      -webkit-box-sizing: border-box;
      -moz-box-sizing: border-box;
      box-sizing: border-box;
    }

    @page {
      border-top: 2mm solid #0066cc;
      background-image: url(../img/bg-1.png);
      background-size: 100%;
      background-repeat: no-repeat;
    }

    .bold {
      font-weight: bold;
    }

    p {
      margin: 0;
      line-break: auto !important;
    }

    .text-center {
      text-align: center !important;
    }

    .text-left {
      text-align: left !important;
    }

    li::marker {
      color: #0066cc;
      font-weight: bold;
      list-style-type: arabic-indic;
      font-family: 'ArabicNumerals', Arial, sans-serif;
    }

    @font-face {
      font-family: "ArabicNumerals";
      src: url("{{ asset('font/Arabic-Font/ArabicNumerals.ttf') }}") format("truetype");
    }

    .final {
      width: 100%;
      display: inline-flex;
    }

    .final table {
      width: 100% !important;
    }

    .final th {
      width: 33.3% !important;
    }

    .final td {
      height: 20px;
    }

    .meeting-time th {
      width: 33.3% !important;
    }

    .participants-names th {
      width: 50% !important;
    }

    .transparent {
      background-color: transparent;
      border-top: transparent !important;
      border-bottom: transparent !important;
    }

    .white-bg {
      height: 30mm;
      padding: 0;
      background-color: #fff;
    }
  </style>
</head>

<body>
  @if (!isset($meetingMom['mom_summary']))
  <div class="imagelogo" style="display: block !important;float: right;width: 250px;">
    <p style="text-align: left !important;">
      <img src="{{ asset('img/swcc-logo.png') }}">
    </p>
  </div>
  <br>
  <br>
  <div class="text-center">
    <h2 class="text-center">
      Minutes of meeting
    </h2>
  </div>
  @if ($data['show_mom_header'] == 1)
  <div class="meeting-data">
    <p>
      Referring to
      {{ $data['meeting_title_ar'] ? $data['meeting_title_ar'] : $data['meeting_title_en'] }} -
      {{ $data['meeting_description_ar'] ? $data['meeting_description_ar'] : $data['meeting_description_en'] }},
      The meeting took place, and these minutes were produced in accordance with the following agenda and
      recommendations
    </p>
  </div>
  <br>
  <div class="meeting-time">
    <table>
      <tr>
        <th>Date</th>
        <th colspan="3">Time</th>
        <th>Location</th>
      </tr>
      <tbody>
        <tr>
          <td class="text-center">
            Day
            {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%d/%m/%Y') }}
          </td>
          <td class="text-center" style="border-right: transparent !important;border-left: transparent !important;">
            {{ Carbon\Carbon::parse($data['meeting_schedule_from'])->formatLocalized('%r') }}
          </td>
          <td class="text-center" style="border-right: transparent !important;border-left: transparent !important;">
            -
          </td>
          <td class="text-center" style="border-right: transparent !important;border-left: transparent !important;">
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
        <th>Meeting agenda</th>
      </tr>
      <tbody>
        <tr>
          <td class="text-left">
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
          General Corporation for Salt Water Conversion
        </th>
      </tr>
      @if ($data['show_participant_job'] == 1)
      <tr class="participants-names">
        @else
      <tr style="width: 100% !important;">
        @endif
        <th>
          Name
        </th>
        @if ($data['show_participant_job'] == 1)
        <th>
          Position
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
          <td>Guest</td>
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
        <th style="width: 55% !important;">What was agreed upon and recommendations</th>
        <th style="width: 15% !important;">date of starting</th>
        <th style="width: 15% !important;">Administrator</th>
        <th style="width: 15% !important;">Follow-up and reporting officer</th>
      </tr>
      <tbody>
        @foreach ($data['meeting_recommendations'] as $key => $recommend)
        <tr>
          <td class="text-left">
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
  @else
  {!! $meetingMom['mom_summary'] !!}
  @endif
  @foreach ($data['canSignParticipants']->chunk(8) as $participantGroupPage)
  <div class="new-page">
    <table style="width:100%;" cellspacing="1">
      <colgroup>
        <col span="2" style="background-color:#F9F9F9">
      </colgroup>
      @foreach ($participantGroupPage->chunk(2) as $participantGroup)
      <tr>
        @foreach ($participantGroup as $participant)
        <td style="margin:15px;border: transparent !important;">
          <table style="width:100%;border-spacing: 5px;">
            <tr>
              <td bgcolor="#ddd" style="border: transparent !important;padding: 5pt;">
                <h4 style="font-weight: 700;">
                  @if ($participant['user_title_en'] != null && !empty($participant['user_title_en']))
                  {{ $participant['user_title_en']}}:
                  @else
                  {{ $participant['user_title_ar']}}:
                  @endif
                  @if ($participant['name'] == null || empty($participant['name']))
                  {{ $participant['name_ar']}}
                  @else
                  {{ $participant['name']}}
                  @endif
                </h4>
                @if ($participant['nickname_en'] != null && !empty($participant['nickname_en']))
                ({{ $participant['nickname_en'] }})
                @else
                @if ($participant['nickname_ar'] != null && !empty($participant['nickname_ar']))
                ({{ $participant['nickname_ar'] }})
                @endif
                @endif
                <table width="100%" style="width:100%;height: 160px;margin-top: 20px;">
                  <tr>
                    <td style="width:100%;border: transparent !important;height: 120px;color:#fff" bgcolor="#FFF">
                      <p>Signature</p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
        @endforeach
      </tr>
      @endforeach
    </table>
  </div>
  @endforeach
</body>

</html>