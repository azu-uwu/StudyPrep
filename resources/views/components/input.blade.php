
@props([
    'label' => '',
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'size' => 'md',
    'state' => 'default',
    'disabled' => false,
    'helper' => '',
    'hint' => null,
    'error' => '',
    'success' => '',
    'prefix' => null,
    'suffix' => null,
    'clearable' => null,
    'toggle' => null,
    'rows' => 2,
    'maxlength' => null,
    'showCount' => null,
])

@php
    $size = in_array($size, ['sm', 'md', 'lg'], true) ? $size : 'md';
    $typeLower = strtolower((string) $type);
    $isTextarea = $typeLower === 'textarea';
    $isPassword = $typeLower === 'password';
    $isSearch = $typeLower === 'search';
    $showToggle = $toggle ?? $isPassword;
    $showClear = $clearable ?? $isSearch;
    if ($hint !== null && $helper === '') { $helper = $hint; }
    $hasError = $error !== '' && $error !== null;
    $hasSuccess = ! $hasError && $success !== '' && $success !== null;
    if ($hasError) { $state = 'error'; }
    elseif ($hasSuccess) { $state = 'success'; }
    $state = in_array($state, ['default', 'filled', 'success', 'error'], true) ? $state : 'default';
    $isDisabled = (bool) $disabled;
    if ($isDisabled) { $state = 'default'; }
    $fieldId = $id ?: ($name ? preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $name) : 'sp-input-' . uniqid());
    $resolvedValue = $name ? old($name, $value) : $value;
    $resolvedStr = (string) $resolvedValue;
    $isFilled = $state === 'filled' || trim($resolvedStr) !== '';
    $showCount = $showCount ?? ($isTextarea && $maxlength !== null);
    $caption = $hasError ? $error : ($hasSuccess ? $success : $helper);
    $inputType = $isPassword ? 'password' : ($isSearch ? 'search' : $typeLower);
    $controlClass = trim('sp-control sp-control--' . $size
        . ($isTextarea ? ' sp-control--textarea' : '')
        . ($isFilled && ! $isDisabled ? ' is-filled' : '')
        . ($hasError && ! $isDisabled ? ' has-error' : '')
        . ($hasSuccess && ! $isDisabled ? ' has-success' : '')
        . ($isDisabled ? ' is-disabled' : ''));
    $labelClass = 'sp-label'
        . ($hasError && ! $isDisabled ? ' sp-label--error' : '')
        . ($hasSuccess && ! $isDisabled ? ' sp-label--success' : '')
        . ($isDisabled ? ' sp-label--disabled' : '');
@endphp
@once
    @if (! file_exists(public_path('build/manifest.json')) && ! file_exists(public_path('hot')))
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,500,600,700,800">
    @endif
    <style>
        .sp-field { display: flex; flex-direction: column; gap: 6px; width: 100%; margin-bottom: 16px; font-family: 'Nunito', ui-sans-serif, system-ui, sans-serif; }
        .sp-label { font-size: 12px; font-weight: 700; line-height: 16px; color: #111C2D; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .sp-label--error { color: #BA1A1A; }
        .sp-label--success { color: #006A44; }
        .sp-label--disabled { color: #727786; }
        .sp-label svg { width: 16px; height: 16px; flex: none; }
        .sp-control { display: flex; align-items: stretch; width: 100%; box-sizing: border-box; background: #FFFFFF; box-shadow: 0 0 0 1.5px #E2E8F0; overflow: hidden; transition: box-shadow .15s ease, background-color .15s ease; }
        .sp-control--sm { min-height: 36px; border-radius: 10px; }
        .sp-control--md { min-height: 44px; border-radius: 12px; }
        .sp-control--lg { min-height: 54px; border-radius: 14px; }
        .sp-control--textarea { border-radius: 14px; }
        .sp-control:hover { box-shadow: 0 0 0 1.5px #CBD5E1; }
        .sp-control:focus-within { box-shadow: 0 0 0 2px #0057BF, 0 0 0 5px rgba(0,118,254,.15); background: #FFFFFF; }
        .sp-control.is-filled { box-shadow: 0 0 0 1.5px #CBD5E1; }
        .sp-control.is-filled:focus-within { box-shadow: 0 0 0 2px #0057BF, 0 0 0 5px rgba(0,118,254,.15); }
        .sp-control.has-error { box-shadow: 0 0 0 2px #BA1A1A, 0 0 0 5px rgba(255,59,48,.15); }
        .sp-control.has-success { box-shadow: 0 0 0 2px #008557, 0 0 0 5px rgba(0,184,122,.15); }
        .sp-control.is-disabled { background: #CFDAF2; box-shadow: 0 0 0 1.5px #E2E8F0; cursor: not-allowed; }
        .sp-input { flex: 1 1 auto; min-width: 0; width: 100%; border: 0; outline: none; background: transparent; box-sizing: border-box; color: #111C2D; font-family: inherit; }
        .sp-control--sm .sp-input { min-height: 36px; padding: 0 12px; font-size: 12px; font-weight: 600; line-height: 16px; }
        .sp-control--md .sp-input { min-height: 44px; padding: 0 16px; font-size: 14px; font-weight: 500; line-height: 22px; }
        .sp-control--lg .sp-input { min-height: 54px; padding: 0 20px; font-size: 16px; font-weight: 500; line-height: 24px; }
        .sp-control.is-filled .sp-input { font-weight: 700; }
        .sp-input::placeholder { color: #727786; opacity: 1; }
        .sp-control.is-disabled .sp-input { color: #727786; cursor: not-allowed; }
        textarea.sp-input { padding: 12px 14px; resize: vertical; min-height: 76px; }
        .sp-addon { display: flex; align-items: center; padding: 0 12px; background: #CFDAF2; color: #414755; font-size: 12px; font-weight: 700; line-height: 16px; white-space: nowrap; user-select: none; }
        .sp-icon { display: flex; align-items: center; justify-content: center; flex: none; color: #727786; width: 38px; }
        .sp-control--sm .sp-icon { width: 32px; }
        .sp-icon svg { width: 20px; height: 20px; }
        .sp-icon--error { color: #BA1A1A; }
        .sp-icon--success { color: #008557; }
        .sp-iconbtn { align-self: center; flex: none; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; margin-right: 6px; padding: 0; border: 0; border-radius: 8px; background: transparent; color: #414755; cursor: pointer; }
        .sp-iconbtn:hover { background: #F0F3FF; }
        .sp-iconbtn:focus-visible { outline: 2px solid #0057BF; outline-offset: 1px; }
        .sp-iconbtn svg { width: 20px; height: 20px; }
        .sp-clear { width: 24px; height: 24px; border-radius: 9999px; background: #CFDAF2; }
        .sp-meta { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; }
        .sp-caption { font-size: 11px; font-weight: 600; line-height: 14px; color: #414755; }
        .sp-caption--error { color: #BA1A1A; font-weight: 700; }
        .sp-caption--success { color: #006A44; font-weight: 700; }
        .sp-count { font-size: 11px; font-weight: 700; line-height: 14px; color: #727786; white-space: nowrap; }
    </style>
@endonce
<div class="sp-field">
    @if ($label)
        <label class="{{ $labelClass }}" for="{{ $fieldId }}">
            <span>{{ $label }}</span>
            @if ($isDisabled)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            @endif
        </label>
    @endif
    <div class="{{ $controlClass }}">
        @if ($prefix !== null && $prefix !== '')
            <span class="sp-addon">{{ $prefix }}</span>
        @endif
        @if ($isSearch)
            <span class="sp-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></span>
        @endif
        @if ($isTextarea)
            <textarea id="{{ $fieldId }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
                @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                @disabled($isDisabled) @if ($hasError) aria-invalid="true" @endif
                aria-describedby="{{ $fieldId }}-desc" class="sp-input" data-sp-field
                @if ($showCount) data-sp-count @endif
                {{ $attributes }}>{{ $resolvedStr }}</textarea>
        @else
            <input id="{{ $fieldId }}" name="{{ $name }}" type="{{ $inputType }}" value="{{ $resolvedStr }}"
                placeholder="{{ $placeholder }}"
                @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                @disabled($isDisabled) @if ($hasError) aria-invalid="true" @endif
                aria-describedby="{{ $fieldId }}-desc" class="sp-input" data-sp-field
                {{ $attributes }}>
        @endif
        @if ($hasError && ! $isDisabled)
            <span class="sp-icon sp-icon--error" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg></span>
        @elseif ($hasSuccess && ! $isDisabled)
            <span class="sp-icon sp-icon--success" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5.5"/></svg></span>
        @endif
        @if ($showClear && ! $isDisabled && ! $isTextarea)
            <button type="button" class="sp-iconbtn sp-clear" data-sp-clear="{{ $fieldId }}" aria-label="Xoa noi dung" style="display: {{ trim($resolvedStr) !== '' ? 'inline-flex' : 'none' }};"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
        @endif
        @if ($showToggle && ! $isDisabled && ! $isTextarea)
            <button type="button" class="sp-iconbtn" data-sp-toggle="{{ $fieldId }}" aria-label="Hien/an mat khau" aria-pressed="false"><svg data-eye-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg><svg data-eye-off hidden viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.1A9.8 9.8 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 3.9M6.6 6.6A16.6 16.6 0 0 0 2 12s3.5 7 10 7c1.5 0 2.9-.3 4.1-.9"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>
        @endif
        @if ($suffix !== null && $suffix !== '')
            <span class="sp-addon">{{ $suffix }}</span>
        @endif
    </div>
    @if ($caption !== '' || $showCount)
        <div class="sp-meta">
            <span id="{{ $fieldId }}-desc" class="sp-caption{{ $hasError ? ' sp-caption--error' : ($hasSuccess ? ' sp-caption--success' : '') }}">{{ $hasError ? '! ' : ($hasSuccess ? 'OK ' : '') }}{{ $caption }}</span>
            @if ($showCount)
                <span class="sp-count" id="{{ $fieldId }}-count">{{ mb_strlen($resolvedStr) }} / {{ $maxlength }} ky tu</span>
            @endif
        </div>
    @else
        <span id="{{ $fieldId }}-desc" class="sp-caption" hidden></span>
    @endif
</div>
@once
<script>
(function () {
    function spUpdateCount(field) {
        var id = field.getAttribute('id');
        if (!id) return;
        var counter = document.getElementById(id + '-count');
        if (!counter) return;
        counter.textContent = (field.value || '').length + (field.getAttribute('maxlength') ? ' / ' + field.getAttribute('maxlength') : '') + ' ky tu';
    }
    function spUpdateClear(field) {
        var id = field.getAttribute('id');
        if (!id) return;
        var btn = document.querySelector('[data-sp-clear="' + id + '"]');
        if (!btn) return;
        btn.style.display = (field.value || '') !== '' ? 'inline-flex' : 'none';
    }
    document.addEventListener('click', function (e) {
        var t = e.target.closest ? e.target.closest('[data-sp-toggle]') : null;
        if (t) {
            var input = document.getElementById(t.getAttribute('data-sp-toggle'));
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            t.setAttribute('aria-pressed', show ? 'true' : 'false');
            var open = t.querySelector('[data-eye-open]');
            var off = t.querySelector('[data-eye-off]');
            if (open && off) { open.hidden = show; off.hidden = !show; }
            input.focus();
            return;
        }
        var c = e.target.closest ? e.target.closest('[data-sp-clear]') : null;
        if (c) {
            var target = document.getElementById(c.getAttribute('data-sp-clear'));
            if (!target) return;
            target.value = '';
            target.dispatchEvent(new Event('input', { bubbles: true }));
            spUpdateCount(target);
            spUpdateClear(target);
            target.focus();
        }
    });
    document.addEventListener('input', function (e) {
        var f = e.target.closest ? e.target.closest('[data-sp-field]') : null;
        if (!f) return;
        var wrap = f.closest('.sp-control');
        if (wrap) wrap.classList.toggle('is-filled', (f.value || '') !== '');
        spUpdateCount(f);
        spUpdateClear(f);
    });
    window.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-sp-field]').forEach(function (f) {
            spUpdateCount(f);
            spUpdateClear(f);
        });
    });
})();
</script>
@endonce
