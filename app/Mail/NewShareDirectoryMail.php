<?php

namespace App\Mail;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewShareDirectoryMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameAr, $nameEn, $directoryNameAr, 
        $directoryNameEn, $createdByNameEn, 
        $createdByNameAr, $languageId,$directoryId;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($nameAr, $nameEn, $directoryNameAr, $directoryNameEn, $createdByNameEn, $createdByNameAr,$directoryId, $languageId)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->directoryNameAr = $directoryNameAr;
        $this->directoryNameEn = $directoryNameEn;
        $this->createdByNameEn = $createdByNameEn;
        $this->createdByNameAr = $createdByNameAr;
        $this->languageId = $languageId;
        $this->directoryId = $directoryId;
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

        return $this->view($mailFolderName . '.new-share-directory')
            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('directoryNameAr', $this->directoryNameAr)
            ->with('directoryNameEn', $this->directoryNameEn)
            ->with('createdByNameEn', $this->createdByNameEn)
            ->with('createdByNameAr', $this->createdByNameAr)
            ->with('languageId', $this->languageId)
            ->with('directoryId',$this->directoryId)
            ->subject('الملفات');
    }
}
