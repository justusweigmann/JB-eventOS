<?php

namespace HiEvents\Jobs\Order;

use HiEvents\Models\Order;
use HiEvents\Services\Domain\Mail\SendPaymentReminderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendPaymentReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(SendPaymentReminderService $service): void
    {
        $now = Carbon::now();

        $targetDates = [
            $now->copy()->addDays(3)->toDateString(),
            $now->copy()->addDay()->toDateString(),
        ];

        Log::info('Payment Reminder Job started', [
            'target_dates' => $targetDates,
            'payment_status' => 'AWAITING_PAYMENT',
        ]);

        $ordersProcessed = 0;
        $orderIds = [];

        Order::query()
            ->where('payment_status', 'AWAITING_PAYMENT')
            ->whereHas('invoices', function ($query) use ($targetDates): void {
                $query
                    ->where('status', 'UNPAID')
                    ->where(function ($query) use ($targetDates): void {
                        $query
                            ->whereDate('due_date', $targetDates[0])
                            ->orWhereDate('due_date', $targetDates[1]);
                    });
            })
            ->with('invoices')
            ->chunkById(1000, function ($orders) use (
                $now,
                $service,
                &$ordersProcessed,
                &$orderIds
            ): void {
                foreach ($orders as $order) {
                    $invoice = $order->invoices->first();

                    if (!$invoice) {
                        continue;
                    }

                    $hoursUntilExpiry = $now->diffInHours(
                        Carbon::parse($invoice->due_date),
                        false
                    );

                    $service->sendPaymentReminderEmail(
                        $order->toDomainObject(),
                        $hoursUntilExpiry
                    );

                    $ordersProcessed++;
                    $orderIds[] = $order->id;
                }
            });

        Log::info('Payment Reminder Job completed', [
            'orders_processed' => $ordersProcessed,
            'order_ids' => $orderIds,
            'target_dates' => $targetDates,
        ]);
    }
}