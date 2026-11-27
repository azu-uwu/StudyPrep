<x-skill-practice-layout skill="doc" title="StudyPrep - Luyện theo Kỹ năng Đọc">

    {{-- 1. Card Ôn tập (6 câu sai) --}}
    <div class="sp-review-banner">
        <div class="sp-review-left">
            <div class="sp-review-icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
            </div>
            <div class="sp-review-content">
                <span class="sp-review-tag">ÔN TẬP</span>
                <h3 class="sp-review-title">6 câu sai</h3>
                <p class="sp-review-desc">Bạn có muốn ôn lại câu sai bây giờ không?</p>
            </div>
        </div>
        <x-button variant="warning" size="small" href="{{ url('/cai-thien') }}">
            <span>Ôn lại</span>
            <span aria-hidden="true">&rarr;</span>
        </x-button>
    </div>

    {{-- 2. Tiêu đề danh sách: "Tất cả phần luyện" --}}
    <div class="sp-section-heading-wrap">
        <span class="sp-section-heading-bars" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="sp-section-icon">
                <rect x="4" y="5" width="2.8" height="14" rx="1.4" fill="#111C2D"/>
                <rect x="10.5" y="5" width="2.8" height="14" rx="1.4" fill="#111C2D"/>
                <rect x="17" y="7" width="2.8" height="10" rx="1.4" fill="#111C2D"/>
            </svg>
        </span>
        <h2 class="sp-section-heading-title">Tất cả phần luyện</h2>
    </div>

    @php
        $inProgressPart5 = !empty($inProgressAttempts[5]);
        $inProgressPart6 = !empty($inProgressAttempts[6]);
        $inProgressPart7 = !empty($inProgressAttempts[7]);
    @endphp

    {{-- 3. Danh sách các phần luyện TOEIC Reading --}}
    <div class="sp-parts-list">

        {{-- Part 5: Hoàn thành câu --}}
        <div class="sp-part-item-card">
            <div class="sp-part-item-left">
                <div class="sp-part-item-icon">
                    <svg width="58" height="58" viewBox="0 0 64 64" fill="none">
                        <rect x="10" y="8" width="34" height="46" rx="6" fill="#FFFFFF" stroke="#111C2D" stroke-width="2.5"/>
                        <rect x="15" y="14" width="11" height="11" rx="3" fill="#38BDF8" stroke="#0284C7" stroke-width="1.5"/>
                        <path d="M18 19.5l2 2 4-4" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="15" y="28" width="11" height="11" rx="3" fill="#38BDF8" stroke="#0284C7" stroke-width="1.5"/>
                        <path d="M18 33.5l2 2 4-4" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="15" y="42" width="11" height="11" rx="3" fill="#FFFFFF" stroke="#94A3B8" stroke-width="1.5"/>
                        <g transform="translate(42, 28) rotate(42)">
                            <rect x="-24" y="-5" width="28" height="10" rx="1" fill="#F59E0B" stroke="#111C2D" stroke-width="2"/>
                            <line x1="-24" y1="0" x2="4" y2="0" stroke="#D97706" stroke-width="1.5"/>
                            <path d="M-24 -5 L-32 0 L-24 5 Z" fill="#FDE68A" stroke="#111C2D" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M-32 0 L-28 -2.5 L-28 2.5 Z" fill="#111C2D"/>
                            <rect x="4" y="-5" width="6" height="10" rx="1" fill="#EF4444" stroke="#111C2D" stroke-width="2"/>
                            <rect x="2" y="-5" width="3" height="10" fill="#94A3B8" stroke="#111C2D" stroke-width="1.5"/>
                        </g>
                    </svg>
                </div>
                <div class="sp-part-item-info">
                    <h3 class="sp-part-item-title" @if($inProgressPart5) onclick="window.location.href='{{ route('luyen-tap.doc.tiep-tuc', ['part' => 5]) }}'" @else onclick="openInpageSetup(5)" @endif>Part 5: Hoàn thành câu</h3>
                    <p class="sp-part-item-sub">{{ $inProgressPart5 ? 'Đang làm dở' : 'Chưa bắt đầu' }}</p>
                </div>
            </div>
            <div class="sp-part-item-actions">
                @if($inProgressPart5)
                    <x-button variant="primary" size="small" href="{{ route('luyen-tap.doc.tiep-tuc', ['part' => 5]) }}">
                        <span>Tiếp tục</span>
                        <span aria-hidden="true">&rarr;</span>
                    </x-button>
                @else
                    <x-button variant="success" size="small" onclick="openInpageSetup(5)">
                        <span>Bắt đầu</span>
                        <span aria-hidden="true">&rarr;</span>
                    </x-button>
                @endif
            </div>
        </div>

        {{-- Part 6: Hoàn thành đoạn văn --}}
        <div class="sp-part-item-card">
            <div class="sp-part-item-left">
                <div class="sp-part-item-icon">
                    <svg width="58" height="58" viewBox="0 0 64 64" fill="none">
                        <rect x="12" y="10" width="28" height="38" rx="4" fill="#EEF2FF" stroke="#4F46E5" stroke-width="2.2"/>
                        <line x1="18" y1="18" x2="32" y2="18" stroke="#818CF8" stroke-width="2" stroke-linecap="round"/>
                        <line x1="18" y1="24" x2="28" y2="24" stroke="#818CF8" stroke-width="2" stroke-linecap="round"/>
                        <rect x="20" y="16" width="30" height="40" rx="5" fill="#FFFFFF" stroke="#4F46E5" stroke-width="2.2"/>
                        <line x1="26" y1="24" x2="44" y2="24" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"/>
                        <line x1="26" y1="30" x2="38" y2="30" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"/>
                        <line x1="26" y1="36" x2="42" y2="36" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"/>
                        <path d="M26 42h18a3 3 0 0 1 3 3v4a3 3 0 0 1-3 3h-12l-4 3v-3h-2a3 3 0 0 1-3-3v-4a3 3 0 0 1 3-3z" fill="#818CF8" stroke="#4F46E5" stroke-width="2"/>
                        <circle cx="35" cy="46" r="3" fill="#FFFFFF"/>
                        <line x1="37" y1="48" x2="41" y2="52" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="sp-part-item-info">
                    <h3 class="sp-part-item-title" @if($inProgressPart6) onclick="window.location.href='{{ route('luyen-tap.doc.tiep-tuc', ['part' => 6]) }}'" @else onclick="openInpageSetup(6)" @endif>Part 6: Hoàn thành đoạn văn</h3>
                    <p class="sp-part-item-sub">{{ $inProgressPart6 ? 'Đang làm dở' : 'Chưa bắt đầu' }}</p>
                </div>
            </div>
            <div class="sp-part-item-actions">
                @if($inProgressPart6)
                    <x-button variant="primary" size="small" href="{{ route('luyen-tap.doc.tiep-tuc', ['part' => 6]) }}">
                        <span>Tiếp tục</span>
                        <span aria-hidden="true">&rarr;</span>
                    </x-button>
                @else
                    <x-button variant="success" size="small" onclick="openInpageSetup(6)">
                        <span>Bắt đầu</span>
                        <span aria-hidden="true">&rarr;</span>
                    </x-button>
                @endif
            </div>
        </div>

        {{-- Part 7: Đọc hiểu đoạn văn --}}
        <div class="sp-part-item-card">
            <div class="sp-part-item-left">
                <div class="sp-part-item-icon">
                    <svg width="58" height="58" viewBox="0 0 64 64" fill="none">
                        <rect x="18" y="10" width="28" height="42" rx="4" fill="#3B82F6"/>
                        <rect x="18" y="10" width="6" height="42" rx="3" fill="#2563EB"/>
                        <line x1="43" y1="12" x2="43" y2="50" stroke="#60A5FA" stroke-width="1.5"/>
                        <path d="M25 48v9l3.5-2.5 3.5 2.5v-9z" fill="#93C5FD"/>
                    </svg>
                </div>
                <div class="sp-part-item-info">
                    <h3 class="sp-part-item-title" @if($inProgressPart7) onclick="window.location.href='{{ route('luyen-tap.doc.tiep-tuc', ['part' => 7]) }}'" @else onclick="openInpageSetup(7)" @endif>Part 7: Đọc hiểu đoạn văn</h3>
                    <p class="sp-part-item-sub">{{ $inProgressPart7 ? 'Đang làm dở' : 'Chưa bắt đầu' }}</p>
                </div>
            </div>
            <div class="sp-part-item-actions">
                @if($inProgressPart7)
                    <x-button variant="primary" size="small" href="{{ route('luyen-tap.doc.tiep-tuc', ['part' => 7]) }}">
                        <span>Tiếp tục</span>
                        <span aria-hidden="true">&rarr;</span>
                    </x-button>
                @else
                    <x-button variant="success" size="small" onclick="openInpageSetup(7)">
                        <span>Bắt đầu</span>
                        <span aria-hidden="true">&rarr;</span>
                    </x-button>
                @endif
            </div>
        </div>

    </div>

</x-skill-practice-layout>
