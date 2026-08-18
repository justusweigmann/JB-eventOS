<?php

namespace HiEvents\Http\Actions\Attendees;

use Barryvdh\DomPDF\Facade\Pdf;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class PrintAttendeeQrCodesAction extends BaseAction
{
    /**
     * Zentrale Definition aller unterstützten Avery-Zweckform-Bögen.
     * Werte (Ränder/Abstände) bitte 1:1 aus der offiziellen Avery-Vorlage
     * (Software-Code Download auf avery-zweckform.com/etiketten-vorlagen)
     * für den jeweiligen Artikel übernehmen, damit der Druck exakt auf
     * die Stanzung passt.
     */
    private const AVERY_TEMPLATES = [
        // Artikel-Nr => [Breite, Höhe, Spalten, Zeilen, oben, links, horiz. Abstand, vert. Abstand] in mm
        'L7120-25' => ['width' => 35.0,  'height' => 35.0,  'columns' => 5, 'rows' => 5, 'marginTop' => 21.5, 'marginLeft' => 17.5, 'colGap' => 2.5, 'rowGap' => 0],
        'L7121-25' => ['width' => 45.0,  'height' => 45.0,  'columns' => 4, 'rows' => 5, 'marginTop' => 21.5, 'marginLeft' => 12.5, 'colGap' => 2.5, 'rowGap' => 0],
        'L7163'    => ['width' => 99.1,  'height' => 38.1,  'columns' => 2, 'rows' => 7, 'marginTop' => 15.15, 'marginLeft' => 4.65, 'colGap' => 2.5, 'rowGap' => 0],
        '105x42'   => ['width' => 105.0, 'height' => 42.3,  'columns' => 2, 'rows' => 6, 'marginTop' => 21.0,  'marginLeft' => 0.0,  'colGap' => 0,   'rowGap' => 0],
        '105x48'   => ['width' => 105.0, 'height' => 48.0,  'columns' => 2, 'rows' => 6, 'marginTop' => 1.0,   'marginLeft' => 0.0,  'colGap' => 0,   'rowGap' => 0],
    ];

    public function __construct(
        private readonly AttendeeRepositoryInterface $attendeeRepository,
    ) {
    }

    public function __invoke(Request $request, int $eventId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $attendeeIds = $request->input('attendee_ids', []);
        $templateKey = $request->input('avery_template', 'L7163');

        if (!array_key_exists($templateKey, self::AVERY_TEMPLATES)) {
            throw ValidationException::withMessages([
                'avery_template' => sprintf(
                    'Unbekannte Vorlage "%s". Erlaubt: %s',
                    $templateKey,
                    implode(', ', array_keys(self::AVERY_TEMPLATES)),
                ),
            ]);
        }

        $template = self::AVERY_TEMPLATES[$templateKey];

        if (empty($attendeeIds)) {
            $attendees = $this->attendeeRepository->findWhere([
                'event_id' => $eventId,
            ]);
        } else {
            // Wichtig: zusätzlich event_id prüfen, damit keine Attendee-IDs
            // aus fremden Events mit ausgedruckt werden können (IDOR-Fix).
            $attendees = $this->attendeeRepository->findWhereIn(
                'id',
                $attendeeIds,
                ['event_id' => $eventId],
            );
        }

        $pdf = Pdf::loadView('qr-codes.attendee-labels', [
            'attendees'   => $attendees,
            'template'    => $templateKey,
            'labelWidth'  => $template['width'],
            'labelHeight' => $template['height'],
            'columns'     => $template['columns'],
            'rows'        => $template['rows'],
            'marginTop'   => $template['marginTop'],
            'marginLeft'  => $template['marginLeft'],
            'colGap'      => $template['colGap'],
            'rowGap'      => $template['rowGap'],
            'eventId'     => $eventId,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream(sprintf('qr-codes-event-%d.pdf', $eventId));
    }
}