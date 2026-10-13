
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
        2. Button
    </h2>

    <x-button>Primary</x-button>
    <x-button variant="secondary">Secondary</x-button>
    <x-button variant="danger">Xóa</x-button>

</div>
