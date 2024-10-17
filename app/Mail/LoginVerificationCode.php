<?php

namespace App\Mail;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameEn;
    protected $nameAr;
    protected $code;
    protected $languageId;
    protected $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($nameAr, $nameEn, $code ,$token,$languageId)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->code = $code;
        $this->token = $token;
        $this->languageId=$languageId;

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
        } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.gaft')) {
            $mailFolderName = 'mails-gaft';
        } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.eca')) {
            $mailFolderName = 'mails-eca';
        } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.lcgpa')) {
            $mailFolderName = 'mails-lcgpa';
        } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.sadu')) {
            $mailFolderName = 'mails-sadu';
        } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.swcc')) {
            $mailFolderName = 'mails-swcc';
        } 

        return $this->view($mailFolderName . '.login-verification-code')
            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('code', $this->code)
            ->with('token', $this->token)
            ->with('languageId', $this->languageId)
            ->subject($this->languageId == config('languages.ar')? "كود الدخول" : "Login code");
    }
}
