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
                    <h1>ƯU ĐÃI</h1>
                    <p>
                        Nơi cập nhật nhanh nhất những sự kiện nóng hổi, chương trình khuyến mại,
                        <br>
                        khách hàng và thông tin thương hiệu.
                    </p>
                </div>
            </div>
        </section>

        <!-- Danh mục -->
        <div class="menu-scroll-wrapper">
            <div class="fade-zone left"></div>
            <div class="menu-scroll" id="menuScroll">
                <a href="#featured" class="active">Ưu đãi mới</a>
                <a href="#all-promotions">Tất cả ưu đãi</a>
            </div>
            <div class="fade-zone right"></div>
        </div>

        <div class="container">
            @if($promotions->isNotEmpty())
                @php
                    $featuredPromotion = $promotions->first();
                @endphp
                <div class="news-banner" id="featured">
                    <div class="news-banner-content">
                        <img class="big-banner"
                             src="{{ asset($featuredPromotion->images ?: 'images/news/news4.png') }}"
                             alt="{{ $featuredPromotion->name }}" />
                        <div class="banner-text">
                            <h2>{{ $featuredPromotion->name }}</h2>
                            <div class="banner-meta">
                                @if(!empty($featuredPromotion->description))
                                    <p>{{ $featuredPromotion->description }}</p>
                                @endif
                                <a class="banner-cta" href="#all-promotions">
                                    <span class="icn">→</span>
                                    <span class="txt">XEM NGAY</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="news-banner" id="featured">
                    <div class="news-banner-content">
                        <img class="big-banner" src="{{ asset('images/news/news4.png') }}" alt="Ưu đãi" />
                        <div class="banner-text">
                            <h2>Ưu đãi đang được cập nhật</h2>
                            <div class="banner-meta">
                                <p>Hiện chưa có chương trình khuyến mãi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @php
                $otherPromotions = $promotions->skip(1);
            @endphp
            <div class="news-grid" id="all-promotions">
                @forelse($otherPromotions as $promotion)
                    <div class="news-item" id="promotion-{{ $promotion->id }}">
                        <a href="#promotion-{{ $promotion->id }}">
                            <img class="news-img"
                                 src="{{ asset($promotion->images ?: 'images/news/news4.png') }}"
                                 alt="{{ $promotion->name }}">
                            <div class="news-content">
                                <h3>{{ $promotion->name }}</h3>
                                @if(!empty($promotion->description))
                                    <p>{{ \Illuminate\Support\Str::limit($promotion->description, 120) }}</p>
                                @endif
                                <a class="news-cta" href="#promotion-{{ $promotion->id }}">
                                    <span class="icn">→</span>
                                    <span class="txt">XEM NGAY</span>
                                </a>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="news-item">
                        <div class="news-content">
                            <h3>{{ $promotions->isEmpty() ? 'Chưa có ưu đãi' : 'Chưa có ưu đãi khác' }}</h3>
                            <p>Vui lòng quay lại sau để cập nhật chương trình mới.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
    <!-- CONTENT START -->
@endsection

@push('js')
    <script src="{{ asset('js/user/news.js') }}"></script>
@endpush
