<?php

namespace App\Mail;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewCircularDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameAr, $nameEn, $decisionSubjectAr, 
        $decisionSubjectEn, $createdByNameEn, 
        $createdByNameAr, $languageId,$decisionId;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($nameAr, $nameEn, $decisionSubjectAr, $decisionSubjectEn, $createdByNameEn, $createdByNameAr,$decisionId, $languageId)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->decisionSubjectAr = $decisionSubjectAr;
        $this->decisionSubjectEn = $decisionSubjectEn;
        $this->createdByNameEn = $createdByNameEn;
        $this->createdByNameAr = $createdByNameAr;
        $this->languageId = $languageId;
        $this->decisionId = $decisionId;
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

        return $this->view($mailFolderName . '.new-circular-decision')
            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('decisionSubjectAr', $this->decisionSubjectAr)
            ->with('decisionSubjectEn', $this->decisionSubjectEn)
            ->with('createdByNameEn', $this->createdByNameEn)
            ->with('createdByNameAr', $this->createdByNameAr)
            ->with('languageId', $this->languageId)
            ->with('decisionId',$this->decisionId)
            ->subject('قرار بالتمرير');
    }
}
