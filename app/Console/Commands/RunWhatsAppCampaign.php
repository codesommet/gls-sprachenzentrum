<?php

namespace App\Console\Commands;

use App\Models\WhatsAppCampaign;
use App\Services\WhatsApp\CampaignRuntime;
use App\Services\WhatsApp\WindowsAutomation;
use Illuminate\Console\Command;

class RunWhatsAppCampaign extends Command
{
    protected $signature = 'wa:run-campaign {id : Campaign DB id to execute}';
    protected $description = 'Drive WhatsApp Desktop for a stored campaign (spawned detached by the controller)';

    public function handle(CampaignRuntime $runtime, WindowsAutomation $auto): int
    {
        $id = (int) $this->argument('id');
        $campaign = WhatsAppCampaign::find($id);
        if (!$campaign) {
            $this->error("Campaign not found: {$id}");
            return 1;
        }

        $runtime->writeControl(['paused' => false, 'aborted' => false]);
        $campaign->status = 'running';
        if (!$campaign->started_at) $campaign->started_at = now();
        $campaign->save();
        $this->tick($runtime, $campaign, null);

        $minD = max(30, (int) $campaign->delay_min);
        $maxD = max($minD + 10, (int) $campaign->delay_max);
        $launchWait = max(3, (int) $campaign->launch_wait);
        $attachPath = (string) ($campaign->attachment_path ?? '');
        $message = (string) $campaign->message;

        $recipients = $campaign->recipients ?? [];
        $total = count($recipients);

        for ($i = 0; $i < $total; $i++) {
            $control = $runtime->readControl();
            if ($control['aborted']) { $this->line('Aborted'); break; }
            while ($control['paused'] && !$control['aborted']) {
                usleep(500_000);
                $control = $runtime->readControl();
            }
            if ($control['aborted']) break;

            $entry = $recipients[$i];
            $status = $entry['status'] ?? 'pending';
            if (in_array($status, ['sent', 'failed', 'skipped'], true)) continue;

            $phone = (string) ($entry['phone'] ?? '');

            if (WindowsAutomation::isFaxNumber($phone)) {
                $recipients[$i]['status'] = 'skipped';
                $recipients[$i]['error']  = 'fax/landline (no WhatsApp)';
                $campaign->failed += 1;
                $campaign->recipients = $recipients;
                $campaign->save();
                continue;
            }

            $recipients[$i]['status'] = 'sending';
            $campaign->recipients = $recipients;
            $campaign->save();
            $this->tick($runtime, $campaign, $phone);
            $this->info(sprintf('[%d/%d] -> %s', $i + 1, $total, $phone));

            try {
                $filledMessage = $this->fillPlaceholders($message, $entry);

                if ($attachPath !== '') {
                    if (!$auto->clipboardFile($attachPath)) throw new \RuntimeException('file clipboard failed');
                } else {
                    if (!$auto->clipboardText($filledMessage)) throw new \RuntimeException('clipboard text failed');
                }

                if (!$auto->launchUrl(WindowsAutomation::buildWhatsAppUri($phone))) {
                    throw new \RuntimeException('URI launch failed');
                }

                sleep($launchWait);

                if (!$auto->primeChatInputFocus()) throw new \RuntimeException('prime focus failed');
                usleep(400_000);
                if (!$auto->clearInputField()) throw new \RuntimeException('clear input failed');
                usleep(300_000);
                if (!$auto->paste()) throw new \RuntimeException('paste failed');

                if ($attachPath !== '') {
                    sleep(2);
                    if (!$auto->clipboardText($filledMessage)) throw new \RuntimeException('caption clipboard failed');
                    usleep(300_000);
                    if (!$auto->paste()) throw new \RuntimeException('caption paste failed');
                    usleep(800_000);
                } else {
                    usleep(800_000);
                }

                if (!$auto->pressEnter()) throw new \RuntimeException('enter failed');

                $recipients[$i]['status'] = 'sent';
                $recipients[$i]['sentAt'] = gmdate('c');
                $campaign->sent += 1;
            } catch (\Throwable $e) {
                $recipients[$i]['status'] = 'failed';
                $recipients[$i]['error']  = $e->getMessage();
                $campaign->failed += 1;
            }

            $campaign->recipients = $recipients;
            $campaign->save();

            $pending = 0;
            foreach ($recipients as $r) {
                if (($r['status'] ?? '') === 'pending') $pending++;
            }
            if ($pending > 0) {
                $wait = random_int($minD, $maxD);
                $nextAt = time() + $wait;
                $this->tick($runtime, $campaign, null, $nextAt);
                $this->line("  wait {$wait}s");
                while (time() < $nextAt) {
                    $c = $runtime->readControl();
                    if ($c['aborted']) break 2;
                    if ($c['paused']) $nextAt += 1;
                    usleep(500_000);
                }
            }
        }

        // Finalize
        $pending = 0;
        foreach ($recipients as $r) {
            if (($r['status'] ?? '') === 'pending') $pending++;
        }
        $control = $runtime->readControl();
        if ($control['aborted']) {
            $campaign->status = 'stopped';
        } elseif ($pending > 0) {
            $campaign->status = 'paused';
        } else {
            $campaign->status = 'completed';
            $campaign->finished_at = now();
        }
        $campaign->recipients = $recipients;
        $campaign->save();

        $runtime->writeStatus([
            'running'    => false,
            'campaignId' => $campaign->id,
            'status'     => $campaign->status,
        ]);
        $runtime->writeControl(['paused' => false, 'aborted' => false]);

        $this->info("Finished: {$campaign->status}");
        return 0;
    }

    private function fillPlaceholders(string $tmpl, array $entry): string
    {
        $business = (string) ($entry['business'] ?? ($entry['name'] ?? $entry['phone'] ?? ''));
        $phone    = (string) ($entry['phone'] ?? '');
        return str_replace(
            ['{business}', '{phone}', '{name}'],
            [$business, $phone, $business],
            $tmpl
        );
    }

    private function tick(CampaignRuntime $runtime, WhatsAppCampaign $campaign, ?string $currentPhone, ?int $nextSendAt = null): void
    {
        $recipients = $campaign->recipients ?? [];
        $pending = 0;
        foreach ($recipients as $r) {
            if (($r['status'] ?? '') === 'pending') $pending++;
        }
        $runtime->writeStatus([
            'running'        => true,
            'campaignId'     => $campaign->id,
            'name'           => $campaign->name,
            'status'         => $campaign->status,
            'total'          => $campaign->total,
            'sent'           => $campaign->sent,
            'failed'         => $campaign->failed,
            'pending'        => $pending,
            'current'        => $currentPhone,
            'nextSendAt'     => $nextSendAt,
            'attachmentPath' => $campaign->attachment_path,
            'messages'       => $recipients,
        ]);
    }
}
