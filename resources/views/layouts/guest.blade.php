<!DOCTYPE html>
<html lang="en" class="{{ auth()->check() && auth()->user()->theme === 'dark' ? 'dark' : '' }} h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#10141D">
    <title>@yield('title', 'Steven Hydrotech Exponent | Hydrotech Predictive Inventory & POS System')</title>
    <meta name="description" content="@yield('meta_description', 'Steven Hydrotech Exponent — Official Predictive Inventory Management, EOQ & Demand Forecasting, Supply Chain & Point of Sale (POS) System for Water Treatment and Supply Services.')">
    <meta name="keywords" content="Steven Hydrotech Exponent, Hydrotech Inventory System, Hydrotech Predictive Inventory System, Hydrotech POS, Water Treatment and Supply Services, hydrotechinventorysystem">
    <meta name="application-name" content="Steven Hydrotech Exponent">
    <meta name="author" content="Steven Hydrotech Exponent">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <meta name="google-site-verification" content="{{ env('GOOGLE_SITE_VERIFICATION', 'STCqArYDgGnJblLVqwLMQ8I92LsqyFtezAE9SQjUOiI') }}" />
    <link rel="canonical" href="@yield('canonical', url('/'))">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    @if(env('BING_SITE_VERIFICATION'))
        <meta name="msvalidate.01" content="{{ env('BING_SITE_VERIFICATION') }}">
    @endif

    <!-- OpenGraph / Social Discovery -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Steven Hydrotech Exponent">
    <meta property="og:title" content="@yield('title', 'Steven Hydrotech Exponent | Hydrotech Predictive Inventory & POS System')">
    <meta property="og:description" content="@yield('meta_description', 'Official Predictive Inventory Management, Demand Forecasting, EOQ & Point of Sale System for Steven Hydrotech Exponent — Water Treatment and Supply Services.')">
    <meta property="og:url" content="@yield('canonical', url('/'))">
    <meta property="og:image" content="{{ asset('images/logo.svg') }}">

    <!-- Structured Data (JSON-LD) for Search Engine Indexing -->
    @if(trim($__env->yieldContent('robots', 'index, follow')) === 'index, follow')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => 'Steven Hydrotech Exponent — Predictive Inventory & POS System',
        'alternateName' => ['Hydrotech Inventory System', 'Hydrotech Predictive Inventory System', 'Steven Hydrotech Exponent'],
        'url' => url('/'),
        'applicationCategory' => 'BusinessApplication',
        'operatingSystem' => 'Web',
        'description' => 'Predictive Inventory Management, Demand Forecasting, Economic Order Quantity (EOQ), Supply Chain & Point of Sale System for Steven Hydrotech Exponent Water Treatment and Supply Services.',
        'provider' => [
            '@type' => 'Organization',
            'name' => 'Steven Hydrotech Exponent',
            'url' => url('/'),
            'logo' => asset('images/logo.svg'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full m-0 p-0 bg-white dark:bg-[#10141D] antialiased overflow-x-hidden"
      style="background-image: url('{{ asset('images/bg_design.svg') }}'); background-size: cover; background-position: center;">
    @yield('content')
    @include('partials.global-input-guards')
    @stack('scripts')
</body>
</html>