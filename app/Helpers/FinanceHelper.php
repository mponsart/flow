<?php

namespace App\Helpers;

class FinanceHelper
{
    /**
     * Calcule la marge brute.
     */
    public static function margeBrute(float $revenu, float $cout): float
    {
        return $revenu > 0 ? round((($revenu - $cout) / $revenu) * 100, 2) : 0;
    }

    /**
     * Calcule la marge nette.
     */
    public static function margeNette(float $benefice, float $revenu): float
    {
        return $revenu > 0 ? round(($benefice / $revenu) * 100, 2) : 0;
    }

    // Autres helpers financiers...
}
