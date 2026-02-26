<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - File Manager</title>

    {{-- Finder CSS --}}
    <link rel="stylesheet" href="{{ asset('vendor/finder/css/finder.css') }}">

    @livewireStyles
</head>
<body class="finder-app">

    @livewire('finder::file-manager')

    @livewireScripts
    <script src="{{ asset('vendor/finder/js/finder.js') }}"></script>
</body>
</html>
