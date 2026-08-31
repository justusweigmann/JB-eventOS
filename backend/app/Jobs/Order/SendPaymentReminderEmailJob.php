<?php

namespace HiEvents\Jobs\Order;

use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\Services\Domain\Mail\SendPaymentReminderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly OrderDomainObject $order,
        private readonly int $hoursUntilExpiry,
    ) {}

    public function handle(SendPaymentReminderService $service): void
    {
        $service->sendPaymentReminderEmail($this->order, $this->hoursUntilExpiry);
    }
}