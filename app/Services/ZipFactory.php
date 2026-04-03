<?php

namespace App\Services;

use App\Libraries\CompetitionStorage;

class ZipFactory
{
    public static function make($competition): ZipServiceInterface
    {
        if (!$competition) {
            throw new \InvalidArgumentException("Competition invalide");
        }

        // 🔁 sécurise array / object
        if (is_array($competition)) {
            $competition = (object) $competition;
        }

        $storage = new CompetitionStorage();

        /*
        =====================
        TYPE RESOLUTION
        =====================
        */

        if ($storage->isNational($competition)) {

            log_message('debug', '[ZIP FACTORY] NATIONAL');

            return new NationalZipService();
        }

        log_message('debug', '[ZIP FACTORY] REGIONAL');

        return new RegionalZipService();
    }
}