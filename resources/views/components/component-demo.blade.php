
<div style="max-width: 860px; margin: 40px auto; padding: 30px; font-family: 'Nunito', Arial, sans-serif; color: #111C2D; background: #F9F9FF;">

    <h1 style="font-size: 28px; margin-bottom: 8px;">
        Component Demo
    </h1>

    <p style="color: #6b7280; margin-bottom: 30px;">
        Demo component dùng chung
    </p>

    <h2 style="font-size: 18px;">1. Input — Ma tran trang thai co ban</h2>
    <p style="color:#414755;font-size:13px;">Default (vien #E2E8F0) / Filled (#CBD5E1) / Disabled (nen #CFDAF2) / Focus (click vao o Default de xem glow #0057BF).</p>

    <x-input label="Ho va ten (Default)" name="demo_default" placeholder="Vi du: Nguyen Van A" helper="Trang thai nghi tu nhien" />

    <x-input label="Email dang nhap (Focus demo — click vao de xem ring)" name="demo_focus" value="hocvien@studyprep.vn" helper="Email nhan thong bao ket qua" />

    <x-input label="Muc tieu IELTS (Filled)" name="demo_filled" state="filled" value="7.5 Overall (Band Target)" helper="Du lieu hoan tat, tuong phan cao" />
    <x-input label="Ma hoc vien (Disabled)" name="demo_disabled" value="SP-2025-8899" disabled helper="Da khoa bao mat he thong" />

    <h2 style="font-size: 18px; margin-top: 26px;">2. Input — Validation (Success / Error)</h2>
    <x-input label="Ten dang nhap (Success)" name="demo_success" value="nguyenan_ielts" success="Ten nguoi dung hop le" />
    <x-input label="Mat khau moi (Error)" name="demo_error" value="12345" error="Mat khau phai co it nhat 8 ky tu" />
    <h2 style="font-size: 18px; margin-top: 26px;">3. Input — Bien the chuyen dung</h2>
    <x-input label="Search (icon + nut xoa)" name="demo_search" type="search" value="Tu vung Band 8.0" helper="Leading icon 20px, clear 24px" />
    <x-input label="Password (an/hien)" name="demo_password" type="password" value="StudyPrep2025!" helper="Bam icon mat de doi trang thai" />
    <x-input label="Prefix addon" name="demo_prefix" value="trang.ielts80" prefix="studyprep.vn/" helper="Prefix #CFDAF2" />
    <x-input label="Suffix addon" name="demo_suffix" value="7.5" suffix="Overall" helper="Don vi tinh co dinh" />
    <x-input label="Textarea (ghi chu + dem)" name="demo_note" type="textarea" rows="3" maxlength="500" value="Can xem lai collocations Environment." helper="Radius 14px" />
<!--DEMO4-->

    <h2 style="font-size: 18px; margin-top: 26px;">4. Input — Size (sm 36 / md 44 / lg 54)</h2>
    <x-input label="Small 36px" name="demo_sm" size="sm" placeholder="Bo loc nhanh" />
    <x-input label="Standard 44px (khuyen dung)" name="demo_md" size="md" placeholder="Nhap cau tra loi" />
    <x-input label="Large 54px" name="demo_lg" size="lg" placeholder="Tim kiem nhanh" />
    <h2 style="font-size: 18px; margin-top: 30px;">
        5. Button Tactile 3D (theo spec Figma)
    </h2>

    <h3 style="font-size: 15px; margin: 18px 0 12px;">Sizes: small 36px / medium 44px / large 56px</h3>
    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 8px;">
        <x-button size="small">Làm Full Test</x-button>
        <x-button size="medium">Làm Full Test</x-button>
        <x-button size="large">Làm Full Test</x-button>
    </div>

    <h3 style="font-size: 15px; margin: 18px 0 12px;">Variants</h3>
    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 8px;">
        <x-button variant="primary">Làm Full Test</x-button>
        <x-button variant="neutral">Bắt đầu →</x-button>
        <x-button variant="success">Nộp bài thi</x-button>
        <x-button variant="warning">Tạm dừng</x-button>
        <x-button variant="danger">Thoát</x-button>
    </div>

    <h3 style="font-size: 15px; margin: 18px 0 12px;">Disabled + Link + Thuộc tính bổ sung</h3>
    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 8px;">
        <x-button variant="primary" disabled>Làm Full Test</x-button>
        <x-button variant="success" type="submit" name="action" value="submit-exam">Nộp bài thi</x-button>
        <x-button variant="neutral" href="/component-demo" class="demo-link">Bắt đầu →</x-button>
    </div>

</div>
