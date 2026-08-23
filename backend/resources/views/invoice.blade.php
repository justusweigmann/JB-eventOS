@php use Carbon\Carbon; @endphp
@php use HiEvents\Helper\Currency; @endphp
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
        ? __('Paid')
        : ($isVoid ? __('Void') : __('Unpaid'));

    $issueDate = $invoice->getIssueDate()
        ? Carbon::parse($invoice->getIssueDate())->format('d.m.Y')
        : null;

    $dueDate = $invoice->getDueDate()
        ? Carbon::parse($invoice->getDueDate())->format('d.m.Y')
        : null;

    $currency = $order->getCurrency();
@endphp

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $eventSettings->getInvoiceLabel() ?? __('Invoice') }}
        #{{ $invoice->getInvoiceNumber() }}
    </title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body,
        table,
        td,
        th,
        div,
        span,
        p,
        strong {
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        body {
            font-size: 12px;
            line-height: 1.5;
            color: #1a1a1a;
            padding: 20px 26px;
        }

        table {
            border-collapse: collapse;
        }

        .header-table {
            width: 100%;
            margin-bottom: 24px;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo-title {
            font-size: 28px;
            line-height: 1.15;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .header-event-name {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
        }

        .company-details {
            text-align: right;
            line-height: 1.65;
            color: #555;
        }

        .company-name {
            color: #1a1a1a;
            font-weight: bold;
        }

        /*
         * Wrapper erhält den Außenrahmen.
         * Die Tabelle selbst hat keine Zellrahmen und deshalb keine vertikalen Linien.
         */
        .invoice-info-grid-wrapper {
            width: 100%;
            margin: 0 auto 20px;
            border: 1px solid #dedede;
            border-radius: 7px;
            overflow: hidden;
        }

        .invoice-info-grid {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            text-align: center;
        }

        .invoice-info-grid td {
            width: 20%;
            height: 78px;
            padding: 13px 14px;
            vertical-align: middle;
            border: 0;
            text-align: center;
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        .info-label {
            display: block;
            margin-bottom: 8px;
            color: #777;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.1;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.25px;
        }

        .info-value {
            display: block;
            color: #1a1a1a;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 13px;
            line-height: 1.25;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border: 1px solid transparent;
            border-radius: 5px;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
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
            min-height: 126px;
            margin-bottom: 22px;
            padding: 15px 24px;
            background: #f9f9fb;
            border-radius: 4px;
        }

        .billing-title {
            margin-bottom: 10px;
            color: #777;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .billing-name {
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: bold;
        }

        .billing-section div:not(.billing-title):not(.billing-name) {
            margin-bottom: 4px;
        }

        .items {
            width: 100%;
            margin: 0 0 22px;
        }

        .items th {
            padding: 10px 15px;
            color: #555;
            background: #f9f9fb;
            border-bottom: 2px solid #e6e6e6;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .items td {
            padding: 14px 15px;
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
            margin-top: 4px;
            color: #888;
            font-size: 11px;
        }

        .item-price-original {
            margin-bottom: 2px;
            color: #999;
            font-size: 11px;
            text-decoration: line-through;
        }

        .totals {
            width: 350px;
            margin: 0 0 0 auto;
        }

        .totals td {
            padding: 6px 10px;
            text-align: right;
        }

        .subtotal td {
            padding-top: 10px;
            font-weight: bold;
        }

        .breakdown td {
            color: #888;
            font-size: 11px;
        }

        .total-line td {
            padding-top: 12px;
            border-top: 2px solid #1a1a1a;
            font-size: 14px;
            font-weight: bold;
        }

        .amount-paid-line td {
            padding-top: 8px;
            color: #1a7d42;
            font-size: 12px;
        }

        .balance-due-line td {
            padding-top: 10px;
            border-top: 1px solid #e6e6e6;
            font-size: 14px;
            font-weight: bold;
        }

        .invoice-notes {
            margin-top: 30px;
            padding: 17px 26px;
            background: #f9f9fb;
            border-radius: 4px;
            color: #333;
            line-height: 1.7;
        }

        .payment-instructions {
            margin-top: 14px;
            line-height: 1.6;
        }

        .payment-instructions p {
            margin: 0 0 8px;
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
            margin: 8px 0 8px 20px;
            padding-left: 12px;
        }

        .payment-instructions li {
            margin-bottom: 4px;
        }

        .payment-instructions br {
            line-height: 1.6;
        }

        .payment-instructions a {
            color: #1a1a1a;
            text-decoration: underline;
        }

        .invoice-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e6e6e6;
            color: #888;
            text-align: center;
            font-size: 11px;
            line-height: 1.6;
        }

        .tax-info {
            margin-top: 15px;
            padding-top: 12px;
            border-top: 1px dashed #e6e6e6;
        }

        @media print {
            body {
                padding: 15px;
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
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <h1 class="logo-title">
                    {{ $eventSettings->getInvoiceLabel() ?? __('Invoice') }}
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
                        <span class="info-label">
                            {{ __('Invoice Number') }}
                        </span>

                        <span class="info-value">
                            #{{ $invoice->getInvoiceNumber() }}
                        </span>
                    </td>

                    <td>
                        <span class="info-label">
                            {{ __('Date Issued') }}
                        </span>

                        <span class="info-value">
                            {{ $issueDate }}
                        </span>
                    </td>

                    <td>
                        <span class="info-label">
                            {{ __('Due Date') }}
                        </span>

                        <span class="info-value">
                            {{ (!$isPaid && !$isVoid && $dueDate) ? $dueDate : '—' }}
                        </span>
                    </td>

                    <td>
                        <span class="info-label">
                            {{ $isPaid ? __('Amount Paid') : __('Amount Due') }}
                        </span>

                        <span class="info-value">
                            {{ Currency::format($order->getTotalGross(), $currency) }}
                        </span>
                    </td>

                    <td>
                        <span class="info-label">
                            {{ __('Status') }}
                        </span>

                        <span class="status-badge {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="billing-section">
        <div class="billing-title">
            {{ __('Billed To') }}
        </div>

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
                <th class="col-description">
                    {{ __('Description') }}
                </th>

                <th class="col-rate align-right">
                    {{ __('Rate') }}
                </th>

                <th class="col-qty align-right">
                    {{ __('Qty') }}
                </th>

                <th class="col-amount align-right">
                    {{ __('Amount') }}
                </th>
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
                                {{ Currency::format(
                                    $orderItem['price_before_discount'],
                                    $currency
                                ) }}
                            </div>

                            <div>
                                {{ Currency::format(
                                    $orderItem['price'],
                                    $currency
                                ) }}
                            </div>
                        @else
                            {{ Currency::format(
                                $orderItem['price'],
                                $currency
                            ) }}
                        @endif
                    </td>

                    <td class="align-right">
                        {{ $orderItem['quantity'] }}
                    </td>

                    <td class="align-right">
                        @if($orderItem['price_before_discount'])
                            <div class="item-price-original">
                                {{ Currency::format(
                                    $orderItem['price_before_discount'] * $orderItem['quantity'],
                                    $currency
                                ) }}
                            </div>

                            <div>
                                {{ Currency::format(
                                    $orderItem['total_before_additions'],
                                    $currency
                                ) }}
                            </div>
                        @else
                            {{ Currency::format(
                                $orderItem['total_before_additions'],
                                $currency
                            ) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr class="subtotal">
            <td>{{ __('Subtotal') }}</td>
            <td>
                {{ Currency::format(
                    $order->getTotalBeforeAdditions(),
                    $currency
                ) }}
            </td>
        </tr>

        @if($totalDiscount > 0)
            <tr class="breakdown">
                <td>{{ __('Total Discount') }}</td>
                <td>
                    -{{ Currency::format($totalDiscount, $currency) }}
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
                        {{ Currency::format($tax['value'], $currency) }}
                    </td>
                </tr>
            @endforeach

            <tr class="subtotal">
                <td>{{ __('Total Tax') }}</td>
                <td>
                    {{ Currency::format($order->getTotalTax(), $currency) }}
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
                        {{ Currency::format($fee['value'], $currency) }}
                    </td>
                </tr>
            @endforeach

            <tr class="subtotal">
                <td>{{ __('Total Service Fee') }}</td>
                <td>
                    {{ Currency::format($order->getTotalFee(), $currency) }}
                </td>
            </tr>
        @endif

        <tr class="total-line">
            <td>{{ __('Total') }}</td>
            <td>
                {{ Currency::format(
                    $order->getTotalGross(),
                    $currency
                ) }}
            </td>
        </tr>

        @if($isPaid)
            <tr class="amount-paid-line">
                <td>{{ __('Amount Paid') }}</td>
                <td>
                    -{{ Currency::format(
                        $order->getTotalGross(),
                        $currency
                    ) }}
                </td>
            </tr>

            <tr class="balance-due-line">
                <td>{{ __('Balance Due') }}</td>
                <td>
                    {{ Currency::format(0, $currency) }}
                </td>
            </tr>
        @endif
    </table>

    @if(!$isPaid && !$isVoid && $invoice->getDueDate())
        <div class="invoice-notes">
            <p>
                {{ __('Für die Bestätigung Deiner Reservierung erwarten wir eine Überweisung bis zum') }}
                <strong>{{ $dueDate }}</strong>
                {{ __('unter Angabe der Rechnungs-Nr.') }}
                <strong>{{ $invoice->getInvoiceNumber() }}</strong>
                {{ __('an:') }}
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

    <div class="invoice-footer">
        @if($eventSettings->getSupportEmail())
            <p>
                {{ __('For any queries, please contact us at') }}
                {{ $eventSettings->getSupportEmail() }}
            </p>
        @endif

        @if((bool) $eventSettings->getInvoiceTaxDetails())
            <div class="tax-info">
                <p>
                    <strong>{{ __('Tax Information') }}:</strong>
                    {!! $eventSettings->getInvoiceTaxDetails() !!}
                </p>
            </div>
        @endif
    </div>
</body>
</html>