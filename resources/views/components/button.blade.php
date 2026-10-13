@props([
    'variant' => 'primary',
    'type' => 'button',
    'disabled' => false,
])

@php
    // Chỉ chấp nhận các biến thể đã định nghĩa, biến thể lạ sẽ rơi về primary
    $variant = in_array($variant, ['primary', 'secondary', 'danger'], true) ? $variant : 'primary';
@endphp

@once
    @if (! file_exists(public_path('build/manifest.json')) && ! file_exists(public_path('hot')))
        {{-- Chưa build asset nên font trong vite.config.js chưa được self-host: nạp tạm Nunito từ Bunny Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800">
    @endif

    <style>
        .sp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 32px;
            border: none;
            border-radius: 14px;
            font-family: 'Nunito', 'Instrument Sans', ui-sans-serif, system-ui, 'Segoe UI', sans-serif;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
            color: #ffffff;
            cursor: pointer;
            user-select: none;
            text-decoration: none;
            margin-right: 8px;
            /* Viền đậm phía dưới tạo cảm giác nổi khối 3D giống hình mẫu */
            box-shadow: inset 0 -5px 0 rgba(0, 0, 0, 0.22), 0 6px 14px rgba(13, 110, 253, 0.28);
            transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
        }

        .sp-btn:hover {
            filter: brightness(1.06);
        }

        .sp-btn:active {
            transform: translateY(3px);
            box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.22), 0 3px 8px rgba(13, 110, 253, 0.22);
        }

        .sp-btn:focus-visible {
            outline: 3px solid rgba(13, 110, 253, 0.45);
            outline-offset: 2px;
        }

        .sp-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            filter: none;
            transform: none;
        }

        .sp-btn--primary {
            background: linear-gradient(180deg, #1e7bff 0%, #0b63e6 100%);
        }

        .sp-btn--secondary {
            background: linear-gradient(180deg, #ffffff 0%, #e5e7eb 100%);
            color: #1f2937;
            box-shadow: inset 0 -5px 0 rgba(0, 0, 0, 0.10), 0 6px 14px rgba(31, 41, 55, 0.12);
        }

        .sp-btn--danger {
            background: linear-gradient(180deg, #f05252 0%, #dc2626 100%);
            box-shadow: inset 0 -5px 0 rgba(0, 0, 0, 0.22), 0 6px 14px rgba(220, 38, 38, 0.28);
        }
    </style>
@endonce

<button
    type="{{ $type }}"
    @disabled($disabled)
    {{ $attributes->class(['sp-btn', "sp-btn--{$variant}"]) }}
>
    {{ $slot }}
</button>
