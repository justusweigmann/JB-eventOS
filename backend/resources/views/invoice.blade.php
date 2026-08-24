@php use Carbon\Carbon; @endphp
@php use HiEvents\DomainObjects\Status\InvoiceStatus; @endphp
@php /** @var \HiEvents\DomainObjects\EventDomainObject $event */ @endphp
@php /** @var \HiEvents\DomainObjects\EventSettingDomainObject $eventSettings */ @endphp
@php /** @var \HiEvents\DomainObjects\OrderDomainObject $order */ @endphp
@php /** @var \HiEvents\DomainObjects\InvoiceDomainObject $invoice */ @endphp


@php
    $isPaid = $invoice->getStatus() === InvoiceStatus::PAID->name;
    $isVoid = $invoice->getStatus() === InvoiceStatus::VOID->name;

    $statusClass = $isPaid
        ? 'status-paid'
        : ($isVoid ? 'status-void' : 'status-unpaid');

    $statusLabel = $isPaid
        ? 'Bezahlt'
        : ($isVoid ? 'Storniert' : 'Offen');

    $issueDate = $invoice->getIssueDate()
        ? Carbon::parse($invoice->getIssueDate())->format('d.m.Y')
        : null;

    $dueDate = $invoice->getDueDate()
        ? Carbon::parse($invoice->getDueDate())->format('d.m.Y')
        : null;

    $currency = strtoupper($order->getCurrency());

    $formatAmount = function ($amount) use ($currency) {
        $formatter = new \NumberFormatter(
            'de_DE',
            \NumberFormatter::CURRENCY
        );

        return $formatter->formatCurrency(
            (float) $amount,
            $currency
        );
    };
@endphp


<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $eventSettings->getInvoiceLabel() ?? 'Rechnung' }}
        {{ $invoice->getInvoiceNumber() }}
    </title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1a1a1a;
            padding: 15px 20px;
            display: flex;
            flex-direction: column;
        }

        .content-wrapper {
            flex: 1;
        }

        table {
            border-collapse: collapse;
        }

        .header-table {
            width: 100%;
            margin-bottom: 18px;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo-title {
            font-size: 22px;
            line-height: 1.15;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header-event-name {
            margin-top: 6px;
            font-size: 10px;
            color: #666;
        }

        .company-details {
            text-align: right;
            line-height: 1.5;
            color: #555;
        }

        .company-name {
            color: #1a1a1a;
            font-weight: bold;
        }

        .invoice-info-grid-wrapper {
            width: 100%;
            max-width: 900px;
            margin: 0 auto 16px;
            border: 1px solid #dedede;
            border-radius: 7px;
            overflow: hidden;
        }

        .invoice-info-grid {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            text-align: center;
        }

        .invoice-info-grid td {
            width: 20%;
            height: 60px;
            padding: 10px 12px;
            vertical-align: middle;
            border: 0;
            text-align: center;
        }

        .info-label {
            display: block;
            margin-bottom: 6px;
            color: #777;
            font-size: 9px;
            line-height: 1.1;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.25px;
        }

        .info-value {
            display: block;
            color: #1a1a1a;
            font-size: 11px;
            line-height: 1.25;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid transparent;
            border-radius: 5px;
            font-size: 9px;
            line-height: 1.2;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-paid {
            color: #1a7d42;
            background: #e6f9ee;
            border-color: #b8e6cc;
        }

        .status-unpaid {
            color: #a76700;
            background: #fff3e0;
            border-color: #ffe0b2;
        }

        .status-void {
            color: #888;
            background: #f5f5f5;
            border-color: #ddd;
        }

        .billing-section {
            width: 53%;
            min-height: 100px;
            margin-bottom: 18px;
            padding: 12px 20px;
            background: #f9f9fb;
            border-radius: 4px;
        }

        .billing-title {
            margin-bottom: 8px;
            color: #777;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .billing-name {
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .billing-section div:not(.billing-title):not(.billing-name) {
            margin-bottom: 3px;
        }

        .items {
            width: 100%;
            margin: 0 0 18px;
        }

        .items th {
            padding: 8px 12px;
            color: #555;
            background: #f9f9fb;
            border-bottom: 2px solid #e6e6e6;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .items td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .col-description {
            width: 55%;
        }

        .col-rate {
            width: 15%;
        }

        .col-qty {
            width: 15%;
        }

        .col-amount {
            width: 15%;
        }

        .align-right {
            text-align: right !important;
        }

        .item-description {
            margin-top: 3px;
            color: #888;
            font-size: 9px;
        }

        .item-price-original {
            margin-bottom: 2px;
            color: #999;
            font-size: 9px;
            text-decoration: line-through;
        }

        .totals {
            width: 350px;
            margin: 0 0 0 auto;
        }

        .totals td {
            padding: 5px 10px;
            text-align: right;
        }

        .subtotal td {
            padding-top: 8px;
            font-weight: bold;
        }

        .breakdown td {
            color: #888;
            font-size: 9px;
        }

        .total-line td {
            padding-top: 10px;
            border-top: 2px solid #1a1a1a;
            font-size: 12px;
            font-weight: bold;
        }

        .amount-paid-line td {
            padding-top: 6px;
            color: #1a7d42;
            font-size: 10px;
        }

        .balance-due-line td {
            padding-top: 8px;
            border-top: 1px solid #e6e6e6;
            font-size: 12px;
            font-weight: bold;
        }

        .invoice-notes {
            margin-top: 24px;
            padding: 14px 20px;
            background: #f9f9fb;
            border-radius: 4px;
            color: #333;
            line-height: 1.6;
        }

        .payment-instructions {
            margin-top: 12px;
            line-height: 1.5;
        }

        .payment-instructions p {
            margin: 0 0 6px;
        }

        .payment-instructions p:last-child {
            margin-bottom: 0;
        }

        .payment-instructions strong,
        .payment-instructions b {
            font-weight: bold;
        }

        .payment-instructions em,
        .payment-instructions i {
            font-style: italic;
        }

        .payment-instructions ul,
        .payment-instructions ol {
            margin: 6px 0 6px 20px;
            padding-left: 12px;
        }

        .payment-instructions li {
            margin-bottom: 3px;
        }

        .payment-instructions br {
            line-height: 1.5;
        }

        .payment-instructions a {
            color: #1a1a1a;
            text-decoration: underline;
        }

        .invoice-footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid #e6e6e6;
            color: #888;
            text-align: center;
            font-size: 9px;
            line-height: 1.5;
        }

        .tax-info {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px dashed #e6e6e6;
        }

        @media print {
            body {
                padding: 12px;
            }

            .invoice-info-grid-wrapper,
            .billing-section,
            .items th,
            .invoice-notes {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <h1 class="logo-title">
                        {{ $eventSettings->getInvoiceLabel() ?? 'Rechnung' }}
                    </h1>

                    <p class="header-event-name">
                        {{ $event->getTitle() }}
                    </p>
                </td>

                <td class="company-details">
                    <div class="company-name">
                        {{ $eventSettings->getOrganizationName() }}
                    </div>

                    <div>
                        {!! $eventSettings->getOrganizationAddress() !!}
                    </div>

                    @if($eventSettings->getSupportEmail())
                        <div>
                            {{ $eventSettings->getSupportEmail() }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="invoice-info-grid-wrapper">
            <table class="invoice-info-grid">
                <tbody>
                    <tr>
                        <td>
                            <span class="info-label">Rechnungsnummer</span>
                            <span class="info-value">
                                {{ $invoice->getInvoiceNumber() }}
                            </span>
                        </td>

                        <td>
                            <span class="info-label">Ausstellungsdatum</span>
                            <span class="info-value">
                                {{ $issueDate }}
                            </span>
                        </td>

                        <td>
                            <span class="info-label">Fälligkeitsdatum</span>
                            <span class="info-value">
                                {{ (!$isPaid && !$isVoid && $dueDate) ? $dueDate : '—' }}
                            </span>
                        </td>

                        <td>
                            <span class="info-label">
                                {{ $isPaid ? 'Bezahlt' : 'Betrag offen' }}
                            </span>
                            <span class="info-value">
                                {{ $formatAmount($order->getTotalGross()) }}
                            </span>
                        </td>

                        <td>
                            <span class="info-label">Status</span>
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="billing-section">
            <div class="billing-title">Rechnungsempfänger</div>

            <div class="billing-name">
                {{ $order->getFullName() }}
            </div>

            @if($order->getAddress())
                <div>
                    {{ $order->getBillingAddressString() }}
                </div>
            @endif

            <div>
                {{ $order->getEmail() }}
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th class="col-description">Beschreibung</th>
                    <th class="col-rate align-right">Einzelpreis</th>
                    <th class="col-qty align-right">Menge</th>
                    <th class="col-amount align-right">Betrag</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $totalDiscount = 0;
                @endphp

                @foreach($invoice->getItems() as $orderItem)
                    @php
                        $itemDiscount = 0;

                        if ($orderItem['price_before_discount']) {
                            $itemDiscount = (
                                $orderItem['price_before_discount'] - $orderItem['price']
                            ) * $orderItem['quantity'];

                            $totalDiscount += $itemDiscount;
                        }
                    @endphp

                    <tr>
                        <td>
                            {{ $orderItem['item_name'] }}

                            @if(!empty($orderItem['description']))
                                <div class="item-description">
                                    {{ $orderItem['description'] }}
                                </div>
                            @endif
                        </td>

                        <td class="align-right">
                            @if($orderItem['price_before_discount'])
                                <div class="item-price-original">
                                    {{ $formatAmount($orderItem['price_before_discount']) }}
                                </div>

                                <div>
                                    {{ $formatAmount($orderItem['price']) }}
                                </div>
                            @else
                                {{ $formatAmount($orderItem['price']) }}
                            @endif
                        </td>

                        <td class="align-right">
                            {{ $orderItem['quantity'] }}
                        </td>

                        <td class="align-right">
                            @if($orderItem['price_before_discount'])
                                <div class="item-price-original">
                                    {{ $formatAmount(
                                        $orderItem['price_before_discount'] * $orderItem['quantity']
                                    ) }}
                                </div>

                                <div>
                                    {{ $formatAmount($orderItem['total_before_additions']) }}
                                </div>
                            @else
                                {{ $formatAmount($orderItem['total_before_additions']) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr class="subtotal">
                <td>Zwischensumme</td>
                <td>
                    {{ $formatAmount($order->getTotalBeforeAdditions()) }}
                </td>
            </tr>

            @if($totalDiscount > 0)
                <tr class="breakdown">
                    <td>Rabatt</td>
                    <td>
                        {{ $formatAmount(-$totalDiscount) }}
                    </td>
                </tr>
            @endif

            @if($order->getHasTaxes())
                @foreach($order->getTaxesAndFeesRollup()['taxes'] as $tax)
                    <tr class="breakdown">
                        <td>
                            {{ $tax['name'] }}
                            ({{ $tax['rate'] }}@if($tax['type'] === 'PERCENTAGE')%@else {{ $currency }}@endif)
                        </td>

                        <td>
                            {{ $formatAmount($tax['value']) }}
                        </td>
                    </tr>
                @endforeach

                <tr class="subtotal">
                    <td>Steuern gesamt</td>
                    <td>
                        {{ $formatAmount($order->getTotalTax()) }}
                    </td>
                </tr>
            @endif

            @if($order->getHasFees())
                @foreach($order->getTaxesAndFeesRollup()['fees'] as $fee)
                    <tr class="breakdown">
                        <td>
                            {{ $fee['name'] }}
                            ({{ $fee['rate'] }}@if($fee['type'] === 'PERCENTAGE')%@else {{ $currency }}@endif)
                        </td>

                        <td>
                            {{ $formatAmount($fee['value']) }}
                        </td>
                    </tr>
                @endforeach

                <tr class="subtotal">
                    <td>Servicegebühren gesamt</td>
                    <td>
                        {{ $formatAmount($order->getTotalFee()) }}
                    </td>
                </tr>
            @endif

            <tr class="total-line">
                <td>Gesamtbetrag</td>
                <td>
                    {{ $formatAmount($order->getTotalGross()) }}
                </td>
            </tr>

            @if($isPaid)
                <tr class="amount-paid-line">
                    <td>Bezahlt</td>
                    <td>
                        {{ $formatAmount(-$order->getTotalGross()) }}
                    </td>
                </tr>

                <tr class="balance-due-line">
                    <td>Offener Betrag</td>
                    <td>
                        {{ $formatAmount(0) }}
                    </td>
                </tr>
            @endif
        </table>

        @if(!$isPaid && !$isVoid && $invoice->getDueDate())
            <div class="invoice-notes">
                <p>
                    Für die Bestätigung Deiner Reservierung erwarten wir eine Überweisung bis zum
                    <strong>{{ $dueDate }}</strong>
                    unter Angabe der Rechnungs-Nr.
                    <strong>{{ $invoice->getInvoiceNumber() }}</strong>
                    an:
                </p>

                @if($eventSettings->getOfflinePaymentInstructions())
                    <div class="payment-instructions">
                        {!! $eventSettings->getOfflinePaymentInstructions() !!}
                    </div>
                @endif
            </div>
        @endif

        @if($eventSettings->getInvoiceNotes())
            <div class="invoice-notes">
                {!! $eventSettings->getInvoiceNotes() !!}
            </div>
        @endif
    </div>

    <div class="invoice-footer">
        @if($eventSettings->getSupportEmail())
            <p>
                Bei Fragen erreichst Du uns unter
                {{ $eventSettings->getSupportEmail() }}
            </p>
        @endif

        @if((bool) $eventSettings->getInvoiceTaxDetails())
            <div class="tax-info">
                <p style="text-align: center;">
                    <strong>Steuerinformationen:</strong>
                    {!! $eventSettings->getInvoiceTaxDetails() !!}
                </p>
            </div>
        @endif
    </div>
</body>
</html>