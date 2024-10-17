<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminsOrganizationExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameEn;
    protected $nameAr;
    protected $expiresOrganizations;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nameAr, $nameEn, $expiresOrganizations)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->expiresOrganizations = $expiresOrganizations;
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

        return $this->view($mailFolderName.'.organizations-expired-date')
                ->with('nameEn', $this->nameEn)
                ->with('nameAr', $this->nameAr)
                ->with('expiresOrganizations', $this->expiresOrganizations)
                ->subject("إنقضاء فترة صلاحية المنشأة");
    }
}
