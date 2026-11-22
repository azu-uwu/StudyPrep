<x-layouts.app active="" title="StudyPrep - Hồ sơ học viên">

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/ho-so.css') }}">
@endpush

{{-- Tiêu đề trang đồng bộ chuẩn với Luyện tập --}}
<div class="hs-page-header">
    <h1 class="hs-page-title">Hồ sơ của bạn</h1>
    <p class="hs-page-desc">Quản lý thông tin cá nhân và theo dõi hành trình học tập của bạn.</p>
</div>

{{-- 1. Card thông tin tài khoản --}}
<section class="hs-card hs-profile-summary">
    <div class="hs-profile-main">
        <div class="hs-avatar-wrap">

        <div class="hs-avatar" id="avatarInitials">
            @if($user->avatar_url)
                <img
                    src="{{ $user->avatar_url }}"
                    alt="Ảnh đại diện"
                    style="
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        border-radius: 50%;
                    "
                >
            @else
                <span id="avatarLetters">
                    {{ $user->initials }}
                </span>
            @endif
        </div>

        <input
            type="file"
            id="avatarFile"
            accept="image/jpeg,image/png,image/webp"
            hidden
        >
            <span class="hs-avatar-edit" title="Ảnh đại diện">✎</span>
        </div>
        <div class="hs-profile-identity">
            <div class="hs-name-line">
                <h2 id="summaryName">{{ $user->full_name  }}</h2>
                <span class="hs-status-pill">Đã xác thực</span>
            </div>
            <p class="hs-email-line" id="summaryEmail">{{ $user->email }}</p>
            <!-- <div class="hs-profile-meta">
                <span class="hs-course-pill">KHOÁ HỌC TOEIC 750+</span>
                <span class="hs-dot">•</span>
                <span>Tham gia từ tháng 8, 2025</span>
            </div> -->
        </div>
    </div>
    <x-button variant="neutral" size="medium" id="changeAvatar" type="button">
        <span>▧</span> &nbsp; Đổi ảnh đại diện
    </x-button>
    <input id="avatarFile" type="file" accept="image/jpeg,image/png,image/webp" hidden>
</section>

{{-- 2. Thông tin cá nhân: 2 cột đồng đều trên desktop, 1 cột trên điện thoại --}}
<section class="hs-card personal-card">
    <div class="hs-section-heading">
        <div>
            <h3 class="hs-section-title">Thông tin cá nhân</h3>
            <p class="hs-section-sub">Cập nhật thông tin để StudyPrep hiểu bạn hơn.</p>
        </div>
    </div>

    <form id="profileForm" action="{{ route('profile.update') }}" method="POST">
        @csrf
        <div class="hs-form-grid">
            <div class="hs-field">
                <label class="hs-label" for="fullName">Họ và tên</label>
                <div class="hs-control">
                    <input class="hs-input" type="text" id="fullName" name="fullName"
                           value="{{ old('fullName', $user->full_name) }}"
                           maxlength="100" autocomplete="name" required>
                </div>
            </div>

            <div class="hs-field">
                <label class="hs-label" for="birthday">Ngày sinh</label>
                <div class="hs-control">
                    <input class="hs-input" type="date" id="birthday" name="birthday"
                           value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="hs-field">
                <label class="hs-label" for="email">Địa chỉ email</label>
                <div class="hs-control">
                    <input class="hs-input" type="email" id="email" name="email"
                           value="{{ old('email', $user->email) }}"
                           maxlength="255" autocomplete="email" required>
                </div>
            </div>

            <div class="hs-field">
                <label class="hs-label" for="phone">Số điện thoại</label>
                <div class="hs-control">
                    <input class="hs-input" type="tel" id="phone" name="phone"
                           value="{{ old('phone', $user->phone) }}"
                           maxlength="25" autocomplete="tel" placeholder="Nhập số điện thoại">
                </div>
            </div>

            <div class="hs-field">
                <label class="hs-label" for="gender">Giới tính</label>
                <div class="hs-control">
                    <select class="hs-select" id="gender" name="gender">
                        <option value="">Chọn giới tính</option>
                        @foreach (['Nữ', 'Nam', 'Khác', 'Không muốn tiết lộ'] as $gender)
                            <option value="{{ $gender }}" @selected(old('gender', $user->gender) === $gender)>
                                {{ $gender }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="hs-field">
                <label class="hs-label" for="city">Tỉnh / Thành phố</label>
                <div class="hs-control">
                    <select class="hs-select" id="city" name="city">
                        <option value="">Chọn tỉnh / thành phố</option>
                        @foreach (['Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Cần Thơ', 'Khác'] as $city)
                            <option value="{{ $city }}" @selected(old('city', $user->city) === $city)>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="hs-form-footer">
            <span class="hs-muted-note" id="saveMessage">Dùng thông tin này để tối ưu trải nghiệm của bạn.</span>
            <x-button variant="primary" size="medium" type="submit">
                ✓ &nbsp; Lưu thay đổi
            </x-button>
        </div>
    </form>
</section>


{{-- 3. Mục tiêu của bạn --}}
<section class="hs-card goal-card">
    <div class="hs-inline-heading">
        <h3 class="hs-section-title">Mục tiêu của bạn</h3>

        <button class="hs-tiny-icon-btn"
                id="editGoal"
                type="button"
                aria-label="Chỉnh sửa mục tiêu">
            ⚙
        </button>
    </div>

    <div class="hs-goal-grid">

        {{-- Mục tiêu Listening & Reading --}}
        <div class="hs-goal-metric">
            <span class="hs-goal-type">
                Listening &amp; Reading (L&amp;R)
            </span>

            <strong class="hs-goal-value" id="goalScore">
                {{ $goalLRScore !== null
                    ? $goalLRScore . '+'
                    : '—' }}
            </strong>

            <span class="hs-goal-note">
                {{ $goalLRScore !== null
                    ? 'Điểm mục tiêu L&R'
                    : 'Chưa thiết lập mục tiêu L&R' }}
            </span>
        </div>

        {{-- Mục tiêu Speaking & Writing --}}
        <div class="hs-goal-metric">
            <span class="hs-goal-type">
                Speaking &amp; Writing (S&amp;W)
            </span>

            <strong class="hs-goal-value" id="goalSWScore">
                {{ $goalSWScore !== null
                    ? $goalSWScore . '+'
                    : '—' }}
            </strong>

            <span class="hs-goal-note">
                {{ $goalSWScore !== null
                    ? 'Điểm mục tiêu S&W'
                    : 'Chưa thiết lập mục tiêu S&W' }}
            </span>
        </div>
    </div>

    <div class="hs-goal-divider"></div>

    <a href="{{ url('/ho-so/muc-tieu-thiet-lap') }}"
       class="hs-text-link"
       id="editGoalText">
        ✎ &nbsp; Điều chỉnh mục tiêu
    </a>
</section>


{{-- 4. Card Tiến độ học tập: số liệu thật truyền từ Controller; chưa có thì hiện dấu — --}}
@php
    $examAttemptsCount = $examAttemptsCount ?? null;
    $learnedVocabularyCount = $learnedVocabularyCount ?? null;
    $lrPercent = (isset($lrProgressPercent) && is_numeric($lrProgressPercent))
        ? max(0, min(100, (int) round($lrProgressPercent))) : null;
    $swPercent = (isset($swProgressPercent) && is_numeric($swProgressPercent))
        ? max(0, min(100, (int) round($swProgressPercent))) : null;
@endphp
<section class="hs-card progress-card">
    <div class="hs-inline-heading">
        <h3 class="hs-section-title">Tiến độ học tập</h3>
        <!-- <span class="hs-mini-icon" aria-hidden="true">▥</span> -->
    </div>

    <div class="hs-overview-grid">
        <div class="hs-stat-tile">
            <span class="hs-stat-badge" aria-hidden="true">▤</span>
            <div class="hs-stat-content">
                <strong class="hs-stat-count">{{ $examAttemptsCount ?? '—' }}</strong>
                <span class="hs-stat-title">Số lần làm đề thi</span>
            </div>
        </div>
        <div class="hs-stat-tile">
            <span class="hs-stat-badge" aria-hidden="true">▧</span>
            <div class="hs-stat-content">
                <strong class="hs-stat-count">{{ $learnedVocabularyCount ?? '—' }}</strong>
                <span class="hs-stat-title">Từ vựng đã học</span>
            </div>
        </div>
    </div>

    <h4 class="hs-group-heading">Tiến độ theo kỹ năng</h4>
    <div class="hs-track-grid">
        <div class="hs-track-tile">
            <div class="hs-track-header">
                <span class="hs-track-label">Listening &amp; Reading</span>
                <strong class="hs-track-value">{{ $lrPercent === null ? '—' : $lrPercent.'%' }}</strong>
            </div>
            <div class="hs-track-rail" role="progressbar" aria-label="Tiến độ Listening và Reading"
                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $lrPercent ?? 0 }}">
                <div class="hs-track-fill" style="width: {{ $lrPercent ?? 0 }}%;"></div>
            </div>
            <span class="hs-track-note">{{ $lrPercent === null ? 'Chưa có dữ liệu tiến độ' : 'Tiến độ theo mục tiêu L&R' }}</span>
        </div>
        <div class="hs-track-tile">
            <div class="hs-track-header">
                <span class="hs-track-label">Speaking &amp; Writing</span>
                <strong class="hs-track-value">{{ $swPercent === null ? '—' : $swPercent.'%' }}</strong>
            </div>
            <div class="hs-track-rail" role="progressbar" aria-label="Tiến độ Speaking và Writing"
                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $swPercent ?? 0 }}">
                <div class="hs-track-fill" style="width: {{ $swPercent ?? 0 }}%;"></div>
            </div>
            <span class="hs-track-note">{{ $swPercent === null ? 'Chưa có dữ liệu tiến độ' : 'Tiến độ theo mục tiêu S&W' }}</span>
        </div>
    </div>

    <div class="hs-progress-bottom">
        <a class="hs-text-link" id="detailsBtn" href="{{ url('/phan-tich/chi-tiet') }}">
            Xem phân tích chi tiết <span aria-hidden="true">→</span>
        </a>
    </div>
</section>

{{-- 5. Cài đặt tài khoản: hai mục cùng kiểu và cùng chiều cao --}}
<section class="hs-card settings-card">
    <h3 class="hs-section-title">Cài đặt tài khoản</h3>

    <button class="hs-setting-tile" id="passwordBtn" type="button"
            aria-haspopup="dialog" aria-controls="passwordModal">
        <span class="hs-setting-badge" aria-hidden="true">♙</span>
        <span class="hs-setting-details">
            <strong class="hs-setting-title">Mật khẩu</strong>
            <small class="hs-setting-subtitle" id="passwordUpdatedAt">
                @if($user->password_changed_at)
                    Đổi lần cuối: {{ $user->password_changed_at->format('d/m/Y') }}
                @else
                    Chưa có lịch sử đổi mật khẩu
                @endif
            </small>
        </span>
        <span class="hs-setting-link">Thay đổi ›</span>
    </button>

</section>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

{{-- Modal Đổi mật khẩu --}}
<div class="password-modal" id="passwordModal" aria-hidden="true">
    <div class="popup" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <button class="close" type="button" id="closePopup" aria-label="Đóng popup">×</button>
        <section>
            <h2 class="popup-title" id="popupTitle">Đổi mật khẩu</h2>
            <p class="description">Vui lòng nhập mật khẩu hiện tại và mật khẩu mới.</p>
            <form id="changeForm" novalidate>
                <div class="fields">
                    <div class="field-wrap">
                        <input type="password" name="oldPassword" placeholder="Mật khẩu hiện tại" autocomplete="current-password" required>
                        <button class="toggle" type="button" aria-label="Hiện mật khẩu">◉</button>
                    </div>
                    <div class="field-wrap">
                        <input type="password" name="password" placeholder="Mật khẩu mới" autocomplete="new-password" required>
                        <button class="toggle" type="button" aria-label="Hiện mật khẩu">◉</button>
                    </div>
                    <div class="field-wrap">
                        <input type="password" name="confirm" placeholder="Nhập lại mật khẩu mới" autocomplete="new-password" required>
                        <button class="toggle" type="button" aria-label="Hiện mật khẩu">◉</button>
                    </div>
                </div>
                <x-button variant="primary" size="medium" type="submit" style="width: 100%; margin-top: 20px;">
                    ĐỔI MẬT KHẨU
                </x-button>
                <p class="notice" id="changeNotice" aria-live="polite"></p>
            </form>
        </section>
    </div>
</div>

@push('scripts')

<script>
(() => {
    const $ = id => document.getElementById(id);
    const csrf = @json(csrf_token());

    const routes = {
        profile: @json(route('profile.update')),
        password: @json(route('profile.password')),
        avatar: @json(route('profile.avatar')),
        goal: @json(url('/ho-so/muc-tieu-thiet-lap'))
    };

    // Gửi dữ liệu đến Laravel
    async function send(url, payload) {
        const formData = payload instanceof FormData;

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf
        };

        if (!formData) {
            headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers,
            body: formData
                ? payload
                : JSON.stringify(payload)
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (response.status === 419) {
                throw new Error(
                    'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang.'
                );
            }

            if (response.status === 429) {
                throw new Error(
                    'Bạn đã thử quá nhiều lần. Vui lòng thử lại sau 1 phút.'
                );
            }

            const message = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : (data.message || 'Có lỗi xảy ra.');

            throw new Error(message);
        }

        return data;
    }

    let toastTimer;

    function toast(message) {
        const el = $('toast');
        if (!el) return;

        el.textContent = message;
        el.classList.add('show');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            el.classList.remove('show');
        }, 3000);
    }

    // Cập nhật thông tin cá nhân
    $('profileForm')?.addEventListener('submit', async e => {
        e.preventDefault();

        const form = e.currentTarget;
        const button = form.querySelector('[type="submit"]');

        if (button) button.disabled = true;

        try {
            const result = await send(
                routes.profile,
                new FormData(form)
            );

            $('summaryName').textContent = result.data.name;
            $('summaryEmail').textContent = result.data.email;

            if ($('emailVerifyBadge')) {
                $('emailVerifyBadge').textContent =
                    result.data.email_verified
                    ? 'Đã xác thực'
                    : 'Chưa xác thực';
            }

            if ($('avatarLetters')) {
                const parts = result.data.name.trim().split(/\s+/);
                $('avatarLetters').textContent = parts
                    .slice(-2)
                    .map(p => Array.from(p)[0])
                    .join('')
                    .toLocaleUpperCase('vi-VN');
            }

            $('saveMessage').textContent =
                'Thông tin đã được lưu vào cơ sở dữ liệu.';

            toast(result.message);
        } catch (error) {
            toast(error.message);
        } finally {
            if (button) button.disabled = false;
        }
    });

    // Đổi ảnh đại diện
    $('changeAvatar')?.addEventListener('click', () => {
        $('avatarFile')?.click();
    });

    $('avatarFile')?.addEventListener('change', async e => {
        const input = e.currentTarget;
        const file = input.files?.[0];
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp']
            .includes(file.type) || file.size > 2 * 1024 * 1024) {
            toast('Chỉ chấp nhận JPG/PNG/WebP tối đa 2 MB.');
            input.value = '';
            return;
        }

        const data = new FormData();
        data.append('avatar', file);

        try {
            const result = await send(routes.avatar, data);

            const image = document.createElement('img');
            image.src = result.avatar_url;
            image.alt = 'Ảnh đại diện';
            image.style.cssText =
                'width:100%;height:100%;object-fit:cover;';

            $('avatarInitials').replaceChildren(image);
            toast(result.message);
        } catch (error) {
            toast(error.message);
        } finally {
            input.value = '';
        }
    });

    // Chỉnh sửa mục tiêu
    $('editGoal')?.addEventListener('click', () => {
        window.location.href = routes.goal;
    });

    // Modal đổi mật khẩu
    const modal = $('passwordModal');
    const form = $('changeForm');
    const notice = $('changeNotice');

    function closeModal() {
        modal?.classList.remove('show');
        modal?.setAttribute('aria-hidden', 'true');
        form?.reset();
        if (notice) notice.textContent = '';
    }

    $('passwordBtn')?.addEventListener('click', () => {
        modal?.classList.add('show');
        modal?.setAttribute('aria-hidden', 'false');
        if (notice) notice.textContent = '';
        form?.querySelector('input[name="oldPassword"]')?.focus();
    });

    $('closePopup')?.addEventListener('click', closeModal);

    modal?.addEventListener('click', e => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal();
    });

    modal?.querySelectorAll('.toggle').forEach(button => {
        button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('input');
            const showing = input.type === 'password';
            input.type = showing ? 'text' : 'password';
            button.setAttribute(
                'aria-label',
                showing ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'
            );
        });
    });

    // Gửi yêu cầu đổi mật khẩu
    form?.addEventListener('submit', async e => {
        e.preventDefault();

        const oldPassword = form.elements.oldPassword.value;
        const password = form.elements.password.value;
        const password_confirmation = form.elements.confirm.value;

        // Xóa thông báo cũ
        notice.textContent = '';
        notice.style.color = '#d64c5e';

        // 1. Kiểm tra dữ liệu trống
        if (!oldPassword || !password || !password_confirmation) {
            notice.textContent = 'Vui lòng nhập đầy đủ thông tin.';
            return;
        }

        // 2. Kiểm tra độ dài
        if (password.length < 8) {
            notice.textContent =
                'Mật khẩu mới phải có ít nhất 8 ký tự.';
            return;
        }

        // 3. Kiểm tra mật khẩu mới khác mật khẩu cũ
        if (password === oldPassword) {
            notice.textContent =
                'Mật khẩu mới phải khác mật khẩu hiện tại.';
            return;
        }

        // 4. Kiểm tra xác nhận mật khẩu
        if (password !== password_confirmation) {
            notice.textContent =
                'Mật khẩu xác nhận không khớp.';
            return;
        }

        const button = form.querySelector('[type="submit"]');

        if (button) {
            button.disabled = true;
        }

        try {
            // 5. Gửi dữ liệu đến Laravel
            const result = await send(routes.password, {
                oldPassword,
                password,
                password_confirmation
            });

            // 6. Hiển thị kết quả thành công
            notice.style.color = '#10a879';
            notice.textContent = result.message;

            // Xóa mật khẩu đã nhập
            form.reset();

            // Cập nhật thời gian đổi mật khẩu
            if ($('passwordUpdatedAt') && result.changed_at) {
                $('passwordUpdatedAt').textContent =
                    'Đổi lần cuối: ' + result.changed_at;
            }

            toast(result.message);

        } catch (error) {
            // 7. Hiển thị lỗi từ backend
            notice.style.color = '#d64c5e';
            notice.textContent = error.message;

        } finally {
            if (button) {
                button.disabled = false;
            }
        }
    });


})();
</script>

@endpush

</x-layouts.app>
