<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

/**
 * Minimal import class for Maatwebsite Excel.
 * Actual parsing is done in PresenceExcelParserService.
 */
class PresenceExcelImport implements WithCalculatedFormulas
{
}
