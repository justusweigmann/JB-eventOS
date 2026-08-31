<?php

namespace HiEvents\Console;

use HiEvents\Jobs\Account\ProcessScheduledAccountDeletionsJob;
use HiEvents\Jobs\Message\SendScheduledMessagesJob;
use HiEvents\Jobs\Order\SendPaymentReminderEmailJob;
use HiEvents\Jobs\Waitlist\ProcessExpiredWaitlistOffersJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Every minute
        $schedule->job(new SendScheduledMessagesJob())
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->job(new ProcessExpiredWaitlistOffersJob())
            ->everyMinute()
            ->withoutOverlapping();

        // Every hour
        $schedule->job(new ProcessScheduledAccountDeletionsJob())
            ->hourly()
            ->withoutOverlapping();

        // Nightly at 02:00 - Payment reminder emails
        $schedule->job(SendPaymentReminderEmailJob::class)
            ->dailyAt('02:00')
            ->withoutOverlapping();

        // Failed jobs monitor - every 5 minutes
        $schedule->call(function (): void {
            $failedJobs = DB::table('failed_jobs')
                ->select('id', 'uuid', 'connection', 'queue', 'payload', 'exception', 'failed_at')
                ->orderByDesc('failed_at')
                ->limit(10)
                ->get();

            if ($failedJobs->isNotEmpty()) {
                $jobDetails = $failedJobs->map(function ($job) {
                    $payload = json_decode($job->payload, true);
                    $jobClass = $payload['data']['command'] ?? 'Unknown';
                    $exceptionLines = explode("\n", $job->exception);
                    $exceptionMessage = trim($exceptionLines[0] ?? 'Unknown error');

                    return [
                        'id' => $job->id,
                        'uuid' => $job->uuid,
                        'class' => $jobClass,
                        'queue' => $job->queue,
                        'connection' => $job->connection,
                        'failed_at' => $job->failed_at,
                        'error' => $exceptionMessage,
                    ];
                })->toArray();

                Log::warning('Failed jobs present in queue', [
                    'count' => $failedJobs->count(),
                    'jobs' => $jobDetails,
                ]);
            }
        })->everyFiveMinutes()
            ->name('failed-jobs-monitor')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        include base_path('routes/console.php');
    }
}