<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#134734">
    <title>@yield('title', 'Cấu hình trang khách')</title>
    <link rel="icon" href="{{ asset($website['favicon']) }}">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" crossorigin="anonymous">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin/website-settings.css') }}?v={{ filemtime(public_path('css/admin/website-settings.css')) }}">
</head>
<body>
    @yield('content')
    @stack('js')
</body>
</html>
