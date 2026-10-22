@props([
    'active' => 'luyen-tap',
    'title' => 'StudyPrep - Hệ thống luyện thi TOEIC',
])
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Nunito & Instrument Sans Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800|instrument-sans:400,500,600,700">

    {{-- Core Design System & Responsive Framework --}}
    <link rel="stylesheet" href="{{ asset('css/studyprep-core.css') }}">

    {{-- Sidebar CSS --}}
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    {{-- Page Specific Styles --}}
    @stack('styles')
</head>
<body>
    <div class="sp-app-layout">
        {{-- Sidebar điều hướng chung --}}
        <x-sidebar :active="$active" />

        {{-- Khung nội dung chính bên phải --}}
        <div class="sp-main-container">
            {{-- Header đầu trang --}}
            <x-header />

            {{-- Vùng hiển thị nội dung từng màn hình --}}
            <main class="sp-content-wrapper">
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
