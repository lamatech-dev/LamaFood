<!doctype html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>ورود · Denardi Admin</title>@vite('resources/js/admin.js')</head>
<body class="admin-app login-screen">
<main class="login-card">
    <a class="admin-brand" href="/fa"><span>D</span><b>DENARDI</b></a>
    <p class="admin-kicker">LAMATECH · CONTROLLED ACCESS</p>
    <h1>ورود به مدیریت</h1>
    <p>محتوا، منو و وضعیت شعبه را از یک پنل مدیریت کنید.</p>
    <form data-login-form>
        <label>نام کاربری یا ایمیل<input name="identifier" autocomplete="username" required></label>
        <label>رمز عبور<input name="password" type="password" autocomplete="current-password" required></label>
        <p class="form-error" data-form-error hidden></p>
        <button class="admin-button" type="submit">ورود امن</button>
    </form>
</main>
</body>
</html>
