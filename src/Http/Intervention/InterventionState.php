<?php

namespace App\Http\Intervention;

class InterventionState
{
    public const RECEIVED = "Réceptionnée";
    public const CLOSED = "Clôturée";
    public const IN_PROGRESS = "En cours";
    public const PERSISTENT = "Persistant";

    public static function getAll()
    {
        return [
            InterventionState::RECEIVED,
            InterventionState::CLOSED,
            InterventionState::IN_PROGRESS,
            InterventionState::PERSISTENT,
        ];
    }
}
