<?php

namespace HiEvents\Exports;

use Carbon\Carbon;
use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\Enums\QuestionTypeEnum;
use HiEvents\DomainObjects\Enums\TicketType;
use HiEvents\DomainObjects\QuestionDomainObject;
use HiEvents\DomainObjects\TicketDomainObject;
use HiEvents\DomainObjects\TicketPriceDomainObject;
use HiEvents\Resources\Attendee\AttendeeResource;
use HiEvents\Services\Domain\Question\QuestionAnswerFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    private LengthAwarePaginator|Collection $data;
    private Collection $questions;

    public function __construct(private QuestionAnswerFormatter $questionAnswerFormatter)
    {
    }

    public function withData(LengthAwarePaginator|Collection $data, Collection $questions): AttendeesExport
    {
        $this->data = $data;
        $this->questions = $questions;
        return $this;
    }

    public function collection(): AnonymousResourceCollection
    {
        return AttendeeResource::collection($this->data);
    }

    public function headings(): array
    {
        $questionTitles = $this->questions->map(fn($question) => $question->getTitle())->toArray();

        return array_merge([
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Status',
            'Is Checked In',
            'Checked In At',
            'Ticket ID',
            'Ticket Name',
            'Event ID',
            'Public ID',
            'Short ID',
            'Created Date',
            'Last Updated Date'
        ], $questionTitles);
    }

    /**
     * @param AttendeeDomainObject $attendee
     * @return array
     */
    public function map($attendee): array
    {
        $answers = $this->questions->map(function (QuestionDomainObject $question) use ($attendee) {
            $answer = $attendee->getQuestionAndAnswerViews()
                ->first(fn($qav) => $qav->getQuestionId() === $question->getId())?->getAnswer() ?? '';

            return $this->questionAnswerFormatter->getAnswerAsText(
                $answer,
                QuestionTypeEnum::fromName($question->getType()),
            );
        });

        /** @var TicketDomainObject $ticket */
        $ticket = $attendee->getTicket();
        $ticketName = $ticket->getTitle();
        if ($attendee->getTicket()?->getType() === TicketType::TIERED->name) {
            $ticketName .= ' - ' . $ticket
                    ->getTicketPrices()
                    ->first(fn(TicketPriceDomainObject $tp) => $tp->getId() === $attendee->getTicketPriceId())
                    ->getLabel();
        }

        return array_merge([
            $attendee->getId(),
            $attendee->getFirstName(),
            $attendee->getLastName(),
            $attendee->getEmail(),
            $attendee->getStatus(),
            $attendee->getCheckIn() ? 'Yes' : 'No',
            $attendee->getCheckIn()
                ? Carbon::parse($attendee->getCheckIn()->getCreatedAt())->format('Y-m-d H:i:s')
                : '',
            $attendee->getTicketId(),
            $ticketName,
            $attendee->getEventId(),
            $attendee->getPublicId(),
            $attendee->getShortId(),
            Carbon::parse($attendee->getCreatedAt())->format('Y-m-d H:i:s'),
            Carbon::parse($attendee->getUpdatedAt())->format('Y-m-d H:i:s'),
        ], $answers->toArray());
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
