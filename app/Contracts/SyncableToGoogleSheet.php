<?php

namespace App\Contracts;

interface SyncableToGoogleSheet
{
    public function getSheetFullName(): string;

    public function getSheetLevel(): string;

    public function getSheetPhone(): string;

    public function getSheetEmail(): string;

    public function getSheetCenter(): ?string;

    public function getSheetGroup(): string;

    /**
     * Return the centre ID used as key in the google-sheets.sheet_map config.
     */
    public function getSheetCentreId(): ?int;

    public function isSyncedToSheet(): bool;

    public function isSyncedToConfirmedSheet(): bool;

    /**
     * Check if this model is a consultation type (uses different columns).
     */
    public function isConsultation(): bool;
}
