@extends('layout.user')

@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/user/news-detail.css') }}">
    @endpush

    @php
        $publishedDate = $news->published_at ? $news->published_at->format('d, \\T\\H\\Á\\N\\G n, Y') : 'Đang cập nhật';
        $bodyText = trim((string) $news->content);
    @endphp

    <section class="news-detail-page">
        <div class="news-detail-container">
            <article class="news-article">
                <nav class="news-breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('news') }}">TIN TỨC</a>
                    <span>›</span>
                    <span>{{ strtoupper($news->category) }}</span>
                </nav>

                <span class="news-detail-category">{{ $news->category }}</span>
                <h1 class="news-detail-title">{{ $news->title }}</h1>
                <time class="news-detail-date" datetime="{{ optional($news->published_at)->toDateString() }}">
                    {{ $publishedDate }}
                </time>

                <img class="news-detail-image"
                     src="{{ asset($news->image ?: 'images/news/news4.png') }}"
                     alt="{{ $news->title }}">

                @if(!empty($news->summary))
                    <p class="news-detail-summary">{{ $news->summary }}</p>
                @endif

                <div class="news-body">
                    @if($bodyText !== '')
                        <div class="news-body-text">{!! nl2br(e($bodyText)) !!}</div>
                    @else
                        <p class="news-body-empty">Bài viết này chưa có nội dung chi tiết.</p>
                    @endif
                </div>
            </article>

            <aside class="news-sidebar">
                @if($latestNews->isNotEmpty())
                    <section class="news-sidebar-block">
                        <h2>Tin mới lên</h2>
                        <div class="news-sidebar-list">
                            @foreach($latestNews as $item)
                                <a class="news-sidebar-item" href="{{ route('news.show', $item) }}">
                                    <div class="news-sidebar-copy">
                                        <span class="news-sidebar-label">{{ $item->category }}</span>
                                        <h3>{{ $item->title }}</h3>
                                    </div>
                                    <img src="{{ asset($item->image ?: 'images/news/news4.png') }}" alt="{{ $item->title }}">
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($sidebarBanners->isNotEmpty())
                    <section class="news-sidebar-posters">
                        @foreach($sidebarBanners as $item)
                            <a class="news-sidebar-poster" href="{{ route('news.show', $item) }}">
                                <img src="{{ asset($item->image ?: 'images/news/news4.png') }}" alt="{{ $item->title }}">
                            </a>
                        @endforeach
                    </section>
                @endif
            </aside>
        </div>
    </section>
@endsection
