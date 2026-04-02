@extends('layout.user')

@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/user/news.css') }}">
    @endpush

    @php
        $formatDate = function ($date) {
            return $date ? $date->format('d, \\T\\H\\Á\\N\\G n, Y') : 'Đang cập nhật';
        };

        $resolveImageMeta = function ($path, $fallback = 'images/news/news4.png') {
            $relativePath = ltrim($path ?: $fallback, '/');
            $absolutePath = public_path($relativePath);
            $size = is_file($absolutePath) ? @getimagesize($absolutePath) : false;

            return [
                'src' => asset($relativePath),
                'width' => $size[0] ?? null,
                'height' => $size[1] ?? null,
                'ratio' => ($size && !empty($size[1])) ? ($size[0] . ' / ' . $size[1]) : '16 / 9',
            ];
        };

        $featuredImage = $featuredNews ? $resolveImageMeta($featuredNews->image) : null;
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
                            <div class="news-banner-media" style="--news-media-ratio: {{ $featuredImage['ratio'] }};">
                                <img class="big-banner"
                                     src="{{ $featuredImage['src'] }}"
                                     alt="{{ $featuredNews->title }}"
                                     @if($featuredImage['width']) width="{{ $featuredImage['width'] }}" @endif
                                     @if($featuredImage['height']) height="{{ $featuredImage['height'] }}" @endif
                                     loading="eager"
                                     decoding="async">
                            </div>
                            <div class="banner-text">
                                <span class="news-kicker">{{ $featuredNews->category }}</span>
                                <h2>{{ $featuredNews->title }}</h2>
                                <div class="banner-meta">
                                    <span class="news-date">{{ $formatDate($featuredNews->published_at) }}</span>
                                    @if(!empty($featuredNews->summary))
                                        <p>{{ \Illuminate\Support\Str::limit($featuredNews->summary, 180) }}</p>
                                    @endif
                                    <span class="banner-cta">
                                        <span class="icn">&rarr;</span>
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
                    @php($image = $resolveImageMeta($item->image))
                    <article class="news-item">
                        <a class="news-item-link" href="{{ route('news.show', $item) }}">
                            <div class="news-item-media" style="--news-card-ratio: {{ $image['ratio'] }};">
                                <img class="news-img"
                                     src="{{ $image['src'] }}"
                                     alt="{{ $item->title }}"
                                     @if($image['width']) width="{{ $image['width'] }}" @endif
                                     @if($image['height']) height="{{ $image['height'] }}" @endif
                                     loading="lazy"
                                     decoding="async">
                            </div>
                            <div class="news-content">
                                <span class="news-card-category">{{ $item->category }}</span>
                                <h3>{{ $item->title }}</h3>
                                <span class="news-card-date">{{ $formatDate($item->published_at) }}</span>
                                @if(!empty($item->summary))
                                    <p>{{ \Illuminate\Support\Str::limit($item->summary, 140) }}</p>
                                @endif
                                <span class="news-cta">
                                    <span class="icn">&rarr;</span>
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
