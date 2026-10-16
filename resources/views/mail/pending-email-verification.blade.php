@extends('emails.layouts.base')

@section('title', 'E-posta Değişikliğini Onaylayın')

@section('content')
    <p style="margin:0 0 8px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.12em;color:#6e6e73;">
        E-posta Değişikliği &middot; E-mail Change
    </p>

    <h1 style="margin:0 0 20px;font-size:22px;font-weight:600;color:#1d1d1f;line-height:1.3;">
        Yeni e-posta adresinizi doğrulayın.
    </h1>

    <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#4b5563;">
        Hesabınızın e-posta adresini bu adres olarak değiştirmek istediniz.
        Onaylamak için aşağıdaki butona tıklayın. Bağlantı <strong>60 dakika</strong> geçerlidir.
        Bu isteği siz yapmadıysanız bu e-postayı yok sayın; adresiniz değişmez.
    </p>

    <p style="margin:0 0 28px;font-size:14px;line-height:1.7;color:#4b5563;">
        You requested to change your account e-mail to this address. Click below to confirm.
        This link is valid for <strong>60 minutes</strong>. If you did not request this, ignore
        this e-mail — nothing changes.
    </p>

    <table cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
        <tr>
            <td style="border-radius:10px;background-color:#1d1d1f;">
                <a href="{{ $verificationUrl }}"
                   style="display:inline-block;padding:14px 32px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;letter-spacing:0.02em;">
                    Doğrula / Confirm
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:12px;line-height:1.6;color:#9ca3af;word-break:break-all;">
        {{ $verificationUrl }}
    </p>
@endsection
