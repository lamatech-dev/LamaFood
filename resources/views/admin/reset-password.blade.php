<!doctype html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#071015"><meta name="robots" content="noindex,nofollow"><link rel="manifest" href="/admin.webmanifest"><link rel="icon" href="/icons/icon-192.png" type="image/png"><link rel="apple-touch-icon" href="/icons/apple-touch-icon.png"><title>رمز عبور جدید · Denardi Admin</title>@vite('resources/js/admin.js')</head>
<body class="admin-app login-screen">
<main class="login-card">
    <a class="admin-brand" href="/"><span>D</span><b>DENARDI</b></a>
    <p class="admin-kicker">SECURE PASSWORD RESET</p>
    <h1>رمز عبور جدید</h1>
    <p>رمز باید حداقل ۱۲ نویسه و شامل حروف بزرگ و کوچک، عدد و نماد باشد.</p>
    <form data-reset-password-form>
        <input name="token" type="hidden" value="{{ $token }}">
        <label>ایمیل<input name="email" type="email" autocomplete="email" value="{{ request('email') }}" required></label>
        <label>رمز عبور جدید<input name="password" type="password" autocomplete="new-password" required></label>
        <label>تکرار رمز عبور<input name="password_confirmation" type="password" autocomplete="new-password" required></label>
        <p class="form-error" data-form-error hidden></p>
        <p data-form-success hidden></p>
        <button class="admin-button" type="submit">ذخیره رمز جدید</button>
        <a href="{{ route('admin.login') }}">بازگشت به ورود</a>
    </form>
</main>
</body>
</html>
