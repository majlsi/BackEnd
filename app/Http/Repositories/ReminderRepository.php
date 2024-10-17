<?php

namespace Repositories;


class ReminderRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\Reminder';
    }
}