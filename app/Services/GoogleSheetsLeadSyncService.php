<?php

namespace App\Services;

use App\Contracts\SyncableToGoogleSheet;
use App\Models\Consultation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class GoogleSheetsLeadSyncService
{
    protected GoogleSheetsClient $client;

    public function __construct(GoogleSheetsClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get headers based on the model type
     */
    protected function getHeaders(Model $lead): array
    {
        if ($lead instanceof Consultation || (method_exists($lead, 'isConsultation') && $lead->isConsultation())) {
            return config('google-sheets.consultation_headers', [
                'Date de Lead',
                'Nom Complet',
                'telephone',
                'City',
                'Email'
            ]);
        }

        return config('google-sheets.inscription_headers', [
            'Date de Lead',
            'Nom',
            'Prenom',
            'telephone',
            'Niveau actuel',
            'Groupe',
            'email',
            'Adresse',
            'Centre de formation'
        ]);
    }

    /**
     * Append a lead to the appropriate Google Sheet tab.
     * Idempotent: if already synced, updates the existing row instead.
     *
     * @param Model&SyncableToGoogleSheet $lead
     */
    public function appendLeadToCenterSheet(Model $lead): void
    {
        $sheetName = $this->resolveSheetName($lead);
        $label = $this->label($lead);

        if (!$sheetName) {
            $centreId = $lead->getSheetCentreId();
            Log::warning("Google Sheets sync: no sheet mapping for {$label} (centreId: {$centreId}, available keys: " . implode(', ', array_keys(config('google-sheets.sheet_map', []))) . ")");
            return;
        }

        $headers = $this->getHeaders($lead);
        $columnCount = count($headers);

        // Ensure header row exists before any write
        $this->client->ensureHeaderRow($sheetName, $headers, $columnCount);

        $rowData = $this->buildRowData($lead);

        // Idempotence: if already synced, update existing row
        if ($lead->isSyncedToSheet() && $lead->google_sheet_row) {
            $this->client->updateRow($sheetName, $lead->google_sheet_row, $rowData);
            Log::info("Google Sheets: updated existing row {$lead->google_sheet_row} in '{$sheetName}' for {$label}");
            return;
        }

        // Append new row
        $rowNumber = $this->client->append($sheetName, $rowData, $columnCount);

        $lead->update([
            'google_sheet_name' => $sheetName,
            'google_sheet_row' => $rowNumber,
            'google_sheet_synced_at' => now(),
        ]);

        Log::info("Google Sheets: appended row {$rowNumber} in '{$sheetName}' for {$label}");
    }

    /**
     * Update the row of an existing lead in the center sheet.
     *
     * @param Model&SyncableToGoogleSheet $lead
     */
    public function updateLeadRow(Model $lead): void
    {
        $sheetName = $lead->google_sheet_name;
        $row = $lead->google_sheet_row;
        $label = $this->label($lead);

        if (!$sheetName || !$row) {
            Log::warning("Google Sheets: cannot update - {$label} has no sheet tracking info");
            return;
        }

        $rowData = $this->buildRowData($lead);
        $this->client->updateRow($sheetName, $row, $rowData);

        Log::info("Google Sheets: updated {$label} in '{$sheetName}' row {$row}");
    }

    /**
     * Resolve the Google Sheet tab name from the lead.
     * Consultations go to a dedicated sheet, inscriptions go by centre.
     *
     * @param Model&SyncableToGoogleSheet $lead
     */
    public function resolveSheetName(Model $lead): ?string
    {
        // Consultations go to their dedicated sheet
        if ($lead instanceof Consultation || (method_exists($lead, 'isConsultation') && $lead->isConsultation())) {
            return config('google-sheets.consultation_sheet', 'Consultation');
        }

        // Inscriptions/Forms — resolve sheet name from the site's city
        $sheetName = $lead->getSheetCenter();

        if (!$sheetName) {
            Log::warning("Google Sheets: {$this->label($lead)} has no centre name (centreId={$lead->getSheetCentreId()})");
            return null;
        }

        Log::debug("Google Sheets: resolveSheetName for {$this->label($lead)} — sheetName={$sheetName}");

        return $sheetName;
    }

    /**
     * Build the row data based on the model type.
     * Consultation: Date de Lead, Nom Complet, telephone, City, Email
     * Inscription/Form: Date de Lead, Nom, Prenom, telephone, Niveau actuel, Groupe, email, Adresse, Centre de formation
     *
     * @param Model&SyncableToGoogleSheet $lead
     */
    public function buildRowData(Model $lead): array
    {
        $dateCreated = $lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');

        // Consultation has different structure
        if ($lead instanceof Consultation || (method_exists($lead, 'isConsultation') && $lead->isConsultation())) {
            return [
                $dateCreated,                           // Date de Lead
                $lead->getSheetFullName(),              // Nom Complet
                $lead->getSheetPhone(),                 // telephone
                $lead->getSheetCity(),                  // City
                $lead->getSheetEmail(),                 // Email
            ];
        }

        // Inscription/Form structure
        return [
            $dateCreated,                               // Date de Lead
            $lead->getSheetFirstName(),                 // Nom (first name)
            $lead->getSheetLastName(),                  // Prenom (last name)
            $lead->getSheetPhone(),                     // telephone
            $lead->getSheetLevel(),                     // Niveau actuel
            $lead->getSheetGroup(),                     // Groupe
            $lead->getSheetEmail(),                     // email
            $lead->getSheetAdresse(),                   // Adresse
            $lead->getSheetCenter() ?? 'N/A',           // Centre de formation
        ];
    }

    /**
     * Human-readable label for logging.
     */
    protected function label(Model $lead): string
    {
        return class_basename($lead) . " #{$lead->id}";
    }
}
