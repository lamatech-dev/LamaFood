<!doctype html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#071015"><meta name="robots" content="noindex,nofollow"><link rel="manifest" href="/admin.webmanifest"><link rel="icon" href="/icons/icon-192.png" type="image/png"><link rel="apple-touch-icon" href="/icons/apple-touch-icon.png"><title>بازیابی رمز عبور · Denardi Admin</title>@vite('resources/js/admin.js')</head>
<body class="admin-app login-screen">
<main class="login-card">
    <a class="admin-brand" href="/"><span>D</span><b>DENARDI</b></a>
    <p class="admin-kicker">ACCOUNT RECOVERY</p>
    <h1>بازیابی رمز عبور</h1>
    <p>ایمیل حساب مدیریت را وارد کنید. پاسخ سیستم وجود یا عدم وجود حساب را افشا نمی‌کند.</p>
    <form data-forgot-password-form>
        <label>ایمیل<input name="email" type="email" autocomplete="email" required></label>
        <p class="form-error" data-form-error hidden></p>
        <p data-form-success hidden></p>
        <button class="admin-button" type="submit">ارسال لینک بازیابی</button>
        <a href="{{ route('admin.login') }}">بازگشت به ورود</a>
    </form>
</main>
</body>
</html>
