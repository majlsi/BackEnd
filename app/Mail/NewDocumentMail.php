<?php

namespace App\Mail;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameAr, $nameEn, $documentSubject,
        $createdByNameEn, $createdByNameAr,$documentId , $languageId;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($nameAr, $nameEn, $documentSubject, $createdByNameEn, $createdByNameAr,$documentId , $languageId)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->documentSubject = $documentSubject;
        $this->createdByNameEn = $createdByNameEn;
        $this->createdByNameAr = $createdByNameAr;
        $this->languageId = $languageId;
        $this->documentId = $documentId;
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

        return $this->view($mailFolderName . '.new-document')
            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('documentSubject', $this->documentSubject)
            ->with('createdByNameEn', $this->createdByNameEn)
            ->with('createdByNameAr', $this->createdByNameAr)
            ->with('languageId', $this->languageId)
            ->with('documentId',$this->documentId)
            ->subject('غرفة المراجعات');
    }
}
