@props([
    'variant' => 'primary',
    'size' => 'medium',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
])

@php
    // Tương thích ngược: variant "secondary" cũ chính là nút trắng, nay đổi tên thành "neutral".
    if ($variant === 'secondary') {
        $variant = 'neutral';
    }

    // Spec từ ảnh Figma: 5 biến thể đã xác định rõ, tím Secondary Outline để dành sau.
    $variant = in_array($variant, ['primary', 'neutral', 'success', 'warning', 'danger'], true)
        ? $variant
        : 'primary';

    // 3 kích thước: small 36px / medium 44px (mặc định) / large 56px.
    $size = in_array($size, ['small', 'medium', 'large'], true) ? $size : 'medium';

    // Khi có href thì render thẻ <a> để điều hướng, ngược lại render <button>.
    $isLink = ! is_null($href) && $href !== false && $href !== '';
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
            border: 0;
            font-family: 'Nunito', 'Instrument Sans', ui-sans-serif, system-ui, 'Segoe UI', sans-serif;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
            text-decoration: none;
            transition: transform 0.1s ease, box-shadow 0.1s ease, filter 0.1s ease;
        }

        /* ===== Sizes theo spec: cao cố định + radius ===== */
        .sp-btn--small {
            height: 36px;
            padding: 0 16px;
            font-size: 14px;
            border-radius: 10px;
        }

        .sp-btn--medium {
            height: 44px;
            padding: 0 24px;
            font-size: 16px;
            border-radius: 14px;
        }

        .sp-btn--large {
            height: 56px;
            padding: 0 32px;
            font-size: 20px;
            border-radius: 14px;
        }

        /* ===== Variants: Nền + Đáy 3D dày 4.5px ===== */
        .sp-btn--primary {
            background-color: #0076FE;
            color: #FFFFFF;
            box-shadow: 0 4.5px 0 #0053BF;
        }

        .sp-btn--neutral {
            background-color: #FFFFFF;
            color: #374151;
            box-shadow: 0 4.5px 0 #CBD5E1;
        }

        .sp-btn--success {
            background-color: #00B87A;
            color: #FFFFFF;
            box-shadow: 0 4.5px 0 #008A5B;
        }

        .sp-btn--warning {
            background-color: #FF8A00;
            color: #FFFFFF;
            box-shadow: 0 4.5px 0 #D97706;
        }

        .sp-btn--danger {
            background-color: #FF3B30;
            color: #FFFFFF;
            box-shadow: 0 4.5px 0 #CC2B22;
        }

        .sp-btn:hover {
            filter: brightness(1.06);
        }

        /* Hiệu ứng nhấn Tactile: lún 3.5px + xẹp đáy còn ~1px */
        .sp-btn:active:not(:disabled):not([aria-disabled='true']) {
            transform: translateY(3.5px);
            filter: brightness(0.99);
        }

        .sp-btn--primary:active:not(:disabled):not([aria-disabled='true']) {
            box-shadow: 0 1px 0 #0053BF;
        }

        .sp-btn--neutral:active:not(:disabled):not([aria-disabled='true']) {
            box-shadow: 0 1px 0 #CBD5E1;
        }

        .sp-btn--success:active:not(:disabled):not([aria-disabled='true']) {
            box-shadow: 0 1px 0 #008A5B;
        }

        .sp-btn--warning:active:not(:disabled):not([aria-disabled='true']) {
            box-shadow: 0 1px 0 #D97706;
        }

        .sp-btn--danger:active:not(:disabled):not([aria-disabled='true']) {
            box-shadow: 0 1px 0 #CC2B22;
        }

        /* Bàn phím: Tab tới là thấy vòng focus rõ ràng */
        .sp-btn:focus-visible {
            outline: 3px solid rgba(0, 118, 254, 0.45);
            outline-offset: 2px;
        }

        .sp-btn:disabled,
        .sp-btn[aria-disabled='true'] {
            opacity: 0.55;
            cursor: not-allowed;
            filter: none;
            transform: none;
        }
    </style>
@endonce

@if ($isLink)
    <a
        href="{{ $disabled ? null : $href }}"
        @if ($disabled) aria-disabled="true" @endif
        {!! $disabled ? 'tabindex="-1" aria-hidden="false"' : '' !!}
        {{ $attributes->class(['sp-btn', "sp-btn--{$variant}", "sp-btn--{$size}"]) }}
    >{{ $slot }}</a>
@else
    <button
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->class(['sp-btn', "sp-btn--{$variant}", "sp-btn--{$size}"]) }}
    >{{ $slot }}</button>
@endif

