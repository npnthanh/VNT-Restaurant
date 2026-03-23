@extends('layout.user')

@section('content')
    <div id="preloader">
        <div class="loader-content">
            <div class="loader-text">Tới Bến Quán</div>
            <div class="progress-bar">
                <div class="progress"></div>
            </div>
            <div class="percent">0%</div>
        </div>
    </div>

    @push('css')
        <link rel="stylesheet" href="{{ asset('css/user/home.css') }}">
    @endpush

    <section class="slider home-hero">
        <div class="slides">
            <div class="slide active" style="background-image: url('{{ asset('images/banner/banner1.png') }}');"></div>
            <div class="slide" style="background-image: url('{{ asset('images/banner/banner2.png') }}');"></div>
            <div class="slide" style="background-image: url('{{ asset('images/banner/banner3.png') }}');"></div>
        </div>
        <div class="home-hero-copy">
            <span class="home-kicker">Quây hết mình cùng Tới Bến</span>
            <h1 class="home-hero-title">
                Niềm vui là lý do<br>
                Tới Bến là điểm đến
            </h1>
            <div class="home-hero-actions">
                <a href="{{ route('menu') }}" class="menu hero-menu-btn">Xem thực đơn</a>
                <button class="btn-booking home-booking-btn" type="button" data-open-booking>Đặt bàn</button>
            </div>
        </div>
        <button class="prev">&#10094;</button>
        <button class="next">&#10095;</button>
    </section>

    <section class="slogan-section">
        <h2 class="slogan-text">
            NIỀM VUI LÀ LÝ DO <br>
            TỚI BẾN LÀ ĐIỂM ĐẾN
        </h2>
        <p class="slogan-subtext">
            Món nóng ra lò, không gian tụ họp thoải mái và ưu đãi luôn được cập nhật mỗi ngày.
        </p>
    </section>

    <div class="home-desktop-cta">
        <a href="{{ route('menu') }}" class="menu">Xem thá»±c Ä‘Æ¡n</a>
    </div>

    <section class="news-section">
        <h2 class="news-title">Ưu đãi nổi bật</h2>
        <div class="news-container">
            <div class="news-card">
                <img src="{{ asset('images/news/news4.png') }}" alt="Sinh nhật độc nhất" class="news-img">
                <div class="news-content">
                    <h3>🎉 NGÀY CỦA BẠN, TỚI BẾN CÙNG BẠN</h3>
                    <p>Sinh nhật không chỉ là một bữa tiệc, mà còn là dịp để tụ họp, ăn ngon và vui trọn khoảnh khắc.</p>
                    <button class="news-btn">XEM NGAY</button>
                </div>
            </div>

            <div class="news-card">
                <img src="{{ asset('images/news/news2.png') }}" alt="Combo Tới Bến" class="news-img">
                <div class="news-content">
                    <h3>🔥 COMBO RA MẮT, GÓI TRỌN NIỀM VUI</h3>
                    <p>Những set món được chọn sẵn cho nhóm bạn, vừa đủ no, vừa đủ vui, lên bàn là nhập tiệc ngay.</p>
                    <button class="news-btn">XEM NGAY</button>
                </div>
            </div>

            <div class="news-card">
                <img src="{{ asset('images/news/news3.png') }}" alt="Bộ đôi trà đậm vị" class="news-img">
                <div class="news-content">
                    <h3>🥂 THÊM MÓN HAY, THÊM LÝ DO ĐỂ TỚI</h3>
                    <p>Mỗi tuần một gợi ý mới cho buổi tụ tập, giúp thực đơn của nhóm bạn luôn có cảm giác mới mẻ.</p>
                    <button class="news-btn">XEM NGAY</button>
                </div>
            </div>
        </div>
        <div class="news-viewall">
            <a href="{{ route('news') }}">XEM TẤT CẢ</a>
        </div>
    </section>
@endsection

@push('js')
    <script src="{{ asset('js/user/home.js') }}"></script>
    <script src="{{ asset('js/user/load.js') }}"></script>
@endpush
