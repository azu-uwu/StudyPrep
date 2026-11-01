{{--
    Component Modal yêu cầu đăng nhập dùng chung (StudyPrep).
    Hiển thị thông báo khi người dùng chưa đăng nhập bấm vào các chức năng yêu cầu tài khoản.
    Tái sử dụng component <x-modal> và <x-button> có sẵn.
--}}
<x-modal id="sp-auth-modal" title="Bạn cần đăng nhập" maxWidth="440px">
    <p style="margin: 0; font-size: 15px; color: #475569; line-height: 1.6;">
        Vui lòng đăng nhập để sử dụng tính năng này.
    </p>

    <x-slot:actions>
        <x-button variant="neutral" size="medium" data-sp-modal-close>
            Để sau
        </x-button>
        <x-button variant="primary" size="medium" href="{{ route('login') }}">
            Đăng nhập
        </x-button>
    </x-slot:actions>
</x-modal>

@guest
    <script>
        (function () {
            'use strict';

            function openAuthModal() {
                var modal = document.getElementById('sp-auth-modal');
                if (modal) {
                    modal.hidden = false;
                    document.body.style.overflow = 'hidden';
                    var target = modal.querySelector('.sp-modal__actions .sp-btn, .sp-modal__actions button, .sp-modal__actions a')
                        || modal.querySelector('.sp-modal__card');
                    if (target) {
                        target.focus();
                    }
                }
            }

            window.spOpenAuthModal = openAuthModal;

            // Chặn click ở capture phase đối với mọi phần tử có attribute [data-require-auth]
            document.addEventListener('click', function (e) {
                var target = e.target;
                if (!target || !target.closest) {
                    return;
                }

                var authTrigger = target.closest('[data-require-auth]');
                if (authTrigger) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.stopImmediatePropagation) {
                        e.stopImmediatePropagation();
                    }
                    openAuthModal();
                    return false;
                }
            }, true);
        })();
    </script>
@endguest
