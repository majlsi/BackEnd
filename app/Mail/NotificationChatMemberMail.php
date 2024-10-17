<?php

namespace App\Mail;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationChatMemberMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameEn;
    protected $nameAr;
    protected $titleEn;
    protected $titleAr;
    protected $languageId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nameAr, $nameEn, $titleAr, $titleEn, $languageId)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->titleAr = $titleAr;
        $this->titleEn = $titleEn;
        $this->languageId = $languageId;
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
 

        return $this->view($mailFolderName . '.notification-chat-member')

            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('titleAr', $this->titleAr)
            ->with('titleEn', $this->titleEn)
            ->with('languageId', $this->languageId)
            ->subject("رسالة جديدة ");
    }
}
