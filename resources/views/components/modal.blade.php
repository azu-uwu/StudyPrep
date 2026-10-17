{{--
    StudyPrep - Modal (Dialog xac nhan) component.
    Tai tao theo spec docs/modal/code.html:
      - Overlay: rgba(0,0,0,0.40) + blur(2px), card: radius 22px, padding 28px,
        shadow 0 20px 45px -10px rgba(0,0,0,0.12) + ring 1px rgba(0,0,0,0.03).
      - Title 21px/800, body 15px/500, actions goc phai.
      - CTA tai su dung x-button (primary/neutral/danger), bo 12px (rounded-xl) theo spec.
    Quy uoc dat ten: tien to "sp-" (sp-modal, sp-modal__card, ...).
    Mo: dat data-sp-modal-open="<id>" tren bat ky phan tu nao. Dong: data-sp-modal-close /
    Esc / click nen mo (neu dismissible=true). JS vanilla, @once giong cac component khac.
--}}

@props([
    'title' => '',
    'id' => null,
    'open' => false,
    // Variant A: 440px (2 nut hanh dong). Variant B: 520px (3 nut quyet dinh).
    'maxWidth' => '440px',
    'dismissible' => true,
])

@php
    $modalId = $id ?: 'sp-modal-' . uniqid();

    // maxWidth chap nhan so nguyen (px) hoac chuoi CSS tuy chinh.
    if (is_numeric($maxWidth)) {
        $maxWidth = $maxWidth . 'px';
    } elseif (preg_match('/^\d+$/', (string) $maxWidth)) {
        $maxWidth = $maxWidth . 'px';
    }

    $open = (bool) $open;
    $dismissible = (bool) $dismissible;
    $hasTitle = trim((string) $title) !== '';
@endphp

@once
    @if (! file_exists(public_path('build/manifest.json')) && ! file_exists(public_path('hot')))
        {{-- Chua build asset nen font trong vite.config.js chua duoc self-host: nap tam Nunito tu Bunny Fonts --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:500,700,800">
    @endif

    <style>
        .sp-modal,
        .sp-modal * {
            box-sizing: border-box;
        }

        /* ===== Nen mo (overlay) ===== */
        .sp-modal {
            position: fixed;
            inset: 0;
            z-index: 2000; /* tren sidebar (150) va header (90) */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            overflow-y: auto;
            font-family: 'Nunito', ui-sans-serif, system-ui, 'Segoe UI', sans-serif;
        }

        /* hiddenAttribute co trong HTML nhung display:flex cua .sp-modal se vuot qua -> can rule rieng */
        .sp-modal[hidden] {
            display: none;
        }

        .sp-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.40);
            -webkit-backdrop-filter: blur(2px);
            backdrop-filter: blur(2px);
        }

        /* ===== The noi dung (card) ===== */
        .sp-modal__card {
            position: relative;
            width: 100%;
            margin: auto;
            padding: 28px;
            background: #FFFFFF;
            border: 1px solid #F1F5F9;
            border-radius: 22px;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.03);
            text-align: left;
        }

        .sp-modal__card:focus {
            outline: none;
        }

        .sp-modal__title {
            margin: 0 0 8px;
            font-size: 21px;
            font-weight: 800;
            line-height: 1.35;
            color: #0F172A;
        }

        .sp-modal__body {
            margin: 0 0 28px;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.625;
            color: #334155;
        }

        .sp-modal__body>:first-child {
            margin-top: 0;
        }

        .sp-modal__body>:last-child {
            margin-bottom: 0;
        }

        .sp-modal__actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 4px;
        }

        /* Spec: moi CTA trong popup bo 12px (rounded-xl), khong sua button.blade.php */
        .sp-modal .sp-btn {
            border-radius: 12px;
        }

        /* ===== Hieu ung mo (200ms) ===== */
        @keyframes sp-modal-fade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes sp-modal-pop {
            from {
                opacity: 0;
                transform: translateY(8px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .sp-modal:not([hidden]) .sp-modal__backdrop {
            animation: sp-modal-fade 0.18s ease-out;
        }

        .sp-modal:not([hidden]) .sp-modal__card {
            animation: sp-modal-pop 0.2s ease-out;
        }

        @media (prefers-reduced-motion: reduce) {

            .sp-modal:not([hidden]) .sp-modal__backdrop,
            .sp-modal:not([hidden]) .sp-modal__card {
                animation: none;
            }
        }
    </style>

    <script>
        (function () {
            function spModalResolve(closeEl) {
                // Blade co the render data-sp-modal-close="data-sp-modal-close" (attribute khong gia tri)
                // -> chi dung explicit id khi getElementById tim thay, nguoc lai lay modal cha gan nhat.
                var explicit = closeEl.getAttribute('data-sp-modal-close');
                var byId = explicit ? document.getElementById(explicit) : null;
                return byId || closeEl.closest('.sp-modal');
            }

            function spModalOpen(modal, trigger) {
                if (!modal) return;
                modal.hidden = false;
                if (trigger) modal._spOpener = trigger;
                document.body.style.overflow = 'hidden';
                // Focus nut hanh dong dau tien (thong thuong la hanh dong an toan nhat)
                var target = modal.querySelector('.sp-modal__actions button, .sp-modal__actions a')
                    || modal.querySelector('.sp-modal__card');
                if (target) target.focus();
            }

            function spModalClose(modal) {
                if (!modal) return;
                modal.hidden = true;
                // Chi mo khoa cuon trang khi khong con modal nao dang mo
                if (!document.querySelector('.sp-modal:not([hidden])')) {
                    document.body.style.overflow = '';
                }
                var opener = modal._spOpener;
                modal._spOpener = null;
                if (opener && document.contains(opener)) opener.focus();
            }

            document.addEventListener('click', function (e) {
                var t = e.target;
                if (!t || !t.closest) return;

                var openerEl = t.closest('[data-sp-modal-open]');
                if (openerEl) {
                    spModalOpen(document.getElementById(openerEl.getAttribute('data-sp-modal-open')), openerEl);
                    return;
                }

                var closerEl = t.closest('[data-sp-modal-close]');
                if (closerEl) {
                    spModalClose(spModalResolve(closerEl));
                    return;
                }

                var backdropEl = t.closest('.sp-modal__backdrop');
                if (backdropEl) {
                    var owner = backdropEl.closest('.sp-modal');
                    if (owner && owner.getAttribute('data-dismissible') !== 'false') {
                        spModalClose(owner);
                    }
                }
            });

            // Esc dong modal moi nhat (neu dismissible)
            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape' && e.key !== 'Esc') return;
                var openModals = document.querySelectorAll('.sp-modal:not([hidden])');
                if (!openModals.length) return;
                var top = openModals[openModals.length - 1];
                if (top.getAttribute('data-dismissible') === 'false') return;
                spModalClose(top);
            });

            // Modal duoc render voi trang thai open=true ngay tu dau
            function spModalInit() {
                if (document.querySelector('.sp-modal:not([hidden])')) {
                    document.body.style.overflow = 'hidden';
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', spModalInit);
            } else {
                spModalInit();
            }
        })();
    </script>
@endonce

<div
    {{ $attributes->class(['sp-modal']) }}
    id="{{ $modalId }}"
    role="dialog"
    aria-modal="true"
    @if ($hasTitle) aria-labelledby="{{ $modalId }}-title" @endif
    data-dismissible="{{ $dismissible ? 'true' : 'false' }}"
    @unless ($open) hidden @endunless
>
    <div class="sp-modal__backdrop" aria-hidden="true"></div>

    <div class="sp-modal__card" style="max-width: {{ $maxWidth }};" tabindex="-1" role="document">
        @if ($hasTitle)
            <h3 class="sp-modal__title" id="{{ $modalId }}-title">{{ $title }}</h3>
        @endif

        <div class="sp-modal__body">{{ $slot }}</div>

        @isset($actions)
            <div class="sp-modal__actions">{{ $actions }}</div>
        @endisset
    </div>
</div>
