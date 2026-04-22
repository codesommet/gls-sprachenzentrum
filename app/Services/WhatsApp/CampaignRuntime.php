<?php

namespace App\Services\WhatsApp;

class CampaignRuntime
{
    public function statusPath(): string  { return storage_path('app/whatsapp-campaign-status.json'); }
    public function controlPath(): string { return storage_path('app/whatsapp-campaign-control.json'); }

    public function readStatus(): array
    {
        if (!is_file($this->statusPath())) return ['running' => false];
        $raw = @file_get_contents($this->statusPath());
        $d = $raw ? json_decode($raw, true) : null;
        return is_array($d) ? $d : ['running' => false];
    }

    public function writeStatus(array $status): void
    {
        $dir = dirname($this->statusPath());
        if (!is_dir($dir)) @mkdir($dir, 0o777, true);
        file_put_contents($this->statusPath(), json_encode($status, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public function readControl(): array
    {
        if (!is_file($this->controlPath())) return ['paused' => false, 'aborted' => false];
        $raw = @file_get_contents($this->controlPath());
        $d = $raw ? json_decode($raw, true) : null;
        return is_array($d)
            ? $d + ['paused' => false, 'aborted' => false]
            : ['paused' => false, 'aborted' => false];
    }

    public function writeControl(array $control): void
    {
        $dir = dirname($this->controlPath());
        if (!is_dir($dir)) @mkdir($dir, 0o777, true);
        file_put_contents($this->controlPath(), json_encode($control), LOCK_EX);
    }
}
