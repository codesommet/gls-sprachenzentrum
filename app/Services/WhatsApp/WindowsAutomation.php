<?php

namespace App\Services\WhatsApp;

use Symfony\Component\Process\Process;

class WindowsAutomation
{
    private function ps(string $command, int $timeoutSeconds = 15): bool
    {
        $process = Process::fromShellCommandline(
            'powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -Command ' . escapeshellarg($command)
        );
        $process->setTimeout($timeoutSeconds);
        $process->run();
        return $process->isSuccessful();
    }

    public function launchUrl(string $url): bool
    {
        $process = new Process(['rundll32.exe', 'url.dll,FileProtocolHandler', $url]);
        $process->setTimeout(10);
        $process->run();
        return $process->isSuccessful();
    }

    public function clipboardText(string $text): bool
    {
        $tmp = tempnam(sys_get_temp_dir(), 'wa_clip_') . '.txt';
        file_put_contents($tmp, $text);
        $escaped = str_replace("'", "''", $tmp);
        $ok = $this->ps(
            "\$t = [IO.File]::ReadAllText('{$escaped}', [Text.UTF8Encoding]::new(\$false)); " .
            "Set-Clipboard -Value \$t"
        );
        @unlink($tmp);
        return $ok;
    }

    public function clipboardFile(string $absolutePath): bool
    {
        if (!is_file($absolutePath)) {
            return false;
        }
        $escaped = str_replace("'", "''", $absolutePath);
        return $this->ps("Set-Clipboard -Path '{$escaped}'");
    }

    public function sendKeys(string $keys): bool
    {
        $escaped = str_replace("'", "''", $keys);
        return $this->ps(
            "Add-Type -AssemblyName System.Windows.Forms; " .
            "[System.Windows.Forms.SendKeys]::SendWait('{$escaped}')"
        );
    }

    public function primeChatInputFocus(): bool { return $this->sendKeys(' {BACKSPACE}'); }
    public function clearInputField(): bool     { return $this->sendKeys('^a{DELETE}'); }
    public function paste(): bool               { return $this->sendKeys('^v'); }
    public function pressEnter(): bool          { return $this->sendKeys('{ENTER}'); }

    public static function cleanPhone(string $raw): string
    {
        $s = preg_replace('/[\s\-\+\(\)]/', '', trim($raw)) ?? '';
        if (strlen($s) === 10 && preg_match('/^0[567]/', $s)) {
            $s = '212' . substr($s, 1);
        }
        return $s;
    }

    public static function isFaxNumber(string $raw): bool
    {
        $s = preg_replace('/[\s\-\+\(\)]/', '', trim($raw)) ?? '';
        if (str_starts_with($s, '2125')) return true;
        return strlen($s) === 10 && str_starts_with($s, '05');
    }

    public static function buildWhatsAppUri(string $phone): string
    {
        return 'whatsapp://send?' . http_build_query(['phone' => self::cleanPhone($phone)]);
    }
}
