<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TaskHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTaskWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $taskHistory;

    public function __construct(TaskHistory $taskHistory)
    {
        $this->taskHistory = $taskHistory;
    }

    public function handle(): void
    {
        $webhookUrl = env('WEBHOOK_TARGET_URL');

        if (!$webhookUrl) {
            Log::warning('WEBHOOK_TARGET_URL não está definida. O webhook não será enviado.');
            return;
        }

        try {
            Http::timeout(15)->post($webhookUrl, [
                'event' => 'task_updated',
                'timestamp' => now()->toIso8601String(),
                'data' => $this->taskHistory->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Falha ao enviar webhook: ' . $e->getMessage());
            $this->release(60);
        }
    }
}
