<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

/**
 * Minimal import class for reading Excel files with Excel::toArray().
 * Actual parsing is done in dedicated parser services.
 */
class EmptyImport implements WithCalculatedFormulas
{
    // No methods needed — parsing handled by parser services.
}
