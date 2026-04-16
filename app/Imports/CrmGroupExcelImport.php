<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

/**
 * Minimal import class for reading CRM Excel files.
 * Actual parsing is done in CrmExcelParserService for flexibility.
 * Used with Excel::toArray() to get raw data.
 */
class CrmGroupExcelImport implements WithCalculatedFormulas
{
    // No methods needed — parsing handled by CrmExcelParserService.
    // WithCalculatedFormulas ensures formula cells are evaluated.
}
