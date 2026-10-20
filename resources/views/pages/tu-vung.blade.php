<x-layouts.app active="tu-vung" title="StudyPrep - Học từ vựng & Flashcard hồi lượt thi">
    {{-- Header tiêu đề trang --}}
    <div class="sp-page-header">
        <h1 class="sp-page-title">Học từ vựng TOEIC &amp; Flashcard</h1>
        <p class="sp-page-desc">Ghi nhớ từ vựng trọng tâm theo chủ đề. Hoàn thành bài học để hồi thêm lượt thi thử!</p>
    </div>

    {{-- Banner hồi lượt thi --}}
    <div class="sp-banner sp-banner--purple sp-flex-between">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 26px; flex: none;">
                ⚡
            </div>
            <div>
                <h3 style="font-size: 17px; font-weight: 800; margin: 0 0 4px;">Cơ chế hồi lượt thi (Recovery Rule)</h3>
                <p style="font-size: 13px; font-weight: 500; opacity: 0.9; margin: 0;">Hoàn thành ôn tập 10 thẻ Flashcard dưới đây để được <strong>cộng +1 Lượt thi thử</strong> (tối đa 5 lượt).</p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 13px; font-weight: 700; background: rgba(255, 255, 255, 0.15); padding: 6px 14px; border-radius: 999px;">Tiến độ hôm nay: 8/10 thẻ</span>
        </div>
    </div>

    {{-- Khung Flashcard tương tác (Interactive 3D Flip Flashcard) --}}
    <div style="background: #FFFFFF; border: 1px solid #EEF1F7; border-radius: 20px; padding: 32px; margin-bottom: 32px; box-shadow: 0 4px 16px rgba(17, 28, 45, 0.03);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <span class="sp-badge sp-badge--primary">Thẻ Flashcard 1 / 10 • Chủ đề: Contracts</span>
            <span style="font-size: 13px; font-weight: 700; color: #64748B;">Click thẻ để lật xem nghĩa</span>
        </div>

        {{-- Flip card container --}}
        <div id="spFlashcard" class="sp-flashcard" onclick="toggleFlip()">
            {{-- Mặt trước: Tiếng Anh + IPA --}}
            <div class="sp-flashcard__face sp-flashcard__face--front">
                <span class="sp-badge sp-badge--neutral" style="margin-bottom: 16px;">noun / danh từ</span>
                <h2 style="font-size: 38px; font-weight: 800; color: #111C2D; margin: 0 0 8px;">negotiation</h2>
                <p style="font-size: 17px; font-weight: 600; color: #0076FE; margin: 0 0 20px;">/nɪˌɡoʊ.ʃiˈeɪ.ʃən/</p>
                <p style="font-size: 14px; font-weight: 600; color: #64748B; margin: 0;">(Nhấn vào đây để xem nghĩa &amp; ví dụ câu)</p>
            </div>

            {{-- Mặt sau: Tiếng Việt + Ví dụ --}}
            <div class="sp-flashcard__face sp-flashcard__face--back">
                <span class="sp-badge sp-badge--success" style="margin-bottom: 12px;">Định nghĩa tiếng Việt</span>
                <h3 style="font-size: 26px; font-weight: 800; color: #059669; margin: 0 0 14px;">Sự đàm phán, thương lượng</h3>
                <div style="background: #F8FAFC; border-radius: 12px; padding: 14px 18px; text-align: left; max-width: 520px; margin: 0 auto; width: 100%;">
                    <p style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 6px;">
                        "The contract is still under <u>negotiation</u> between both parties."
                    </p>
                    <p style="font-size: 13px; font-weight: 500; color: #64748B; margin: 0;">
                        → Hợp đồng vẫn đang trong quá trình thương lượng giữa hai bên.
                    </p>
                </div>
            </div>
        </div>

        {{-- Nút hành động đánh giá thẻ --}}
        <div class="sp-flashcard-actions">
            <x-button variant="danger" size="medium" onclick="nextCard(false)">❌ Chưa thuộc</x-button>
            <x-button variant="neutral" size="medium" onclick="toggleFlip()">🔄 Lật thẻ</x-button>
            <x-button variant="success" size="medium" onclick="nextCard(true)">✅ Đã nhớ (+1 Tiến độ)</x-button>
        </div>
    </div>

    {{-- Bộ tìm kiếm và danh mục từ vựng --}}
    <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
        <div style="flex: 1; min-width: 280px; max-width: 480px;">
            <x-input label="Tìm kiếm từ vựng" name="search_vocab" type="search" placeholder="Gõ từ khóa tiếng Anh hoặc nghĩa tiếng Việt..." />
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <span class="sp-badge sp-badge--primary" style="cursor: pointer;">Tất cả</span>
            <span class="sp-badge sp-badge--neutral" style="cursor: pointer;">Hợp đồng (Contracts)</span>
            <span class="sp-badge sp-badge--neutral" style="cursor: pointer;">Tiếp thị (Marketing)</span>
            <span class="sp-badge sp-badge--neutral" style="cursor: pointer;">Tài chính (Finance)</span>
            <span class="sp-badge sp-badge--neutral" style="cursor: pointer;">Nhân sự (HR)</span>
        </div>
    </div>

    {{-- Bảng danh sách từ vựng chi tiết --}}
    <div class="sp-table-wrapper">
        <table class="sp-table">
            <thead>
                <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0; color: #475569; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 0.4px;">
                    <th style="padding: 16px 20px;">Từ vựng</th>
                    <th style="padding: 16px 20px;">Từ loại &amp; IPA</th>
                    <th style="padding: 16px 20px;">Nghĩa tiếng Việt</th>
                    <th style="padding: 16px 20px;">Ví dụ minh họa</th>
                    <th style="padding: 16px 20px; text-align: center;">Trạng thái</th>
                </tr>
            </thead>
            <tbody style="font-weight: 600; color: #1E293B;">
                <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 16px 20px; font-weight: 800; color: #0076FE; font-size: 16px;">abide by</td>
                    <td style="padding: 16px 20px;"><span class="sp-badge sp-badge--neutral">verb</span> <span style="color:#64748B; margin-left:4px;">/əˈbaɪd baɪ/</span></td>
                    <td style="padding: 16px 20px;">Tuân theo, làm theo thỏa thuận</td>
                    <td style="padding: 16px 20px; font-size: 13px; color: #475569;">Both parties must abide by the terms of the agreement.</td>
                    <td style="padding: 16px 20px; text-align: center;"><span class="sp-badge sp-badge--success">Đã thuộc</span></td>
                </tr>
                <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 16px 20px; font-weight: 800; color: #0076FE; font-size: 16px;">agreement</td>
                    <td style="padding: 16px 20px;"><span class="sp-badge sp-badge--neutral">noun</span> <span style="color:#64748B; margin-left:4px;">/əˈɡriː.mənt/</span></td>
                    <td style="padding: 16px 20px;">Hợp đồng, hiệp định, thỏa thuận</td>
                    <td style="padding: 16px 20px; font-size: 13px; color: #475569;">They have signed an agreement to cooperate on the project.</td>
                    <td style="padding: 16px 20px; text-align: center;"><span class="sp-badge sp-badge--success">Đã thuộc</span></td>
                </tr>
                <tr style="border-bottom: 1px solid #F1F5F9;">
                    <td style="padding: 16px 20px; font-weight: 800; color: #0076FE; font-size: 16px;">assurance</td>
                    <td style="padding: 16px 20px;"><span class="sp-badge sp-badge--neutral">noun</span> <span style="color:#64748B; margin-left:4px;">/əˈʃʊr.əns/</span></td>
                    <td style="padding: 16px 20px;">Sự bảo đảm, cam đoan chắc chắn</td>
                    <td style="padding: 16px 20px; font-size: 13px; color: #475569;">He gave an assurance that the work would be completed on time.</td>
                    <td style="padding: 16px 20px; text-align: center;"><span class="sp-badge sp-badge--warning">Đang học</span></td>
                </tr>
                <tr>
                    <td style="padding: 16px 20px; font-weight: 800; color: #0076FE; font-size: 16px;">cancellation</td>
                    <td style="padding: 16px 20px;"><span class="sp-badge sp-badge--neutral">noun</span> <span style="color:#64748B; margin-left:4px;">/ˌkæn.səlˈeɪ.ʃən/</span></td>
                    <td style="padding: 16px 20px;">Sự hủy bỏ, bãi bỏ hợp đồng</td>
                    <td style="padding: 16px 20px; font-size: 13px; color: #475569;">The cancellation clause requires 30 days prior notice.</td>
                    <td style="padding: 16px 20px; text-align: center;"><span class="sp-badge sp-badge--danger">Chưa học</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        function toggleFlip() {
            var card = document.getElementById('spFlashcard');
            card.classList.toggle('is-flipped');
        }

        function nextCard(known) {
            var card = document.getElementById('spFlashcard');
            card.classList.remove('is-flipped');
            if (known) {
                alert('Tuyệt vời! Bạn đã hoàn thành 9/10 thẻ. Chỉ cần 1 thẻ nữa để được hồi +1 Lượt thi!');
            }
        }
    </script>
</x-layouts.app>
