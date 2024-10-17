<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MeetingReminder extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameEn;
    protected $nameAr;
    protected $titleEn;
    protected $titleAr;
    protected $venueAr;
    protected $venueEn;
    protected $date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nameAr, $nameEn,$titleAr,$titleEn,$venueAr,$vanueEn ,$date)
    {
        $this->nameEn   = $nameEn;
        $this->nameAr = $nameAr;
        $this->titleAr   = $titleAr;
        $this->titleEn = $titleEn;
        $this->venueAr   = $venueAr;
        $this->vanueEn = $vanueEn;
        $this->date   = $date;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
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
        
        return $this->view($mailFolderName.'.meeting-reminder')
                ->with('nameEn', $this->nameEn)
                ->with('nameAr', $this->nameAr)
                ->with('venueAr', $this->venueAr)
                ->with('venueEn', $this->vanueEn)
                ->with('titleAr', $this->titleAr)
                ->with('titleEn', $this->titleEn)
                ->with('date', $this->date)
                ->subject("تذكير باجتماع");
    }
}
