<?php

namespace App\Models;

use App\Contracts\SyncableToGoogleSheet;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model implements SyncableToGoogleSheet
{
    protected $fillable = [
        'name',
        'city',
        'phone',
        'email',

        // Google Sheets tracking
        'google_sheet_name',
        'google_sheet_row',
        'google_sheet_synced_at',
    ];

    protected $casts = [
        'google_sheet_synced_at' => 'datetime',
    ];

    // --- SyncableToGoogleSheet Interface ---

    public function getSheetFullName(): string
    {
        return $this->name;
    }

    public function getSheetLevel(): string
    {
        return ''; // Not applicable for consultation
    }

    public function getSheetPhone(): string
    {
        return $this->phone ?? '';
    }

    public function getSheetEmail(): string
    {
        return $this->email ?? '';
    }

    public function getSheetCenter(): ?string
    {
        return null; // Consultation doesn't have a center
    }

    public function getSheetGroup(): string
    {
        return ''; // Not applicable for consultation
    }

    public function getSheetCentreId(): ?int
    {
        return null; // Consultation goes to dedicated sheet
    }

    public function getSheetCity(): string
    {
        return $this->city ?? '';
    }

    public function isSyncedToSheet(): bool
    {
        return $this->google_sheet_synced_at !== null;
    }

    public function isSyncedToConfirmedSheet(): bool
    {
        return false; // Consultation doesn't use confirmed sheet
    }

    /**
     * Check if this is a consultation (used by sync service)
     */
    public function isConsultation(): bool
    {
        return true;
    }
}
