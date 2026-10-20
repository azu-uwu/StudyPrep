@extends('layouts.admin', ['active' => 'dashboard'])

@section('title', 'Tổng quan Dashboard')

@section('content')
    @php
        $adminName = auth()->user()->full_name ?? auth()->user()->user_name ?? 'Admin';
    @endphp

    <section class="rounded-2xl border border-violet-100 bg-white px-5 py-5 shadow-sm sm:px-7">
        <p class="text-xs font-medium text-slate-400">
            Hệ thống
            <span class="text-slate-300">›</span>
            <span class="text-admin-accent">Tổng quan Dashboard</span>
        </p>
        <h1 class="mt-2 font-nunito text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
            {{ $greeting['title'] }}, {{ $adminName }}! <span aria-hidden="true">👋</span>
        </h1>
        <p class="mt-1 max-w-3xl text-sm text-slate-500">{{ $greeting['subtitle'] }}</p>
    </section>

    <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Tổng học viên</p>
                    <p class="mt-2 font-nunito text-3xl font-extrabold text-slate-900">{{ number_format($learnerStats['total']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        Active <span class="font-semibold text-emerald-600">{{ number_format($learnerStats['active']) }}</span>
                        · Locked <span class="font-semibold text-rose-500">{{ number_format($learnerStats['locked']) }}</span>
                    </p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-violet-100 text-admin-accent">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Câu hỏi chờ duyệt</p>
                    <p class="mt-2 font-nunito text-3xl font-extrabold text-slate-900">{{ number_format($questionStats['pending']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        Tổng NHCH <span class="font-semibold text-slate-700">{{ number_format($questionStats['total']) }}</span>
                        · Active {{ number_format($questionStats['active']) }}
                    </p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="m9 14 2 2 4-4"/></svg>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Lượt thi hôm nay</p>
                    <p class="mt-2 font-nunito text-3xl font-extrabold text-slate-900">{{ number_format($attemptStats['today']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">
                        Tuần này <span class="font-semibold text-slate-700">{{ number_format($attemptStats['week']) }}</span> lượt
                    </p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19h16"/><path d="M8 17V9"/><path d="M12 17V5"/><path d="M16 17v-6"/></svg>
                </span>
            </div>
        </article>

        <article class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Tổng từ vựng</p>
                    <p class="mt-2 font-nunito text-3xl font-extrabold text-slate-900">{{ number_format($vocabularyTotal) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Flashcard trong hệ thống</p>
                </div>
                <span class="flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-500">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
                </span>
            </div>
        </article>
    </section>

    <section class="mt-5 rounded-2xl border border-violet-100 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="font-nunito text-lg font-extrabold text-slate-900">Xu hướng luyện thi & Lượt nộp bài</h2>
                <p class="mt-1 text-sm text-slate-500">Tần suất làm đề theo các ngày trong tuần (TOEIC LC/RC & nộp bài)</p>
            </div>
            <div class="rounded-xl bg-violet-50 px-3 py-2 text-right text-xs text-slate-500">
                <p class="font-semibold text-admin-accent">Cập nhật</p>
                <p>tuần hiện tại</p>
                <div class="mt-2 flex flex-col gap-1 text-left">
                    <span class="inline-flex items-center gap-2"><span class="size-2 rounded-full bg-[#6366f1]"></span> Lượt bắt đầu</span>
                    <span class="inline-flex items-center gap-2"><span class="size-2 rounded-full bg-[#14b8a6]"></span> Lượt nộp bài</span>
                </div>
            </div>
        </div>
        <div class="relative mt-4 h-72">
            @if (collect($weeklyChart['started'])->sum() === 0 && collect($weeklyChart['submitted'])->sum() === 0)
                <div class="flex h-full items-center justify-center rounded-xl bg-slate-50 text-sm text-slate-400">
                    Chưa có lượt thi thử trong tuần này.
                </div>
            @else
                <canvas id="weekly-attempt-chart" aria-label="Biểu đồ lượt thi thử trong tuần"></canvas>
            @endif
        </div>
    </section>

    <section class="mt-5 grid gap-5 xl:grid-cols-2">
        <article class="overflow-hidden rounded-2xl border border-violet-100 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 px-5 py-4">
                <div>
                    <h2 class="font-nunito text-lg font-extrabold text-slate-900">Lượt thi mới nhất</h2>
                    <p class="text-sm text-slate-500">5 lần thi thử gần đây kèm điểm Nghe/Đọc</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-bold">Học viên</th>
                            <th class="px-5 py-3 font-bold">Bộ đề</th>
                            <th class="px-5 py-3 font-bold">Nghe / Đọc</th>
                            <th class="px-5 py-3 font-bold">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-violet-50">
                        @forelse ($recentAttempts as $attempt)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-800">{{ $attempt->user->full_name ?? 'Học viên đã xóa' }}</p>
                                    <p class="text-xs text-slate-400">{{ $attempt->user->email ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $attempt->testForm->title ?? 'Đề chưa gắn' }}</td>
                                <td class="px-5 py-3 font-semibold text-slate-800">
                                    @if ($attempt->scoreResult)
                                        {{ rtrim(rtrim((string) $attempt->scoreResult->listening_score, '0'), '.') ?: '—' }}
                                        /
                                        {{ rtrim(rtrim((string) $attempt->scoreResult->reading_score, '0'), '.') ?: '—' }}
                                    @else
                                        <span class="font-medium text-slate-400">Chưa chấm</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-500">
                                    {{ optional($attempt->started_at)->diffForHumans() ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">
                                    Chưa có lượt thi thử nào trong hệ thống.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-violet-100 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 px-5 py-4">
                <div>
                    <h2 class="font-nunito text-lg font-extrabold text-slate-900">Hàng đợi kiểm duyệt & Chấm bài</h2>
                    <p class="text-sm text-slate-500">Câu hỏi mới gửi lên đang chờ Admin phê duyệt</p>
                </div>
                <span class="text-sm font-semibold text-admin-accent">Xem tất cả ({{ number_format($questionStats['pending']) }})</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-400">
                        <tr>
                            <th class="px-5 py-3 font-bold">Mã câu</th>
                            <th class="px-5 py-3 font-bold">Part</th>
                            <th class="px-5 py-3 font-bold">Kỹ năng</th>
                            <th class="px-5 py-3 font-bold">Tác giả</th>
                            <th class="px-5 py-3 font-bold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-violet-50">
                        @forelse ($pendingQuestions as $question)
                            <tr class="hover:bg-violet-50/40">
                                <td class="px-5 py-3 font-semibold text-slate-800">#Q-{{ $question->question_id }}</td>
                                <td class="px-5 py-3 text-slate-600">
                                    {{ $question->section->code ?? '—' }}
                                    @if ($question->questionType)
                                        <span class="block text-xs text-slate-400">{{ $question->questionType->name }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $question->section->skill->skill_name ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-400">Hệ thống</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="#cau-hoi-{{ $question->question_id }}" class="inline-flex rounded-xl border border-violet-200 px-3 py-1.5 text-xs font-semibold text-admin-accent hover:bg-violet-50">
                                        Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">
                                    Không có câu hỏi nào đang chờ duyệt.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection

@push('scripts')
    @if (collect($weeklyChart['started'])->sum() > 0 || collect($weeklyChart['submitted'])->sum() > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                var canvas = document.getElementById('weekly-attempt-chart');
                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                var labels = @json($weeklyChart['labels']);
                var started = @json($weeklyChart['started']);
                var submitted = @json($weeklyChart['submitted']);

                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Lượt bắt đầu',
                                data: started,
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99, 102, 241, 0.18)',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#6366f1',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            },
                            {
                                label: 'Lượt nộp bài',
                                data: submitted,
                                borderColor: '#14b8a6',
                                backgroundColor: 'rgba(20, 184, 166, 0.08)',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#14b8a6',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                            y: { beginAtZero: true, ticks: { precision: 0, color: '#94a3b8' }, grid: { color: '#f1f5f9' } }
                        }
                    }
                });
            })();
        </script>
    @endif
@endpush
