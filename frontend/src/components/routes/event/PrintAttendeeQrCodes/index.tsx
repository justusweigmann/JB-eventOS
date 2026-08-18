import {useParams, useSearchParams} from 'react-router';
import {useGetAttendees} from '../../../../queries/useGetAttendees.ts';
import {Attendee, QueryFilters} from '../../../../types.ts';
import {useEffect, useMemo, useState} from 'react';
import QRCode from 'react-qr-code';
import {t} from '@lingui/macro';
import classes from './PrintAttendeeQrCodes.module.scss';

// Gleiche Definitionen wie im Backend (PrintAttendeeQrCodesAction.php),
// damit Vorschau und PDF/Ausdruck exakt übereinstimmen.
// Randwerte (marginTop/marginLeft) bitte gegen die offizielle Avery-Vorlage
// für den jeweiligen Artikel prüfen.
const AVERY_TEMPLATES = {
    // Quadratische Etiketten – ideal für kleine Aufkleber auf Karten/Boxen
    'L7120-25': {label: 'Avery L7120-25 – 35 × 35 mm (35/Bogen)', width: 35.0, height: 35.0, columns: 5, rows: 7, marginTop: 26.0, marginLeft: 12.5, colGap: 2.5, rowGap: 0},
    'L7121-25': {label: 'Avery L7121-25 – 45 × 45 mm (20/Bogen)', width: 45.0, height: 45.0, columns: 4, rows: 5, marginTop: 36.0, marginLeft: 11.25, colGap: 2.5, rowGap: 0},

    // Rechteckige Etiketten – klassische Adress-/Beschriftungsformate, gut für Karten geeignet
    'L7163': {label: 'Avery L7163 – 99,1 × 38,1 mm (14/Bogen)', width: 99.1, height: 38.1, columns: 2, rows: 7, marginTop: 15.15, marginLeft: 4.65, colGap: 2.5, rowGap: 0},
    '3653-200': {label: 'Avery 3653-200 – 105 × 42,3 mm (14/Bogen)', width: 105.0, height: 42.3, columns: 2, rows: 7, marginTop: 0.45, marginLeft: 0.0, colGap: 0, rowGap: 0},
    '3424': {label: 'Avery 3424 – 105 × 48 mm (12/Bogen)', width: 105.0, height: 48.0, columns: 2, rows: 6, marginTop: 4.5, marginLeft: 0.0, colGap: 0, rowGap: 0},

    // Visitenkarten – direkt als "Karte" konzipiert, ideal für Namens-/Adresslabels
    'C32011-10': {label: 'Avery C32011-10 – Visitenkarten 85 × 54 mm (10/Bogen)', width: 85.0, height: 54.0, columns: 2, rows: 5, marginTop: 13.5, marginLeft: 20.0, colGap: 0, rowGap: 0},

    // Runde Etiketten – Raster berechnet (Avery nennt nur Stückzahl/Bogen)
    '45-RND': {label: 'Avery 45-RND – Ø 45 mm rund (20/Bogen, berechnet)', width: 45.0, height: 45.0, columns: 4, rows: 5, marginTop: 36.0, marginLeft: 11.25, colGap: 2.5, rowGap: 0},
    '60-RND': {label: 'Avery 60-RND – Ø 60 mm rund (12/Bogen, berechnet)', width: 60.0, height: 60.0, columns: 3, rows: 4, marginTop: 28.5, marginLeft: 12.5, colGap: 2.5, rowGap: 0},
} as const;

type AveryTemplateKey = keyof typeof AVERY_TEMPLATES;

const ALL_ATTENDEES_PER_PAGE = 1000;

const PrintAttendeeQrCodes = () => {
    const {eventId} = useParams();
    const [searchParams, setSearchParams] = useSearchParams();

    const templateParam = (searchParams.get('avery_template') as AveryTemplateKey) || 'L7163';
    const template = AVERY_TEMPLATES[templateParam] ?? AVERY_TEMPLATES['L7163'];

    const [readyCount, setReadyCount] = useState(0);
    const [hasPrinted, setHasPrinted] = useState(false);

    const queryFilters: QueryFilters = useMemo(
        () => ({
            pageNumber: 1,
            perPage: ALL_ATTENDEES_PER_PAGE,
        }),
        [],
    );

    const {data} = useGetAttendees(eventId, queryFilters);
    const attendees = data?.data;

    const handleTemplateChange = (key: AveryTemplateKey) => {
        setHasPrinted(false);
        setReadyCount(0);
        const next = new URLSearchParams(searchParams);
        next.set('avery_template', key);
        setSearchParams(next, {replace: true});
    };

    useEffect(() => {
        if (!attendees?.length || hasPrinted) {
            return;
        }

        if (readyCount >= attendees.length) {
            setHasPrinted(true);
            // Kurzer Tick, damit der letzte Layout-Reflow abgeschlossen ist.
            const timer = setTimeout(() => window?.print(), 50);
            return () => clearTimeout(timer);
        }
    }, [attendees, readyCount, hasPrinted]);

    if (!attendees) {
        return <div className={classes.loading}>{t`Loading attendees...`}</div>;
    }

    return (
        <div className={classes.container}>
            <div className={classes.toolbar} data-print-hide="true">
                <h1 className={classes.title}>{t`Attendee QR Codes`}</h1>
                <label className={classes.templateSelect}>
                    {t`Etiketten-Vorlage`}
                    <select
                        value={templateParam}
                        onChange={(event) => handleTemplateChange(event.target.value as AveryTemplateKey)}
                    >
                        {Object.entries(AVERY_TEMPLATES).map(([key, tpl]) => (
                            <option key={key} value={key}>
                                {tpl.label}
                            </option>
                        ))}
                    </select>
                </label>
            </div>

            <style>{`
                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 0;
                    }
                    [data-print-hide="true"] {
                        display: none !important;
                    }
                }
            `}</style>

            <div
                className={classes.sheet}
                style={{
                    paddingTop: `${template.marginTop}mm`,
                    paddingLeft: `${template.marginLeft}mm`,
                }}
            >
                {attendees.map((attendee: Attendee, index: number) => (
                    <div
                        key={attendee.id}
                        className={classes.card}
                        style={{
                            width: `${template.width}mm`,
                            height: `${template.height}mm`,
                            marginRight: `${template.colGap}mm`,
                            marginBottom: `${template.rowGap}mm`,
                            float: 'left',
                        }}
                    >
                        <div className={classes.qrWrapper}>
                            <QRCode
                                value={String(attendee.public_id)}
                                size={120}
                                level="M"
                                style={{
                                    height: `${Math.min(template.height - 4, 25)}mm`,
                                    width: `${Math.min(template.height - 4, 25)}mm`,
                                }}
                                onLoad={() => setReadyCount((count) => count + 1)}
                            />
                        </div>
                        <div className={classes.info}>
                            <div className={classes.name}>
                                {attendee.first_name} {attendee.last_name}
                            </div>
                            <div className={classes.detail}>{attendee.short_id}</div>
                        </div>
                        {(index + 1) % template.columns === 0 && <div style={{clear: 'both'}} />}
                    </div>
                ))}
            </div>
        </div>
    );
};

export default PrintAttendeeQrCodes;