<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeNewParticipant extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameAr;
    protected $nameEn;
    protected $organizationNameAr;
    protected $organizationNameEn;
    protected $token;
    protected $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nameAr,$nameEn,$organizationNameAr,$organizationNameEn,$token,$email)
    {
        $this->nameAr = $nameAr;
        $this->nameEn   = $nameEn;
        $this->organizationNameAr = $organizationNameAr;
        $this->organizationNameEn = $organizationNameEn;

        $this->token   = $token;
        $this->email   = $email;

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

        return $this->view($mailFolderName.'.welcome-new-participant')
                ->with('nameAr',$this->nameAr)
                ->with('nameEn', $this->nameEn)
                ->with('organizationNameAr',$this->organizationNameAr)
                ->with('organizationNameEn', $this->organizationNameEn)
                ->with('token',$this->token)
                ->with('email',$this->email)
                ->subject("تفعيل حساب");
    }
}
