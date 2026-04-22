<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppCampaign extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_campaigns';

    protected $fillable = [
        'site_id', 'user_id', 'name', 'message', 'status',
        'total', 'sent', 'failed',
        'delay_min', 'delay_max', 'launch_wait',
        'attachment_path', 'recipients',
        'started_at', 'finished_at',
    ];

    protected $casts = [
        'recipients'  => 'array',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pendingCount(): int
    {
        $count = 0;
        foreach (($this->recipients ?? []) as $r) {
            if (($r['status'] ?? 'pending') === 'pending') $count++;
        }
        return $count;
    }
}
