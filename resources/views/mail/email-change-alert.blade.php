@extends('emails.layouts.base')

@section('title', 'E-posta Değişikliği İsteği')

@section('content')
    <p style="margin:0 0 8px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.12em;color:#b45309;">
        Güvenlik Uyarısı &middot; Security Alert
    </p>

    <h1 style="margin:0 0 20px;font-size:22px;font-weight:600;color:#1d1d1f;line-height:1.3;">
        Hesabınızda e-posta değişikliği istendi.
    </h1>

    <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#4b5563;">
        Hesabınızın e-posta adresinin <strong>{{ $newEmail }}</strong> olarak değiştirilmesi istendi.
        Değişiklik, yeni adrese gönderilen bağlantı tıklanana kadar <strong>uygulanmaz</strong> —
        mevcut adresiniz hâlâ geçerli.
    </p>

    <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#4b5563;">
        A change of your account e-mail to <strong>{{ $newEmail }}</strong> was requested. It will
        <strong>not</strong> take effect until the link sent to the new address is confirmed.
    </p>

    <p style="margin:0;font-size:14px;line-height:1.7;color:#991b1b;">
        Bu isteği siz yapmadıysanız hemen şifrenizi değiştirin.
        If this was not you, change your password immediately.
    </p>
@endsection
