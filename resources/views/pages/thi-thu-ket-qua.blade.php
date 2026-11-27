<x-layouts.app active="thi-thu" title="StudyPrep - Kết quả bài thi">
@push('styles')
<style>
:root{--purple:#5b50ff;--purple-dark:#493de0;--ink:#171a35;--muted:#7c8499;--line:#e9ebf2;--red:#ff2e0b;--green:#059669;--blue:#2864ff}
*{box-sizing:border-box}body{margin:0;background:#fff;color:var(--ink);font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;font-size:12px}
button{font:inherit;cursor:pointer}.close{position:fixed;top:15px;left:22px;border:0;background:transparent;color:#77819b;font-size:16px;font-weight:300;padding:3px 5px}
main{width:min(100% - 28px,340px);margin:30px auto 40px;text-align:center}
.eyebrow{font-size:9px;color:#7e879c;letter-spacing:.5px;margin-bottom:7px;font-weight:700}
h1{font-size:15px;line-height:1.4;font-weight:800;margin:0 0 4px;letter-spacing:-.2px;color:#1e293b}
.subtitle{font-size:11px;color:#64748b;margin-bottom:12px}
.score-wrap{height:115px;display:grid;place-items:center;position:relative;margin:10px 0 14px}
.score-ring{width:84px;height:84px;position:relative}
.score-ring svg{width:84px;height:84px;transform:rotate(-90deg)}
.ring-track{fill:none;stroke:#f0f2f6;stroke-width:8}
.ring-progress{fill:none;stroke:{{ $accuracy >= 50 ? 'var(--green)' : 'var(--red)' }};stroke-width:8;stroke-linecap:round;stroke-dasharray:{{ (int) round(($accuracy / 100) * 195) }} 220}
.score-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.score{font-size:18px;font-weight:850;color:{{ $accuracy >= 50 ? 'var(--green)' : 'var(--red)' }};line-height:1.2}
.score-label{font-size:8px;color:#8a91a3;margin-top:2px;font-weight:600}
.stats-card,.parts-card{border:1px solid var(--line);border-radius:12px;text-align:left;overflow:hidden;box-shadow:0 2px 8px #1e293b05}
.stats-row{display:grid;grid-template-columns:repeat(3,1fr);padding:10px 6px;text-align:center}
.stat+.stat{border-left:1px solid #f0f1f5}
.stat-number{font-size:15px;font-weight:800;line-height:1.35;color:#1e293b}
.stat-label{font-size:9px;color:#7d869b;margin-top:2px;font-weight:600}
.stats-note{border-top:1px solid #f0f1f5;text-align:center;color:#858da0;font-size:9px;padding:8px 6px;background:#fafafa}
.parts-card{margin-top:12px;padding:14px 12px 12px}
.parts-title{text-transform:uppercase;letter-spacing:.3px;font-size:9px;color:#7b8499;margin-bottom:14px;font-weight:750}
.part-row{display:grid;grid-template-columns:55px minmax(0,1fr) 50px;gap:8px;align-items:center;margin:0 0 12px}
.part-row:last-child{margin-bottom:2px}
.part-name{font-size:10px;font-weight:700;color:#334155}
.bar-track{height:6px;border-radius:5px;background:#f0f1f5;overflow:hidden}
.bar{height:100%;border-radius:5px;background:var(--red)}
.bar.green{background:var(--green)}
.part-pct{text-align:right;font-size:10px;font-weight:700;color:#475569}
.actions{border-top:1px solid #f2f3f7;margin-top:16px;padding-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:10px}
.btn{height:34px;border-radius:9px;font-size:11px;font-weight:750;display:inline-flex;align-items:center;justify-content:center;text-decoration:none}
.btn-primary{border:0;color:#fff;background:var(--purple);box-shadow:0 2px 0 var(--purple-dark)}
.btn-secondary{border:1px solid #e0e4ee;background:#fff;color:#222b48;box-shadow:0 2px 0 #d8ddea}
.continue{display:inline-block;border:0;background:transparent;color:var(--blue);font-size:11px;margin-top:14px;padding:4px 12px;text-decoration:none;font-weight:600}
</style>
@endpush

<a href="{{ route('thi-thu') }}" class="close" aria-label="Đóng" title="Về danh sách đề thi">✕</a>

<main>
  <div class="eyebrow">KẾT QUẢ BÀI THI THỬ</div>
  <h1>{{ $test->title }}</h1>
  <div class="subtitle">{{ $totalQuestions }} câu · {{ $durationMinutes }} phút · {{ $attempt->session_type === 'FULL_TEST' ? 'Full Test' : 'Luyện từng phần' }}</div>

  <div class="score-wrap" aria-label="Độ chính xác {{ $accuracy }}%">
    <div class="score-ring">
      <svg viewBox="0 0 84 84" aria-hidden="true">
        <circle class="ring-track" cx="42" cy="42" r="31"></circle>
        <circle class="ring-progress" cx="42" cy="42" r="31"></circle>
      </svg>
      <div class="score-center">
        <div class="score">{{ $accuracy }}%</div>
        <div class="score-label">độ chính xác</div>
      </div>
    </div>
  </div>

  <section class="stats-card" aria-label="Tổng kết kết quả">
    <div class="stats-row">
      <div class="stat"><div class="stat-number" style="color:var(--green)">{{ $correctCount }}</div><div class="stat-label">Đúng</div></div>
      <div class="stat"><div class="stat-number" style="color:var(--red)">{{ $incorrectCount }}</div><div class="stat-label">Sai</div></div>
      <div class="stat"><div class="stat-number" style="color:#64748b">{{ $unansweredCount }}</div><div class="stat-label">Chưa làm</div></div>
    </div>
    @if(isset($scoreResult) && $scoreResult->total_score > 0)
    <div class="stats-note" style="font-weight:700; color:#1e293b;">
        Điểm ước lượng: {{ (int) $scoreResult->total_score }} điểm
        @if($scoreResult->listening_score > 0 || $scoreResult->reading_score > 0)
            (LC: {{ (int) $scoreResult->listening_score }} | RC: {{ (int) $scoreResult->reading_score }})
        @endif
    </div>
    @else
    <div class="stats-note">
        {{ $unansweredCount > 0 ? "Còn {$unansweredCount} câu chưa trả lời." : "Đã hoàn thành toàn bộ câu hỏi." }}
    </div>
    @endif
  </section>

  @if(!empty($partStats))
  <section class="parts-card">
    <div class="parts-title">KẾT QUẢ CHI TIẾT THEO PART</div>
    @foreach($partStats as $part)
    <div class="part-row">
      <div class="part-name">{{ $part['code'] }}</div>
      <div class="bar-track">
        <div class="bar {{ $part['percentage'] >= 50 ? 'green' : '' }}" style="width:{{ $part['percentage'] }}%"></div>
      </div>
      <div class="part-pct">{{ $part['correct'] }}/{{ $part['total'] }} ({{ $part['percentage'] }}%)</div>
    </div>
    @endforeach
  </section>
  @endif

  <div class="actions">
    <a href="{{ route('thi-thu.gioi-thieu-de', $test->test_form_id) }}" class="btn btn-primary">Làm lại đề này</a>
    <a href="{{ route('thi-thu') }}" class="btn btn-secondary">Danh sách đề</a>
  </div>
  <a href="{{ url('/luyen-tap') }}" class="continue">Về trang Luyện tập →</a>
</main>
</x-layouts.app>
