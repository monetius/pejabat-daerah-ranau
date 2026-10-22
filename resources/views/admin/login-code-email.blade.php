<!doctype html>
<html lang="{{ app()->getLocale() }}">
<body style="margin:0;padding:32px 16px;background:#f3f5f8;color:#142c48;font-family:Arial,sans-serif">
    <div style="max-width:480px;margin:auto;padding:32px;background:#fff;border:1px solid #dbe2eb;border-radius:16px">
        <p style="font-weight:bold">CMS Ranau</p>
        <h1 style="font-size:24px">{{ __('Kod log masuk anda') }}</h1>
        <p>{{ __('Gunakan kod ini untuk melengkapkan log masuk CMS Ranau.') }}</p>
        <p style="font-size:36px;font-weight:bold;letter-spacing:8px;padding:20px;background:#f3f5f8;border-radius:8px">{{ $code }}</p>
        <p>{{ __('Kod sah selama 3 minit dan hanya boleh digunakan sekali.') }}</p>
        <p style="font-size:14px;color:#52647a">{{ __('Jangan kongsi kod ini. Jika anda tidak meminta kod ini, abaikan e-mel ini dan tukar kata laluan akaun anda.') }}</p>
    </div>
</body>
</html>
