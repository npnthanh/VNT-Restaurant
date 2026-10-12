@extends('layout.website-admin')

@section('content')
    <!-- Top Navigation Bar -->
    <header class="admin-header">
        <div class="header-left">
            <a class="admin-brand" href="{{ route('admin.home') }}">
                <div class="brand-avatar">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div class="brand-info">
                    <span class="brand-name">Tới Bến Quán</span>
                    <span class="brand-caption">PORTAL QUẢN TRỊ WEBSITE</span>
                </div>
            </a>
        </div>

        <div class="header-actions">
            <a class="button subtle preview-site-btn" href="{{ route('home') }}" target="_blank" rel="noopener">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Xem trang khách</span>
            </a>

            <div class="user-profile-pill">
                <div class="user-avatar">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="user-meta">
                    <span class="account-name">{{ auth('staff')->user()->name }}</span>
                    <span class="account-role">Quản trị viên</span>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                @csrf
                <button class="logout-button" type="submit" title="Đăng xuất khỏi hệ thống">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </header>

    <div class="admin-shell">
        <!-- Floating Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-inner">
                <div class="sidebar-heading">
                    <i class="fa-solid fa-sliders"></i>
                    <span>MỤC CẤU HÌNH</span>
                </div>

                <nav aria-label="Các nhóm cấu hình" class="sidebar-nav">
                    <a class="nav-item active" href="#brand">
                        <span class="nav-num"><i class="fa-solid fa-signature"></i></span>
                        <span class="nav-label">Thương hiệu</span>
                    </a>
                    <a class="nav-item" href="#contact">
                        <span class="nav-num"><i class="fa-solid fa-address-book"></i></span>
                        <span class="nav-label">Thông tin liên hệ</span>
                    </a>
                    <a class="nav-item" href="#social">
                        <span class="nav-num"><i class="fa-solid fa-share-nodes"></i></span>
                        <span class="nav-label">Mạng xã hội</span>
                    </a>
                    <a class="nav-item" href="#banners">
                        <span class="nav-num"><i class="fa-solid fa-images"></i></span>
                        <span class="nav-label">Banner trang chủ</span>
                    </a>
                    <a class="nav-item" href="#content">
                        <span class="nav-num"><i class="fa-solid fa-quote-left"></i></span>
                        <span class="nav-label">Nội dung khẩu hiệu</span>
                    </a>
                </nav>

                <div class="sidebar-note">
                    <div class="sync-badge">
                        <span class="status-pulse-dot"></span>
                        <strong>Đồng bộ thời gian thực</strong>
                    </div>
                    <p>Các thay đổi sẽ được cập nhật trực tiếp lên trang khách ngay sau khi bạn bấm <b>Lưu cấu hình</b>.</p>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="settings-main">
            <!-- Hero Page Heading -->
            <div class="page-heading-card">
                <div class="page-heading-content">
                    <div class="eyebrow-tag">
                        <i class="fa-solid fa-cube"></i>
                        <span>QUẢN TRỊ HỆ THỐNG / GIAO DIỆN KHÁCH HÀNG</span>
                    </div>
                    <h1>Cấu hình trang khách</h1>
                    <p>Tùy biến nhận diện thương hiệu, thông điệp truyền thông, banner quảng cáo và thông tin liên hệ một cách trực quan.</p>
                </div>

                <div class="page-heading-badges">
                    <div class="stat-pill">
                        <i class="fa-solid fa-shield-check"></i>
                        <span>Bảo mật 100%</span>
                    </div>
                    <div class="stat-pill accent">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Tải nhanh & Tối ưu</span>
                    </div>
                </div>
            </div>

            <!-- Feedback Alerts -->
            @if(session('success'))
                <div class="notice success" role="status">
                    <div class="notice-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="notice-body">
                        <strong>Thành công!</strong>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="notice error" role="alert">
                    <div class="notice-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="notice-body">
                        <strong>Chưa lưu được cấu hình!</strong>
                        <p>Vui lòng kiểm tra lại các lỗi bên dưới:</p>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <small>Nếu bạn đã chọn tệp hình ảnh trước đó, vui lòng tải lại ảnh trước khi lưu.</small>
                    </div>
                </div>
            @endif

            <!-- Settings Form -->
            <form id="settings-form" method="POST" action="{{ route('admin.website-settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- 01: Brand Identity -->
                <section class="settings-card" id="brand">
                    <div class="card-heading">
                        <div class="section-icon-badge">
                            <i class="fa-solid fa-palette"></i>
                        </div>
                        <div class="card-heading-text">
                            <div class="section-tag">MỤC 01</div>
                            <h2>Nhận diện thương hiệu</h2>
                            <p>Logo nhà hàng và tên xuất hiện trên header, footer và tiêu đề trang khách.</p>
                        </div>
                    </div>

                    <div class="brand-grid">
                        <div class="brand-assets">
                            @include('admin.partials.image-upload', ['name' => 'logo', 'label' => 'Logo website'])
                            @include('admin.partials.image-upload', ['name' => 'favicon', 'label' => 'Favicon website'])
                        </div>

                        <div class="field-grid">
                            @include('admin.partials.field', ['name' => 'site_name', 'label' => 'Tên thương hiệu / website', 'required' => true, 'max' => 100, 'wide' => true])
                            @include('admin.partials.field', ['name' => 'copyright', 'label' => 'Dòng bản quyền chân trang', 'max' => 200, 'wide' => true, 'hint' => 'Hiển thị ở góc chân trang (footer).'])

                            <div class="tip-box wide">
                                <div class="tip-icon"><i class="fa-solid fa-lightbulb"></i></div>
                                <div class="tip-content">
                                    <strong>Gợi ý hình ảnh:</strong> Nên dùng logo định dạng PNG hoặc WebP có nền trong suốt để hiển thị sắc nét nhất trên cả thanh header màu xanh rêu của nhà hàng.
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 02: Contact Information -->
                <section class="settings-card" id="contact">
                    <div class="card-heading">
                        <div class="section-icon-badge">
                            <i class="fa-solid fa-phone-volume"></i>
                        </div>
                        <div class="card-heading-text">
                            <div class="section-tag">MỤC 02</div>
                            <h2>Thông tin liên hệ</h2>
                            <p>Được hiển thị đồng bộ ở thanh điều hướng, chân trang và trang thông tin liên hệ.</p>
                        </div>
                    </div>

                    <div class="field-grid">
                        @include('admin.partials.field', ['name' => 'hotline', 'label' => 'Hotline đặt bàn', 'type' => 'tel', 'required' => true, 'max' => 30])
                        @include('admin.partials.field', ['name' => 'email', 'label' => 'Email hỗ trợ', 'type' => 'email', 'required' => true, 'max' => 254])
                        @include('admin.partials.field', ['name' => 'address', 'label' => 'Địa chỉ chính của cơ sở', 'max' => 500, 'wide' => true])
                    </div>
                </section>

                <!-- 03: Social Media Links -->
                <section class="settings-card" id="social">
                    <div class="card-heading">
                        <div class="section-icon-badge">
                            <i class="fa-solid fa-share-nodes"></i>
                        </div>
                        <div class="card-heading-text">
                            <div class="section-tag">MỤC 03</div>
                            <h2>Kênh truyền thông & Mạng xã hội</h2>
                            <p>Nhập đường dẫn liên kết đầy đủ (bắt đầu bằng <code>https://</code>). Để trống nếu muốn ẩn kênh đó trên trang khách.</p>
                        </div>
                    </div>

                    <div class="field-grid social-grid">
                        @foreach([
                            'facebook' => ['label' => 'Facebook Fanpage', 'color' => '#1877F2'],
                            'tiktok' => ['label' => 'Kênh TikTok', 'color' => '#000000'],
                            'instagram' => ['label' => 'Tài khoản Instagram', 'color' => '#E1306C'],
                            'youtube' => ['label' => 'Kênh YouTube', 'color' => '#FF0000'],
                            'zalo' => ['label' => 'Zalo OA / Hỗ trợ', 'color' => '#0068FF']
                        ] as $name => $meta)
                            @include('admin.partials.field', [
                                'name' => $name,
                                'label' => $meta['label'],
                                'type' => 'url',
                                'max' => 500,
                                'placeholder' => 'https://'
                            ])
                        @endforeach
                    </div>
                </section>

                <!-- 04: Homepage Banners -->
                <section class="settings-card" id="banners">
                    <div class="card-heading">
                        <div class="section-icon-badge">
                            <i class="fa-solid fa-panorama"></i>
                        </div>
                        <div class="card-heading-text">
                            <div class="section-tag">MỤC 04</div>
                            <h2>Banner trình chiếu trang chủ</h2>
                            <p>Thêm bao nhiêu banner tùy nhu cầu. Dùng nút Lên/Xuống để đổi thứ tự và bật ít nhất một banner.</p>
                        </div>
                    </div>

                    <div class="tip-box">
                        <div class="tip-icon"><i class="fa-solid fa-circle-info"></i></div>
                        <div class="tip-content">
                            <strong>Khuyến nghị kích thước:</strong> Ảnh máy tính khuyến nghị tỉ lệ ngang (~1700 &times; 710 px hoặc 1920 &times; 900 px). Ảnh điện thoại khuyến nghị tỉ lệ dọc (~1080 &times; 1520 px). Nếu bạn không tải ảnh điện thoại riêng, hệ thống sẽ tự động dùng ảnh máy tính.
                        </div>
                    </div>

                    <div class="banner-list-toolbar">
                        <span id="banner-count" aria-live="polite">{{ count($banners) }} banner</span>
                        <button type="button" class="button primary" id="add-banner"><i class="fa-solid fa-plus" aria-hidden="true"></i> Thêm banner</button>
                    </div>
                    <div class="banner-panels-container" id="banner-list">
                        @foreach($banners as $index => $banner)
                            @include('admin.partials.banner', ['index' => $index, 'banner' => $banner])
                        @endforeach
                    </div>
                    <template id="banner-template">
                        @include('admin.partials.banner', ['index' => '__INDEX__', 'banner' => ['id' => null, 'desktop' => null, 'mobile' => null, 'enabled' => true, 'alt' => '']])
                    </template>
                </section>

                <!-- 05: Homepage Content & Slogans -->
                <section class="settings-card" id="content">
                    <div class="card-heading">
                        <div class="section-icon-badge">
                            <i class="fa-solid fa-quote-left"></i>
                        </div>
                        <div class="card-heading-text">
                            <div class="section-tag">MỤC 05</div>
                            <h2>Nội dung & Khẩu hiệu trang chủ</h2>
                            <p>Thông điệp thu hút khách hàng hiển thị trên banner và khu vực khẩu hiệu chính.</p>
                        </div>
                    </div>

                    <div class="field-grid">
                        @include('admin.partials.field', ['name' => 'hero_kicker', 'label' => 'Lời mời chào trên banner (Kicker text)', 'max' => 150, 'wide' => true, 'placeholder' => 'Ví dụ: Quẩy hết mình cùng Tới Bến'])
                        @include('admin.partials.field', ['name' => 'hero_title', 'label' => 'Tiêu đề chữ lớn trên banner', 'multiline' => true, 'hint' => 'Nhấn Enter để xuống dòng. Hiển thị nổi bật trên giao diện điện thoại.', 'placeholder' => "Ăn hết mình\nsống tới bến"])
                        @include('admin.partials.field', ['name' => 'slogan_title', 'label' => 'Khẩu hiệu chính của nhà hàng', 'multiline' => true, 'hint' => 'Nhấn Enter để xuống dòng. Hiển thị lớn ngay bên dưới banner.', 'placeholder' => "NIỀM VUI LÀ LÝ DO\nTỚI BẾN LÀ ĐIỂM ĐẾN"])
                        @include('admin.partials.field', ['name' => 'slogan_description', 'label' => 'Đoạn mô tả ngắn dưới khẩu hiệu', 'max' => 1000, 'multiline' => true, 'wide' => true, 'placeholder' => 'Món nóng ra lò, không gian tụ họp thoải mái và ưu đãi luôn được cập nhật mỗi ngày.'])
                    </div>
                </section>

                <!-- Floating Save Bar Island -->
                <div class="save-bar">
                    <div class="save-bar-status">
                        <span class="save-pulse-dot"></span>
                        <span id="save-status" aria-live="polite">Tất cả thay đổi đã được đồng bộ.</span>
                    </div>

                    <div class="save-bar-actions">
                        <button class="button primary save-submit-btn" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Lưu cấu hình</span>
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
@endsection

@push('js')
    <script src="{{ asset('js/admin/website-settings.js') }}?v={{ filemtime(public_path('js/admin/website-settings.js')) }}" defer></script>
@endpush
