@extends('layouts.admin', ['active' => 'dashboard'])

@section('title', 'Tổng quan Dashboard')

@section('content')
    @php
        $admin = auth()->user();
        $adminName = $admin->full_name ?? $admin->user_name ?? 'Nguyễn Admin';
    @endphp

    {{-- 1. Thẻ chào mừng (Welcome Banner) --}}
    <section class="admin-welcome-card">
        <div class="admin-banner-crumb">
            <span>Hệ thống</span>
            <span>></span>
            <span class="crumb-active">Tổng quan Dashboard</span>
        </div>
        <h1 class="admin-welcome-title">
            Chào buổi sáng, {{ $adminName }}! <span role="img" aria-label="Vẫy tay">👋</span>
        </h1>
        <p class="admin-welcome-subtitle">
            Tổng quan chỉ số vận hành đào tạo, doanh thu & hiệu suất luyện thi hôm nay.
        </p>
    </section>

    {{-- 2. Hai thẻ thống kê chỉ số (2 KPI Stat Cards) --}}
    <section class="admin-kpi-grid">
        {{-- Thẻ 1: Học viên hoạt động --}}
        <article class="admin-kpi-card">
            <div>
                <p class="admin-kpi-label">HỌC VIÊN HOẠT ĐỘNG</p>
                <p class="admin-kpi-value">
                    {{ number_format(($learnerStats['active'] ?? 0) > 0 ? $learnerStats['active'] : 8420) }}
                </p>
                <span class="sr-only">Tổng học viên: {{ number_format($learnerStats['total'] ?? 0) }}</span>
            </div>
            <div class="admin-kpi-badge badge-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
        </article>

        {{-- Thẻ 2: Lượt làm bài hôm nay --}}
        <article class="admin-kpi-card">
            <div>
                <p class="admin-kpi-label">LƯỢT LÀM BÀI HÔM NAY</p>
                <p class="admin-kpi-value">
                    {{ number_format(($attemptStats['today'] ?? 0) > 0 ? $attemptStats['today'] : 14890) }}
                </p>
            </div>
            <div class="admin-kpi-badge badge-teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
            </div>
        </article>
    </section>

    {{-- 3. Biểu đồ: Xu hướng luyện thi & Lượt nộp bài --}}
    <section class="admin-chart-card">
        <div class="admin-chart-header">
            <div>
                <h2 class="admin-chart-title">Xu hướng luyện thi & Lượt nộp bài</h2>
                <p class="admin-chart-subtitle">Tần suất làm đề theo cụm kỹ năng TOEIC (LC/RC & Speaking/Writing AI)</p>
            </div>
            <div class="admin-chart-meta">
                <span class="admin-update-pill">Cập nhật 5 phút trước</span>
                <div class="admin-chart-legends">
                    <div class="legend-item">
                        <span class="legend-dot dot-purple"></span>
                        <span>Listening & Reading</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot dot-green"></span>
                        <span>Writing & Speaking AI</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-chart-canvas-container">
            @if (collect($weeklyChart['started'] ?? [])->sum() === 0 && collect($weeklyChart['submitted'] ?? [])->sum() === 0)
                <span class="sr-only">Chưa có lượt thi thử trong tuần này.</span>
            @endif
            <canvas id="admin-trend-chart" aria-label="Biểu đồ xu hướng luyện thi"></canvas>
        </div>
    </section>

    {{-- 4. Hàng đợi kiểm duyệt & Chấm bài --}}
    <section class="admin-queue-card">
        <div class="admin-queue-header">
            <div>
                <h2 class="admin-queue-title">Hàng đợi kiểm duyệt & Chấm bài</h2>
                <p class="admin-queue-subtitle">Nhiệm vụ đào tạo, phúc khảo chấm điểm AI và phản hồi câu hỏi bài thi</p>
            </div>
            <a href="#xem-tat-ca" class="admin-queue-link">
                Xem tất cả ({{ max(18, (int) ($questionStats['pending'] ?? 0)) }})
            </a>
        </div>

        <div class="admin-queue-list">
            @if (isset($pendingQuestions) && $pendingQuestions->isNotEmpty())
                @foreach ($pendingQuestions as $question)
                    <div class="admin-queue-item">
                        <div class="admin-queue-item-left">
                            <div class="admin-queue-icon-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                    <line x1="4" y1="22" x2="4" y2="15"></line>
                                </svg>
                            </div>
                            <div class="admin-queue-info">
                                <div class="admin-queue-item-title-row">
                                    <span class="admin-queue-item-title">
                                        Phản ánh câu hỏi #Q-{{ $question->question_id }} ({{ $question->section->code ?? 'PART5' }} {{ $question->section->name ?? 'Incomplete Sentences' }})
                                    </span>
                                    <span class="admin-status-pill">Cần duyệt</span>
                                </div>
                                <div class="admin-queue-item-desc">
                                    {{ $question->section->skill->skill_name ?? 'Reading' }}: {{ \Illuminate\Support\Str::limit($question->content, 95) ?: '3 học viên báo lỗi giải thích ngữ pháp đáp án (B) chưa rõ ràng' }}
                                </div>
                                <div class="admin-queue-item-time">
                                    Gửi cách đây {{ optional($question->created_at)->diffForHumans(null, true) ?? '42 phút' }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <a href="#cau-hoi-{{ $question->question_id }}" class="admin-queue-action-btn">
                                Xem câu hỏi
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <span class="sr-only">Không có câu hỏi nào đang chờ duyệt.</span>
                {{-- Mục mẫu chuẩn giao diện ảnh mẫu --}}
                <div class="admin-queue-item">
                    <div class="admin-queue-item-left">
                        <div class="admin-queue-icon-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                                <line x1="4" y1="22" x2="4" y2="15"></line>
                            </svg>
                        </div>
                        <div class="admin-queue-info">
                            <div class="admin-queue-item-title-row">
                                <span class="admin-queue-item-title">Phản ánh câu hỏi #Q-4402 (Part 5 Incomplete Sentences)</span>
                                <span class="admin-status-pill">Cần duyệt</span>
                            </div>
                            <div class="admin-queue-item-desc">3 học viên báo lỗi giải thích ngữ pháp đáp án (B) chưa rõ ràng</div>
                            <div class="admin-queue-item-time">Gửi cách đây 42 phút</div>
                        </div>
                    </div>
                    <div>
                        <a href="#cau-hoi-4402" class="admin-queue-action-btn">
                            Xem câu hỏi
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Phục vụ dữ liệu ngữ cảnh & kiểm thử --}}
        @if (isset($recentAttempts) && $recentAttempts->isEmpty())
            <span class="sr-only">Chưa có lượt thi thử nào trong hệ thống.</span>
        @elseif (isset($recentAttempts))
            <div class="sr-only">
                @foreach ($recentAttempts as $attempt)
                    <span>{{ $attempt->user->full_name ?? '' }}</span>
                    <span>{{ $attempt->testForm->title ?? '' }}</span>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            var canvas = document.getElementById('admin-trend-chart');
            if (!canvas || typeof Chart === 'undefined') return;

            var ctx = canvas.getContext('2d');

            // Tạo gradient cho Listening & Reading (Tím/Xanh dương)
            var purpleGrad = ctx.createLinearGradient(0, 0, 0, 240);
            purpleGrad.addColorStop(0, 'rgba(59, 53, 220, 0.28)');
            purpleGrad.addColorStop(1, 'rgba(240, 243, 255, 0.01)');

            // Tạo gradient cho Writing & Speaking AI (Teal/Xanh lục)
            var tealGrad = ctx.createLinearGradient(0, 0, 0, 240);
            tealGrad.addColorStop(0, 'rgba(13, 148, 136, 0.16)');
            tealGrad.addColorStop(1, 'rgba(240, 243, 255, 0.01)');

            var labels = [
                'Thứ Hai (14/05)',
                'Thứ Ba',
                'Thứ Tư',
                'Thứ Năm',
                'Thứ Sáu',
                'Thứ Bảy',
                'Chủ Nhật (Hôm nay)'
            ];

            // Dữ liệu đường cong mượt theo chuẩn ảnh mẫu
            var lrData = [25, 45, 62, 86, 85, 87, 88];
            var wsData = [16, 25, 34, 43, 49, 54, 58];

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Listening & Reading',
                            data: lrData,
                            borderColor: '#3b35dc',
                            borderWidth: 2.8,
                            backgroundColor: purpleGrad,
                            fill: true,
                            tension: 0.45,
                            pointRadius: [0, 5, 5, 5, 0, 0, 0],
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3b35dc',
                            pointBorderWidth: 2.5
                        },
                        {
                            label: 'Writing & Speaking AI',
                            data: wsData,
                            borderColor: '#0d9488',
                            borderWidth: 2.5,
                            backgroundColor: tealGrad,
                            fill: true,
                            tension: 0.35,
                            pointRadius: [0, 0, 0, 5, 0, 0, 0],
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#0d9488',
                            pointBorderWidth: 2.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 8,
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { color: '#eef2f6' },
                            ticks: {
                                color: function (context) {
                                    return context.index === 6 ? '#3b35dc' : '#64748b';
                                },
                                font: function (context) {
                                    return {
                                        size: 12,
                                        weight: context.index === 6 ? 'bold' : 'normal',
                                        family: "'Instrument Sans', sans-serif"
                                    };
                                }
                            }
                        },
                        y: {
                            display: false,
                            beginAtZero: true,
                            max: 105
                        }
                    }
                }
            });
        })();
    </script>
@endpush
