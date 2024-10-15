<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminsRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameEn;
    protected $nameAr;
    protected $organizationId;
    protected $organizatioNameAr;
    protected $organizatioNameEn;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nameAr, $nameEn, $organizationId,$organizatioNameAr,$organizatioNameEn)
    {
        $this->nameEn   = $nameEn;
        $this->nameAr = $nameAr;
        $this->organizationId = $organizationId;
        $this->organizatioNameAr = $organizatioNameAr;
        $this->organizatioNameEn = $organizatioNameEn;
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
        return $this->view($mailFolderName.'.new-registration')
                ->with('nameEn', $this->nameEn)
                ->with('nameAr', $this->nameAr)
                ->with('organizationId', $this->organizationId)
                ->with('organizatioNameAr', $this->organizatioNameAr)
                ->with('organizatioNameEn', $this->organizatioNameEn)
                ->subject("طلب جديد");
    }
}
