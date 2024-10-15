<?php

namespace App\Mail;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Spatie\CalendarLinks\Link;
use Carbon\Carbon;

class MettingAgendaPublish extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameEn;
    protected $nameAr;
    protected $titleEn;
    protected $titleAr;
    protected $venueAr;
    protected $vanueEn;
    protected $dateTo;
    protected $dateFrom;
    protected $languageId;
    protected $zoomJoinUrl;
    protected $microsoftTeamsJoinUrl;
    protected $timeZone;
    protected $guestLink;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nameAr, $nameEn, $titleAr, $titleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $zoomJoinUrl,$microsoftTeamsJoinUrl,$languageId,$timeZone, $guestLink)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->titleAr = $titleAr;
        $this->titleEn = $titleEn;
        $this->venueAr = $venueAr;
        $this->vanueEn = $vanueEn;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->languageId=$languageId;
        $this->zoomJoinUrl = $zoomJoinUrl;
        $this->microsoftTeamsJoinUrl = $microsoftTeamsJoinUrl;
        $this->timeZone = $timeZone;
        $this->guestLink = $guestLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = Carbon::parse($this->dateFrom)->subHours($this->timeZone->diff_hours);
        $to = Carbon::parse($this->dateTo)->subHours($this->timeZone->diff_hours);

        if ($this->titleAr == $this->titleEn) {
            $calendarTitle = $this->titleAr;
        } else {
            $calendarTitle = $this->titleAr . ' - ' . $this->titleEn;
        }
        if ($this->venueAr == $this->vanueEn) {
            $address = $this->venueAr;
        } else {
            $address = $this->venueAr . ' - ' . $this->vanueEn;
        }

        $link = Link::create($calendarTitle, $from, $to)
            ->address($address);

            if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.mjlsi')) {
                $mailFolderName = 'mails';
            } 
            else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.gaft')) {
                $mailFolderName = 'mails-gaft';
            } 
            else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.eca')) {
                $mailFolderName = 'mails-eca';
            } 
            else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.lcgpa')) {
                $mailFolderName = 'mails-lcgpa';
        } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.sadu')) {
            $mailFolderName = 'mails-sadu';
        } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.swcc')) {
            $mailFolderName = 'mails-swcc';
        } 
            
        return $this->view($mailFolderName . '.publish-agenda-meeting')

            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('venueAr', $this->venueAr)
            ->with('venueEn', $this->vanueEn)
            ->with('titleAr', $this->titleAr)
            ->with('titleEn', $this->titleEn)
            ->with('dateFrom', $this->dateFrom)
            ->with('languageId', $this->languageId)
            ->with('zoomJoinUrl',$this->zoomJoinUrl)
            ->with('microsoftTeamsJoinUrl',$this->microsoftTeamsJoinUrl)
            ->with('guestLink',$this->guestLink)
            ->attach($link->ics(), array('as' => ($this->languageId == config('languages.ar')? ($this->titleAr? $this->titleAr : $this->titleEn) : ($this->titleEn? $this->titleEn : $this->titleAr)) .'.ics','mime' => "text/calendar")) 
            ->subject("نشر جدول أعمال الإجتماع");
    }
}
