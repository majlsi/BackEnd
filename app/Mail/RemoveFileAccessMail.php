<?php

namespace App\Mail;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RemoveFileAccessMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameAr, $nameEn, $fileNameAr, 
        $fileNameEn, $createdByNameEn, 
        $createdByNameAr, $languageId;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($nameAr, $nameEn, $fileNameAr, $fileNameEn, $createdByNameEn, $createdByNameAr, $languageId)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->fileNameAr = $fileNameAr;
        $this->fileNameEn = $fileNameEn;
        $this->createdByNameEn = $createdByNameEn;
        $this->createdByNameAr = $createdByNameAr;
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
        return $this->view($mailFolderName . '.remove-file-access')
            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('fileNameAr', $this->fileNameAr)
            ->with('fileNameEn', $this->fileNameEn)
            ->with('createdByNameEn', $this->createdByNameEn)
            ->with('createdByNameAr', $this->createdByNameAr)
            ->with('languageId', $this->languageId)
            ->subject('الملفات');
    }
}
