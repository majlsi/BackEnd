<?php

namespace App\Mail;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingEnded extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameEn;
    protected $nameAr;
    protected $titleEn;
    protected $titleAr;
    protected $venueAr;
    protected $venueEn;
    protected $dateTo;
    protected $dateFrom;
    protected $pdf;
    protected $languageId;
    protected $mailSubject;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($nameAr, $nameEn, $titleAr, $titleEn, $venueAr, $vanueEn, $dateFrom, $dateTo, $pdf, $languageId, $mailSubject)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->titleAr = $titleAr;
        $this->titleEn = $titleEn;
        $this->venueAr = $venueAr;
        $this->vanueEn = $vanueEn;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->pdf = $pdf;
        $this->languageId = $languageId;
        $this->mailSubject = $mailSubject;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = DateTime::createFromFormat('Y-m-d H:i:s', $this->dateFrom);
        $to = DateTime::createFromFormat('Y-m-d H:i:s', $this->dateTo);

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

        if ($this->languageId == config('languages.ar')) {
            $pdfTitle = $this->titleAr;
        } else if ($this->languageId == config('languages.en')) {
            $pdfTitle = $this->titleEn;
        }

        $view  = null;
        if($this->pdf != null){
            $view = $this->view($mailFolderName . '.end-meeting')
            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('venueAr', $this->venueAr)
            ->with('venueEn', $this->vanueEn)
            ->with('titleAr', $this->titleAr)
            ->with('titleEn', $this->titleEn)
            ->with('dateFrom', $this->dateFrom)
            ->with('languageId', $this->languageId)
            ->attachData($this->pdf->output(), $pdfTitle . ".pdf")
            ->subject($this->mailSubject);
        } else {
            $view = $this->view($mailFolderName . '.end-meeting')
            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('venueAr', $this->venueAr)
            ->with('venueEn', $this->vanueEn)
            ->with('titleAr', $this->titleAr)
            ->with('titleEn', $this->titleEn)
            ->with('dateFrom', $this->dateFrom)
            ->with('languageId', $this->languageId)
            ->subject($this->mailSubject);
        }
  


        return $view;
    }
}
