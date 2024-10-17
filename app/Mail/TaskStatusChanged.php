<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    protected $nameEn;
    protected $nameAr;
    protected $serial_number;
    protected $taskId;
    protected $languageId;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($nameAr, $nameEn, $serial_number, $taskStatusNameAr, $taskStatusNameEn, $changedByNameEn, $changedByNameAr, $taskId, $languageId)
    {
        $this->nameEn = $nameEn;
        $this->nameAr = $nameAr;
        $this->serial_number = $serial_number;

        $this->taskStatusNameAr = $taskStatusNameAr;
        $this->taskStatusNameEn = $taskStatusNameEn;
        $this->changedByNameEn = $changedByNameEn;
        $this->changedByNameAr = $changedByNameAr;

        $this->taskId = $taskId;
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
        return $this->view($mailFolderName . '.task-status-changed')
            ->with('nameEn', $this->nameEn)
            ->with('nameAr', $this->nameAr)
            ->with('taskId', $this->taskId)
            ->with('serial_number', $this->serial_number)

            ->with('taskStatusNameAr', $this->taskStatusNameAr)
            ->with('taskStatusNameEn', $this->taskStatusNameEn)
            ->with('changedByNameEn', $this->changedByNameEn)
            ->with('changedByNameAr', $this->changedByNameAr)

            ->with('languageId', $this->languageId)
            ->subject("تغيير حالة المهمة");
    }
}
