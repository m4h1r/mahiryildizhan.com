@extends('emails.layouts.base')

@section('title', 'Aboneliğinizi Onaylayın')

@section('content')
    <p style="margin:0 0 8px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.12em;color:#6e6e73;">
        Abonelik Onayı &middot; Subscription Confirmation
    </p>

    <h1 style="margin:0 0 20px;font-size:22px;font-weight:600;color:#1d1d1f;line-height:1.3;">
        Onay bağlantınız hazır.
    </h1>

    <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#4b5563;">
        <strong>{{ $subscriber->email }}</strong> adresiyle
        <strong>{{ config('app.name') }}</strong> bültenine abone olmak için aşağıdaki butona tıklayın.
        Bağlantı <strong>24 saat</strong> geçerlidir.
    </p>

    <p style="margin:0 0 28px;font-size:14px;line-height:1.7;color:#4b5563;">
        Click the button below to confirm your subscription to <strong>{{ config('app.name') }}</strong>.
        This link is valid for <strong>24 hours</strong>.
    </p>

    {{-- CTA Button --}}
    <table cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
        <tr>
            <td style="border-radius:10px;background-color:#1d1d1f;">
                <a href="{{ $confirmationUrl }}"
                   style="display:inline-block;padding:14px 32px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;letter-spacing:0.02em;">
                    Aboneliği Onayla &rarr;
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px;font-size:12px;color:#9ca3af;line-height:1.6;">
        Butona tıklayamıyor musunuz? Aşağıdaki bağlantıyı tarayıcınıza kopyalayın:<br>
        Can't click the button? Copy the link below into your browser:
    </p>
    <p style="margin:0;font-size:11px;color:#6e6e73;word-break:break-all;">
        <a href="{{ $confirmationUrl }}" style="color:#0071e3;">{{ $confirmationUrl }}</a>
    </p>
@endsection

@section('footer-note')
    Bu e-postayı siz istemediyseniz güvenle yok sayabilirsiniz.<br>
    If you didn't request this, you can safely ignore this email.
@endsection
