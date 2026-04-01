@extends('layout.user')

@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/user/news.css') }}">
    @endpush

    @php
        $formatDate = function ($date) {
            return $date ? $date->format('d, \\T\\H\\Á\\N\\G n, Y') : 'Đang cập nhật';
        };
    @endphp

    <section class="menu-page news-page">
        <section class="menu-banner">
            <div class="menu-banner-container">
                <div class="menu-banner-text">
                    <h1>TIN TỨC</h1>
                    <p>
                        Cập nhật ưu đãi, sự kiện và những câu chuyện mới nhất từ Tới Bến.
                    </p>
                </div>
            </div>
        </section>

        <div class="menu-scroll-wrapper">
            <div class="fade-zone left"></div>
            <div class="menu-scroll" id="menuScroll">
                <a href="#featured" class="active">Tin nổi bật</a>
                <a href="#all-news">Tất cả tin tức</a>
            </div>
            <div class="fade-zone right"></div>
        </div>

        <div class="container">
            @if($featuredNews)
                <section class="news-banner" id="featured">
                    <a class="news-banner-link" href="{{ route('news.show', $featuredNews) }}">
                        <div class="news-banner-content">
                            <img class="big-banner"
                                 src="{{ asset($featuredNews->image ?: 'images/news/news4.png') }}"
                                 alt="{{ $featuredNews->title }}">
                            <div class="banner-text">
                                <span class="news-kicker">{{ $featuredNews->category }}</span>
                                <h2>{{ $featuredNews->title }}</h2>
                                <div class="banner-meta">
                                    <span class="news-date">{{ $formatDate($featuredNews->published_at) }}</span>
                                    @if(!empty($featuredNews->summary))
                                        <p>{{ \Illuminate\Support\Str::limit($featuredNews->summary, 180) }}</p>
                                    @endif
                                    <span class="banner-cta">
                                        <span class="icn">→</span>
                                        <span class="txt">XEM NGAY</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </section>
            @else
                <section class="news-empty" id="featured">
                    <span class="news-empty-kicker">TIN TỨC</span>
                    <h2>Chưa có bài viết nào được xuất bản</h2>
                    <p>Bạn có thể thêm dữ liệu vào bảng <code>news</code> để hiển thị tại đây.</p>
                </section>
            @endif

            <section class="news-grid" id="all-news">
                @forelse($otherNews as $item)
                    <article class="news-item">
                        <a class="news-item-link" href="{{ route('news.show', $item) }}">
                            <img class="news-img"
                                 src="{{ asset($item->image ?: 'images/news/news4.png') }}"
                                 alt="{{ $item->title }}">
                            <div class="news-content">
                                <span class="news-card-category">{{ $item->category }}</span>
                                <h3>{{ $item->title }}</h3>
                                <span class="news-card-date">{{ $formatDate($item->published_at) }}</span>
                                @if(!empty($item->summary))
                                    <p>{{ \Illuminate\Support\Str::limit($item->summary, 140) }}</p>
                                @endif
                                <span class="news-cta">
                                    <span class="icn">→</span>
                                    <span class="txt">XEM NGAY</span>
                                </span>
                            </div>
                        </a>
                    </article>
                @empty
                    @if(!$featuredNews)
                        <article class="news-item news-item-empty">
                            <div class="news-content">
                                <h3>Danh sách bài viết đang trống</h3>
                                <p>Triển khai migration rồi thêm bản ghi published vào bảng <code>news</code> để trang này hoạt động.</p>
                            </div>
                        </article>
                    @endif
                @endforelse
            </section>
        </div>
    </section>
@endsection

@push('js')
    <script src="{{ asset('js/user/news.js') }}"></script>
@endpush
