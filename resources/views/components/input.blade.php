
@props([
    'label' => '',
    'name',
    'type' => 'text',
    'placeholder' => '',
])

<div style="margin-bottom: 20px; width: 100%;">
    @if ($label)
        <label
            for="{{ $name }}"
            style="display: block; margin-bottom: 8px;
                   font-weight: 600; color: #374151;"
        >
            {{ $label }}
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        placeholder="{{ $placeholder }}"
        style="box-sizing: border-box; width: 100%;
               padding: 12px; border: 1px solid #d1d5db;
               border-radius: 8px; font-size: 14px;"
    >
</div>
