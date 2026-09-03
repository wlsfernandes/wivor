<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wivor')</title>

    @hasSection('meta-description')
        <meta name="description" content="@yield('meta-description')">
    @endif
    @hasSection('meta-keywords')
        <meta name="keywords" content="@yield('meta-keywords')">
    @endif
    <meta name="robots" content="@yield('robots', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1')">
    @hasSection('author')
        <meta name="author" content="@yield('author')">
    @endif
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif
    @hasSection('og-image')
        <meta property="og:type" content="@yield('og-type', 'website')">
        <meta property="og:site_name" content="WivorPhotos">
        <meta property="og:title" content="@yield('title')">
        <meta property="og:description" content="@yield('meta-description')">
        <meta property="og:url" content="@yield('canonical')">
        <meta property="og:image" content="@yield('og-image')">
        @if (str_starts_with(trim($__env->yieldContent('og-image')), 'https://'))
            <meta property="og:image:secure_url" content="@yield('og-image')">
        @endif
        @hasSection('og-image-type')
            <meta property="og:image:type" content="@yield('og-image-type')">
        @endif
        @hasSection('og-image-width')
            <meta property="og:image:width" content="@yield('og-image-width')">
        @endif
        @hasSection('og-image-height')
            <meta property="og:image:height" content="@yield('og-image-height')">
        @endif
        <meta property="og:image:alt" content="@yield('og-image-alt', trim($__env->yieldContent('title')))">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="@yield('title')">
        <meta name="twitter:description" content="@yield('meta-description')">
        <meta name="twitter:image" content="@yield('og-image')">
        <meta name="twitter:image:alt" content="@yield('og-image-alt', trim($__env->yieldContent('title')))">
    @endif

    @yield('structured-data')

    @include('partials.head-assets')
</head>

<body>
    @include('partials.header')
    <div>

        @yield('content')

    </div>
    @include('partials.footer')
    @yield('scripts')
</body>

</html>
