@extends('layout.user')

@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/user/news.css') }}">
    @endpush
    <!-- CONTENT START -->
    <main class="menu-page">
        <!-- Banner -->
        <section class="menu-banner">
            <div class="menu-banner-container">
                <div class="menu-banner-text">
                    <h1>🍽 Website Quản Lý Nhà Hàng</h1>
                    <p>
                        Vai trò: Full Stack Web Developer
                        <br>
                        Công nghệ: Laravel, PHP, MySQL, HTML, CSS, JavaScript
                        <br>
                        Phát triển hệ thống quản lý nhà hàng hoàn chỉnh gồm website người dùng và trang quản trị.
                    </p>
                </div>
            </div>
        </section>

        <!-- Danh mục -->
        <div class="menu-scroll-wrapper">
            <div class="fade-zone left"></div>
            <div class="menu-scroll" id="menuScroll">
                <a href="#overview" class="active">Tổng quan</a>
                <a href="#customer-site">Website khách hàng</a>
                <a href="#admin-system">Hệ thống quản trị</a>
                <a href="#architecture">CSDL & MVC</a>
            </div>
            <div class="fade-zone right"></div>
        </div>
        <div class="container">
            <div class="news-banner" id="overview">
                <div class="news-banner-content">
                    <img class="big-banner" src="{{ asset('images/news/news4.png') }}" alt="Website Quản Lý Nhà Hàng" />
                    <div class="banner-text">
                        <h2>Website Quản Lý Nhà Hàng</h2>
                        <div class="banner-meta">
                            <p><span class="label">Vai trò:</span> Full Stack Web Developer</p>
                            <p><span class="label">Công nghệ:</span> Laravel, PHP, MySQL, HTML, CSS, JavaScript</p>
                            <p>Phát triển hệ thống quản lý nhà hàng hoàn chỉnh gồm website người dùng và trang quản trị.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="news-grid">
                <div class="news-item" id="customer-site">
                    <div class="news-content">
                        <h3>Website khách hàng</h3>
                        <ul class="news-list">
                            <li>Xem danh sách cơ sở</li>
                            <li>Xem thực đơn</li>
                            <li>Đặt bàn trực tuyến</li>
                        </ul>
                    </div>
                </div>

                <div class="news-item" id="admin-system">
                    <div class="news-content">
                        <h3>Hệ thống quản trị</h3>
                        <ul class="news-list">
                            <li>Quản lý khu vực, phòng và bàn</li>
                            <li>Quản lý món ăn và nguyên liệu</li>
                            <li>Quản lý khách hàng và nhân viên</li>
                            <li>Quản lý lịch làm việc và chấm công</li>
                            <li>Tính bảng lương nhân viên</li>
                            <li>Quản lý nhập – xuất kho</li>
                            <li>Trang thu ngân (Cashier/POS) bán hàng trực tiếp</li>
                        </ul>
                    </div>
                </div>

                <div class="news-item" id="architecture">
                    <div class="news-content">
                        <h3>CSDL & Kiến trúc</h3>
                        <ul class="news-list">
                            <li>Thiết kế cơ sở dữ liệu quan hệ và xây dựng đầy đủ chức năng CRUD.</li>
                            <li>Áp dụng mô hình MVC trong Laravel.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- CONTENT START -->
@endsection

@push('js')
    <script src="{{ asset('js/user/news.js') }}"></script>
@endpush
