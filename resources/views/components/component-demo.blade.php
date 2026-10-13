
<div style="max-width: 600px; margin: 40px auto; padding: 30px; font-family: Arial, sans-serif; color: #111827;">

    <h1 style="font-size: 28px; margin-bottom: 8px;">
        Component Demo
    </h1>

    <p style="color: #6b7280; margin-bottom: 30px;">
        Demo component dùng chung
    </p>

    <h2 style="font-size: 18px;">1. Input</h2>

    <x-input
        label="Email"
        name="email"
        type="email"
        placeholder="Nhập email của bạn"
    />

    <x-input
        label="Mật khẩu"
        name="password"
        type="password"
        placeholder="Nhập mật khẩu"
    />

    <x-input
        label="Họ và tên"
        name="full_name"
    />

    <h2 style="font-size: 18px; margin-top: 30px;">
        2. Button Tactile 3D (theo spec Figma)
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
