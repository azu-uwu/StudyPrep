<x-layouts.app active="ho-so" title="StudyPrep - Hồ sơ học viên">
@push('styles')
<style>
:root{--purple:#5b50ff;--purple-dark:#493de0;--ink:#171a35;--muted:#7c8499;--line:#e9ebf2;--red:#ff2e0b;--green:#20c900;--blue:#2864ff}
*{box-sizing:border-box}body{margin:0;background:#fff;color:var(--ink);font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;font-size:12px}
button{font:inherit;cursor:pointer}.close{position:fixed;top:15px;left:22px;border:0;background:transparent;color:#77819b;font-size:16px;font-weight:300;padding:3px 5px}
main{width:min(100% - 28px,262px);margin:39px auto 0;text-align:center}
.eyebrow{font-size:7px;color:#7e879c;letter-spacing:.25px;margin-bottom:7px}
h1{font-size:12px;line-height:1.4;font-weight:750;margin:0 0 4px;letter-spacing:-.1px}
.subtitle{font-size:8px;color:#788198}
.score-wrap{height:103px;display:grid;place-items:center;position:relative;margin:9px 0 8px}
.score-ring{width:70px;height:70px;position:relative}
.score-ring svg{width:70px;height:70px;transform:rotate(-90deg)}
.ring-track{fill:none;stroke:#f0f2f6;stroke-width:8}
.ring-progress{fill:none;stroke:var(--red);stroke-width:8;stroke-linecap:round;stroke-dasharray:18 220}
.score-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;padding-top:7px}
.score{font-size:16px;font-weight:800;color:var(--red);line-height:1.2}
.score-label{font-size:7px;color:#8a91a3;margin-top:3px}
.stats-card,.parts-card{border:1px solid var(--line);border-radius:9px;text-align:left;overflow:hidden}
.stats-row{display:grid;grid-template-columns:repeat(3,1fr);padding:7px 4px 6px;text-align:center}
.stat+.stat{border-left:1px solid #f0f1f5}
.stat-number{font-size:11px;font-weight:750;line-height:1.35}
.stat-label{font-size:7px;color:#7d869b;margin-top:1px}
.stats-note{border-top:1px solid #f0f1f5;text-align:center;color:#858da0;font-size:6.5px;padding:7px 4px}
.parts-card{margin-top:8px;padding:11px 10px 10px}
.parts-title{text-transform:uppercase;letter-spacing:.15px;font-size:7px;color:#7b8499;margin-bottom:12px}
.part-row{display:grid;grid-template-columns:39px minmax(0,1fr) 25px;gap:7px;align-items:center;margin:0 0 13px}
.part-row:last-child{margin-bottom:3px}
.part-name{font-size:8px;font-weight:650}
.bar-track{height:4px;border-radius:5px;background:#f0f1f5;overflow:hidden}
.bar{height:100%;border-radius:5px;background:var(--red)}
.bar.green{background:var(--green)}
.part-pct{text-align:right;font-size:8px;font-weight:650}
.actions{border-top:1px solid #f2f3f7;margin-top:12px;padding-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:8px}
.btn{height:27px;border-radius:7px;font-size:7.5px;font-weight:700}
.btn-primary{border:0;color:#fff;background:var(--purple);box-shadow:0 2px 0 var(--purple-dark)}
.btn-secondary{border:1px solid #e0e4ee;background:#fff;color:#222b48;box-shadow:0 2px 0 #d8ddea}
.continue{display:inline-block;border:0;background:transparent;color:var(--blue);font-size:8px;margin-top:9px;padding:0 8px}
.modal-backdrop{position:fixed;inset:0;background:#12193655;display:grid;place-items:center;padding:18px}
.modal-backdrop[hidden]{display:none}
.modal{width:min(100%,300px);background:white;border:1px solid var(--line);border-radius:12px;padding:18px;text-align:left;box-shadow:0 16px 50px #12193625}
.modal h2{font-size:13px;margin:0 0 8px}.modal p{font-size:10px;color:var(--muted);line-height:1.6;margin:0}
.modal-actions{display:flex;justify-content:flex-end;gap:7px;margin-top:15px}
.modal-actions button{height:27px;padding:0 12px;border-radius:7px;font-size:9px}
@media(min-height:700px){main{margin-top:40px}.score-wrap{margin-top:11px;margin-bottom:8px}.stats-row{padding-top:8px;padding-bottom:7px}.parts-card{padding-top:12px}}
</style>
@endpush
<button class="close" id="closeBtn" aria-label="Đóng" title="Đóng">×</button>
<main>
  <div class="eyebrow">KẾT QUẢ BÀI LUYỆN</div>
  <h1>Test 1 TOEIC NEW 2026 · 5 part</h1>
  <div class="subtitle">164 câu · 14 phút</div>

  <div class="score-wrap" aria-label="Điểm chính xác 7 phần trăm">
    <div class="score-ring">
      <svg viewBox="0 0 70 70" aria-hidden="true">
        <circle class="ring-track" cx="35" cy="35" r="31"></circle>
        <circle class="ring-progress" id="ringProgress" cx="35" cy="35" r="31"></circle>
      </svg>
      <div class="score-center"><div class="score" id="scoreText">7%</div><div class="score-label">chính xác</div></div>
    </div>
  </div>

  <section class="stats-card" aria-label="Tổng kết kết quả">
    <div class="stats-row">
      <div class="stat"><div class="stat-number">12</div><div class="stat-label">Đúng</div></div>
      <div class="stat"><div class="stat-number">16</div><div class="stat-label">Sai</div></div>
      <div class="stat"><div class="stat-number">136</div><div class="stat-label">Chưa trả lời</div></div>
    </div>
    <div class="stats-note">Bài làm chưa xong, 136 câu chưa trả lời không tính là sai.</div>
  </section>

  <section class="parts-card">
    <div class="parts-title">ĐỘ CHÍNH XÁC THEO PART</div>
    <div class="part-row"><div class="part-name">Part 2</div><div class="bar-track"><div class="bar" style="width:0%"></div></div><div class="part-pct">0%</div></div>
    <div class="part-row"><div class="part-name">Part 3</div><div class="bar-track"><div class="bar" style="width:33%"></div></div><div class="part-pct">33%</div></div>
    <div class="part-row"><div class="part-name">Part 5</div><div class="bar-track"><div class="bar" style="width:38%"></div></div><div class="part-pct">38%</div></div>
    <div class="part-row"><div class="part-name">Part 6</div><div class="bar-track"><div class="bar green" style="width:75%"></div></div><div class="part-pct">75%</div></div>
    <div class="part-row"><div class="part-name">Part 7</div><div class="bar-track"><div class="bar" style="width:37%"></div></div><div class="part-pct">37%</div></div>
  </section>

  <div class="actions">
    <button class="btn btn-primary" id="retryBtn">Làm lại</button>
    <button class="btn btn-secondary" id="answersBtn">Xem đáp án</button>
  </div>
  <button class="continue" id="continueBtn">Tiếp tục</button>
</main>

<div class="modal-backdrop" id="modal" hidden>
  <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <h2 id="modalTitle">Thông báo</h2><p id="modalText"></p>
    <div class="modal-actions"><button class="btn btn-secondary" id="cancelBtn">Đóng</button><button class="btn btn-primary" id="confirmBtn">Xác nhận</button></div>
  </section>
</div>

@push('scripts')
<script>
const $=id=>document.getElementById(id);
function modal(title,text,action=()=>{}){$('modalTitle').textContent=title;$('modalText').textContent=text;$('modal').hidden=false;$('confirmBtn').onclick=()=>{$('modal').hidden=true;action()}}
$('closeBtn').onclick=()=>modal('Thoát kết quả','Bạn muốn đóng màn hình kết quả bài luyện?',()=>{});
$('retryBtn').onclick=()=>modal('Làm lại bài thi?','Bạn sẽ bắt đầu lại bài Test 1 TOEIC NEW 2026 từ câu đầu tiên.',()=>{});
$('answersBtn').onclick=()=>modal('Xem đáp án','Mở danh sách đáp án và lời giải cho các câu đã làm. Bản demo chưa kết nối dữ liệu đáp án.',()=>{});
$('continueBtn').onclick=()=>modal('Tiếp tục luyện tập','Quay lại bài luyện để tiếp tục làm các câu chưa trả lời.',()=>{});
$('cancelBtn').onclick=()=>$('modal').hidden=true;
document.addEventListener('keydown',e=>{if(e.key==='Escape')$('modal').hidden=true});
</script>
@endpush

</x-layouts.app>
