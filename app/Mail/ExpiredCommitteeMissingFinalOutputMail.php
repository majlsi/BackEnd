<?php

namespace App\Mail;

use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiredCommitteeMissingFinalOutputMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameEn;
    protected $nameAr;
    protected $titleEn;
    protected $titleAr;
    protected $committeeHeadNameAr;
    protected $committeeHeadNameEn;
    protected $committeeNameAr;
    protected $committeeNameEn;
    protected $languageId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        $nameAr,
        $nameEn,
        $titleAr,
        $titleEn,
        $committeeHeadNameAr,
        $committeeHeadNameEn,
        $committeeNameAr,
        $committeeNameEn,
        $languageId
    ) {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->titleAr = $titleAr;
        $this->titleEn = $titleEn;
        $this->committeeHeadNameAr = $committeeHeadNameAr;
        $this->committeeHeadNameEn = $committeeHeadNameEn;
        $this->committeeNameAr = $committeeNameAr;
        $this->committeeNameEn = $committeeNameEn;
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
        } elseif (config('buildConfig.currentTheme') == config('buildConfig.themeNames.gaft')) {
            $mailFolderName = 'mails-gaft';
        } elseif (config('buildConfig.currentTheme') == config('buildConfig.themeNames.eca')) {
            $mailFolderName = 'mails-eca';
        } elseif (config('buildConfig.currentTheme') == config('buildConfig.themeNames.lcgpa')) {
            $mailFolderName = 'mails-lcgpa';
        } elseif (config('buildConfig.currentTheme') == config('buildConfig.themeNames.sadu')) {
            $mailFolderName = 'mails-sadu';
        }

        return $this->view($mailFolderName . '.expired-committee-missing-final-output-mail')

            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('titleAr', $this->titleAr)
            ->with('titleEn', $this->titleEn)
            ->with('committeeHeadNameEn', $this->committeeHeadNameEn)
            ->with('committeeHeadNameAr', $this->committeeHeadNameAr)
            ->with('committeeNameAr', $this->committeeNameAr)
            ->with('committeeNameEn', $this->committeeNameEn)
            ->with('languageId', $this->languageId)
            ->subject("لم يتم إضافة المخرج النهائي");
    }
}
