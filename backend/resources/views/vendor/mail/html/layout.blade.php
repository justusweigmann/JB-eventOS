<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ config('app.name') }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">

    <style>
        @media only screen and (max-width: 640px) {
            .inner-body {
                border-radius: 0 !important;
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }

            .content-cell {
                padding: 28px 24px !important;
            }

            h1 {
                font-size: 22px !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                text-align: center;
                width: 100% !important;
            }
        }
    </style>
</head>

<body
    style="
        box-sizing: border-box;
        font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, 'Segoe UI',
            Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-color: #f4f2f7;
        color: #56506a;
        height: 100%;
        line-height: 1.65;
        margin: 0;
        padding: 0;
        width: 100% !important;
    "
>
<table
    class="wrapper"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    role="presentation"
    style="background-color: #f4f2f7; width: 100%;"
>
    <tr>
        <td align="center">
            <table
                class="content"
                width="100%"
                cellpadding="0"
                cellspacing="0"
                role="presentation"
            >
                <tr>
                    <td
                        class="header"
                        style="
                            padding: 32px 0 28px;
                            text-align: center;
                        "
                    >
                        @php
                            $logoUrl = !empty($mailOrganizerLogoUrl ?? null)
                                ? $mailOrganizerLogoUrl
                                : (
                                    config('app.email_logo_url')
                                    ?: rtrim(
                                        (string) config('app.frontend_url'),
                                        '/'
                                    ) . '/logos/hi-events-stacked-light.png'
                                );

                            $logoLinkUrl = !empty($mailOrganizerWebsite ?? null)
                                ? $mailOrganizerWebsite
                                : (
                                    config('app.email_logo_link_url')
                                    ?: config('app.frontend_url')
                                );

                            $logoAltText = !empty($mailOrganizerName ?? null)
                                ? $mailOrganizerName
                                : config('app.name');
                        @endphp

                        <a
                            href="{{ $logoLinkUrl }}"
                            style="
                                color: #191029;
                                font-size: 19px;
                                font-weight: 700;
                                text-decoration: none;
                                display: inline-block;
                            "
                        >
                            <img
                                src="{{ $logoUrl }}"
                                class="logo"
                                alt="{{ $logoAltText }}"
                                style="
                                    display: block;
                                    width: auto;
                                    max-width: 100%;
                                    height: auto;
                                    max-height: 250px;
                                    object-fit: contain;
                                    margin: 0 auto;
                                    border: 0;
                                "
                            >
                        </a>
                    </td>
                </tr>

                <tr>
                    <td
                        class="body"
                        width="100%"
                        cellpadding="0"
                        cellspacing="0"
                        style="border: hidden !important;"
                    >
                        <table
                            class="inner-body"
                            align="center"
                            width="600"
                            cellpadding="0"
                            cellspacing="0"
                            role="presentation"
                            style="
                                background-color: #ffffff;
                                border-radius: 16px;
                                margin: 0 auto;
                                width: 600px;
                            "
                        >
                            <tr>
                                <td class="content-cell">
                                    {!! Illuminate\Mail\Markdown::parse($slot) !!}

                                    {!! $subcopy ?? '' !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {!! $footer ?? '' !!}
            </table>
        </td>
    </tr>
</table>
</body>
</html>