<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f7;padding:40px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;">

                    {{-- Logo / Site Name --}}
                    <tr>
                        <td align="center" style="padding-bottom:24px;">
                            <span style="font-size:18px;font-weight:600;color:#1d1d1f;letter-spacing:-0.02em;">{{ config('app.name') }}</span>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background:#ffffff;border-radius:18px;border:1px solid #e5e7eb;padding:40px 36px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding-top:24px;">
                            <p style="margin:0;font-size:11px;color:#9ca3af;line-height:1.6;">
                                @yield('footer-note')
                            </p>
                            <p style="margin:8px 0 0;font-size:11px;color:#c4c4c6;">
                                &copy; {{ now()->year }} {{ config('app.name') }}
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
