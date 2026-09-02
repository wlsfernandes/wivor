<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wivor')</title>

    <meta name="description" content="@yield('meta-description', 'Default description')">
    <meta name="keywords" content="@yield('meta-keywords', 'default, keywords')">
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif
    @hasSection('og-image')
        <meta property="og:title" content="@yield('title')">
        <meta property="og:description" content="@yield('meta-description')">
        <meta property="og:url" content="@yield('canonical')">
        <meta property="og:image" content="@yield('og-image')">
    @endif

    @include('partials.header')
    @yield('scripts')
</head>

<body>
    <div>

        @yield('content')

    </div>
    @include('partials.footer')
</body>

</html>
