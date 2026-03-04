<?php

namespace App\Services;

use App\Contracts\SyncableToGoogleSheet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class GoogleSheetsLeadSyncService
{
    const HEADERS = ['Nom', 'Niveau', 'Téléphone', 'Email', 'Centre', 'Groupe'];

    protected GoogleSheetsClient $client;

    public function __construct(GoogleSheetsClient $client)
    {
        $this->client = $client;
    }

    /**
     * Append a lead to the center's Google Sheet tab.
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

        // Ensure header row exists before any write
        $this->client->ensureHeaderRow($sheetName, self::HEADERS);

        $rowData = $this->buildRowData($lead);

        // Idempotence: if already synced, update existing row
        if ($lead->isSyncedToSheet() && $lead->google_sheet_row) {
            $this->client->updateRow($sheetName, $lead->google_sheet_row, $rowData);
            Log::info("Google Sheets: updated existing row {$lead->google_sheet_row} in '{$sheetName}' for {$label}");
            return;
        }

        // Append new row
        $rowNumber = $this->client->append($sheetName, $rowData);

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
     * Append the lead to the CONFIRMED sheet.
     * Idempotent: skips if already synced to confirmed sheet.
     *
     * @param Model&SyncableToGoogleSheet $lead
     */
    public function appendLeadToConfirmedSheet(Model $lead): void
    {
        $label = $this->label($lead);

        if ($lead->isSyncedToConfirmedSheet()) {
            Log::info("Google Sheets: {$label} already synced to CONFIRMED sheet, skipping");
            return;
        }

        $confirmedSheet = config('google-sheets.confirmed_sheet', 'CONFIRMED');

        $this->client->ensureSheetExists($confirmedSheet);
        $this->client->ensureHeaderRow($confirmedSheet, self::HEADERS);

        $rowData = $this->buildRowData($lead);
        $this->client->append($confirmedSheet, $rowData);

        $lead->update([
            'google_sheet_confirmed_synced_at' => now(),
        ]);

        Log::info("Google Sheets: appended {$label} to CONFIRMED sheet");
    }

    /**
     * Resolve the Google Sheet tab name from the lead's centre ID.
     *
     * @param Model&SyncableToGoogleSheet $lead
     */
    public function resolveSheetName(Model $lead): ?string
    {
        $centreId = $lead->getSheetCentreId();

        if ($centreId === null) {
            Log::warning("Google Sheets: {$this->label($lead)} has no centre ID");
            return null;
        }

        $sheetMap = config('google-sheets.sheet_map', []);
        $sheetName = $sheetMap[(string) $centreId] ?? null;

        Log::debug("Google Sheets: resolveSheetName for {$this->label($lead)} — centreId={$centreId}, sheetName=" . ($sheetName ?? 'NULL'));

        return $sheetName;
    }

    /**
     * Build the simplified row data: full_name, level, phone, email, center.
     *
     * @param Model&SyncableToGoogleSheet $lead
     */
    public function buildRowData(Model $lead): array
    {
        return [
            $lead->getSheetFullName(),
            $lead->getSheetLevel(),
            $lead->getSheetPhone(),
            $lead->getSheetEmail(),
            $lead->getSheetCenter() ?? 'N/A',
            $lead->getSheetGroup(),
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
