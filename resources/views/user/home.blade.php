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
            <div class="slide active" style="background-image: url('{{ asset('images/banner/Banner1.png') }}');"></div>
            <div class="slide" style="background-image: url('{{ asset('images/banner/Banner2.png') }}');"></div>
            <div class="slide" style="background-image: url('{{ asset('images/banner/Banner3.png') }}');"></div>
        </div>
        <div class="home-hero-copy">
            <span class="home-kicker">Quẩy hết mình cùng Tới Bến</span>
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
        <a href="{{ route('menu') }}" class="menu">Xem thực đơn</a>
    </div>

    <section class="news-section">
        <h2 class="news-title">Ưu đãi nổi bật</h2>
        <div class="news-container">
            @forelse($homeNews as $item)
                @php
                    $summary = trim((string) ($item->summary ?: strip_tags((string) $item->content)));
                @endphp
                <article class="news-card">
                    <div class="news-media">
                        <img
                            src="{{ asset($item->image ?: 'images/news/news4.png') }}"
                            alt="{{ $item->title }}"
                            class="news-img"
                            loading="lazy"
                            decoding="async">
                    </div>
                    <div class="news-content">
                        <h3>{{ $item->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($summary, 140) }}</p>
                        <a href="{{ route('news.show', $item) }}" class="news-btn">XEM NGAY</a>
                    </div>
                </article>
            @empty
                <article class="news-card news-card-empty">
                    <div class="news-content">
                        <h3>Ưu đãi đang được cập nhật</h3>
                        <p>Chưa có bài viết nào được xuất bản. Vui lòng quay lại sau để xem các ưu đãi mới nhất.</p>
                        <a href="{{ route('news') }}" class="news-btn">XEM TẤT CẢ</a>
                    </div>
                </article>
            @endforelse
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
