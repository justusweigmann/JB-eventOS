<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\SeatingCharts;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Exceptions\SeatNotAvailableException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Domain\Seating\SeatHoldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignSeatAction extends BaseAction
{
    public function __construct(
        private readonly SeatHoldService $seatHoldService,
    )
    {
    }

    public function __invoke(int $eventId, int $seatingChartId, int $seatId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $validated = $request->validate([
            'attendee_id' => 'required|integer',
            'product_id' => 'nullable|integer',
            'session_token' => 'required|string|max:64',
        ]);

        try {
            $this->seatHoldService->confirmHold(
                seatId: $seatId,
                sessionToken: $validated['session_token'],
                attendeeId: $validated['attendee_id'],
                productId: $validated['product_id'] ?? null,
            );
        } catch (SeatNotAvailableException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        return $this->jsonResponse(['message' => 'Seat assigned successfully']);
    }
}
