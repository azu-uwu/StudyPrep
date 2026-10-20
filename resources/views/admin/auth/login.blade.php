<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập Admin | StudyPrep</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:700,800|instrument-sans:400,500,600">
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-screen bg-admin-canvas font-sans text-slate-600">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-3xl border border-violet-100 bg-white p-8 shadow-sm">
            <p class="font-nunito text-2xl font-extrabold">
                <span class="text-brand-navy">Study</span><span class="text-brand-prep">PREP</span>
            </p>
            <h1 class="mt-4 font-nunito text-xl font-extrabold text-slate-900">Đăng nhập cổng quản trị</h1>
            <p class="mt-1 text-sm text-slate-500">Dành cho Admin vận hành ngân hàng đề TOEIC.</p>

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-violet-100 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-admin-accent focus:bg-white focus:ring-2 focus:ring-admin-accent/20">
                    @error('email')
                        <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Mật khẩu</label>
                    <input id="password" name="password" type="password" required
                        class="w-full rounded-xl border border-violet-100 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-admin-accent focus:bg-white focus:ring-2 focus:ring-admin-accent/20">
                </div>
                <button type="submit" class="w-full rounded-xl bg-admin-accent px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-600">
                    Đăng nhập
                </button>
            </form>
        </div>
    </div>
</body>
</html>
