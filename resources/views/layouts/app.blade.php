<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-role="traveler">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? 'Honest trips and reviews for Kenya and Africa' }} | SafariHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700&family=Figtree:wght@400;500;600&display=swap"
        rel="stylesheet">
    <script>
        const theme = localStorage.getItem('theme');
        if (theme) document.documentElement.dataset.theme = theme;
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-canvas font-sans text-base text-ink antialiased">
    {{ $slot }}
</body>

</html>
