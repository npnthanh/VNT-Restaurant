@extends('layout.user')

@section('content')
    @push('css')
        <link rel="stylesheet" href="{{ asset('css/user/location-detail.css') }}">
    @endpush

    @php
        $resolveAsset = function (?string $path): string {
            if (!$path) {
                return asset('images/location/L12L04.jpg');
            }

            if (preg_match('/^https?:\/\//i', $path)) {
                return $path;
            }

            return asset(ltrim($path, '/'));
        };

        $sections = $detail->sections ?? collect();
        $heroImage = $resolveAsset($detail->cover_image ?? $location->thumbnail);
        $logoImage = $resolveAsset($detail->logo_image ?? $location->thumbnail);
        $menuImage = $detail->menu_image ? $resolveAsset($detail->menu_image) : null;

        $summary = trim((string) ($detail->summary ?? ''));
        $summary = $summary !== '' ? $summary : 'Khám phá không gian, hình ảnh và thông tin chi tiết của cơ sở ' . $location->name . '.';

        $introTitle = trim((string) ($detail->intro_title ?? ''));
        $introTitle = $introTitle !== '' ? $introTitle : 'Trải nghiệm tại ' . $location->name;

        $introContent = trim((string) ($detail->intro_content ?? ''));
        $introContent = $introContent !== ''
            ? $introContent
            : 'Thông tin chi tiết cho cơ sở này đang được cập nhật thêm. Bạn vẫn có thể xem giờ hoạt động, vị trí trên bản đồ và đặt bàn trước ngay từ trang này.';

        $closingTitle = trim((string) ($detail->closing_title ?? ''));
        $closingTitle = $closingTitle !== '' ? $closingTitle : 'Thông tin thêm';

        $address = trim((string) ($detail->address ?? ''));
        $address = $address !== '' ? $address : $location->name;

        $hotline = trim((string) ($detail->hotline ?? ''));
        $hotline = $hotline !== '' ? $hotline : '0961581328';

        $bookingNote = trim((string) ($detail->booking_note ?? ''));
        $parkingNote = trim((string) ($detail->parking_note ?? ''));
        $openNote = trim((string) ($detail->open_note ?? ''));
        $closingContent = trim((string) ($detail->closing_content ?? ''));

        $rating = $detail->rating !== null ? (float) $detail->rating : null;
        $filledStars = $rating !== null ? max(1, min(5, (int) round($rating))) : 0;
        $reviewCount = $detail->review_count;
        $contactLinks = [
            ['label' => 'Website', 'url' => $detail->website_url],
            ['label' => 'Facebook', 'url' => $detail->facebook_url],
            ['label' => 'TikTok', 'url' => $detail->tiktok_url],
        ];
        $availableLinks = array_filter($contactLinks, fn ($item) => !empty($item['url']));
    @endphp

    <section class="location-detail-page">
        <div class="location-detail-shell">
            <nav class="location-detail-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('location') }}">Cơ sở</a>
                <span>›</span>
                <span>{{ $location->name }}</span>
            </nav>

            <div class="location-detail-layout">
                <article class="location-detail-main">
                    <section class="location-hero-card">
                        <div class="location-hero-copy">
                            <span class="location-hero-kicker">{{ $location->region->name ?? 'Hệ thống nhà hàng' }}</span>

                            <div class="location-brand-row">
                                <div class="location-brand-logo">
                                    <img src="{{ $logoImage }}" alt="{{ $location->name }}">
                                </div>

                                <div class="location-brand-copy">
                                    <h1>{{ $location->name }}</h1>
                                    <p>{{ $summary }}</p>
                                </div>
                            </div>

                            <div class="location-hero-status">
                                <span class="location-status-badge {{ $location->status === 'active' ? 'is-open' : 'is-closed' }}">
                                    {{ $location->status === 'active' ? 'Đang mở cửa' : 'Tạm đóng cửa' }}
                                </span>
                                <span class="location-hours">Hoạt động từ {{ $location->formatted_time_start }} - {{ $location->formatted_time_end }}</span>
                            </div>

                            <div class="location-facts">
                                <div class="location-fact">
                                    <span>Sức chứa</span>
                                    <strong>{{ $location->capacity ?? '---' }} khách</strong>
                                </div>
                                <div class="location-fact">
                                    <span>Diện tích</span>
                                    <strong>{{ $location->area ? number_format($location->area, 0, ',', '.') . ' m²' : '---' }}</strong>
                                </div>
                                <div class="location-fact">
                                    <span>Số tầng</span>
                                    <strong>{{ $location->floors ?? '---' }} tầng</strong>
                                </div>
                            </div>

                            <div class="location-hero-actions">
                                <a href="{{ route('customer.booking', ['location_id' => $location->id]) }}" class="location-hero-btn is-primary">
                                    <i class="fa fa-book"></i>
                                    <span>Đặt bàn ngay</span>
                                </a>
                                <a href="{{ route('menu') }}" class="location-hero-btn">
                                    <i class="fa fa-utensils"></i>
                                    <span>Xem thực đơn</span>
                                </a>
                                @if(!empty($location->map_url))
                                    <a href="{{ $location->map_url }}" target="_blank" rel="noopener" class="location-hero-btn">
                                        <i class="fa fa-map-marker-alt"></i>
                                        <span>Xem bản đồ</span>
                                    </a>
                                @endif
                            </div>

                            <div class="location-hero-meta">
                                @if($rating !== null)
                                    <div class="location-rating">
                                        <div class="location-rating-stars" aria-label="Đánh giá {{ number_format($rating, 1) }}/5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fa {{ $i <= $filledStars ? 'fa-star' : 'fa-regular fa-star' }}"></i>
                                            @endfor
                                        </div>
                                        <span>{{ number_format($rating, 1) }}/5{{ $reviewCount ? ' • ' . number_format($reviewCount, 0, ',', '.') . ' đánh giá' : '' }}</span>
                                    </div>
                                @endif

                                <a href="tel:{{ preg_replace('/\s+/', '', $hotline) }}" class="location-meta-link">
                                    <i class="fa fa-phone"></i>
                                    <span>{{ $hotline }}</span>
                                </a>

                                <div class="location-meta-link">
                                    <i class="fa fa-location-dot"></i>
                                    <span>{{ $address }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="location-hero-media">
                            <img src="{{ $heroImage }}" alt="{{ $location->name }}">
                        </div>
                    </section>

                    <section class="location-story-card">
                        <div class="location-card-head">
                            <span class="location-card-label">Giới thiệu</span>
                            <h2>{{ $introTitle }}</h2>
                        </div>

                        <div class="location-story-body">{!! nl2br(e($introContent)) !!}</div>
                    </section>

                    @forelse($sections as $index => $section)
                        <section class="location-section-card">
                            <div class="location-card-head">
                                <span class="location-card-label">Không gian {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <h2>{{ $section->title }}</h2>
                            </div>

                            @if(!empty($section->content))
                                <div class="location-section-body">{!! nl2br(e($section->content)) !!}</div>
                            @endif

                            <figure class="location-section-media">
                                <img src="{{ $resolveAsset($section->image ?: $detail->cover_image ?: $location->thumbnail) }}" alt="{{ $section->title }}">
                            </figure>
                        </section>
                    @empty
                        <section class="location-section-card is-empty">
                            <div class="location-card-head">
                                <span class="location-card-label">Nội dung</span>
                                <h2>Chi tiết không gian đang được hoàn thiện</h2>
                            </div>

                            <div class="location-section-body">
                                Hệ thống nhiều block nội dung và nhiều ảnh đã sẵn sàng. Bạn chỉ cần thêm dữ liệu vào
                                `location_details` và `location_detail_sections` để hiển thị từng khu vực như tầng 1, tầng 2,
                                ban công hoặc phòng riêng.
                            </div>
                        </section>
                    @endforelse

                    @if($menuImage)
                        <section class="location-section-card">
                            <div class="location-card-head">
                                <span class="location-card-label">Thực đơn</span>
                                <h2>{{ $detail->menu_title ?: 'Thực đơn tham khảo tại cơ sở' }}</h2>
                            </div>

                            <figure class="location-section-media is-poster">
                                <img src="{{ $menuImage }}" alt="{{ $detail->menu_title ?: 'Thực đơn ' . $location->name }}">
                            </figure>
                        </section>
                    @endif

                    @if($closingContent !== '')
                        <section class="location-story-card">
                            <div class="location-card-head">
                                <span class="location-card-label">Ghi chú</span>
                                <h2>{{ $closingTitle }}</h2>
                            </div>

                            <div class="location-story-body">{!! nl2br(e($closingContent)) !!}</div>
                        </section>
                    @endif
                </article>

                <aside class="location-detail-sidebar">
                    <section class="location-sidebar-card">
                        <span class="location-card-label">Thông tin nhanh</span>
                        <h2>Liên hệ và tiện ích</h2>

                        <div class="location-sidebar-list">
                            <div class="location-sidebar-item">
                                <span>Địa chỉ</span>
                                <strong>{{ $address }}</strong>
                            </div>
                            <div class="location-sidebar-item">
                                <span>Hotline</span>
                                <strong>{{ $hotline }}</strong>
                            </div>
                            <div class="location-sidebar-item">
                                <span>Khung giờ phục vụ</span>
                                <strong>{{ $location->formatted_time_start }} - {{ $location->formatted_time_end }}</strong>
                            </div>
                        </div>
                    </section>

                    @if($bookingNote !== '' || $parkingNote !== '' || $openNote !== '')
                        <section class="location-sidebar-card">
                            <span class="location-card-label">Lưu ý</span>
                            <h2>Trước khi ghé quán</h2>

                            <div class="location-note-list">
                                @if($bookingNote !== '')
                                    <div class="location-note-item">
                                        <i class="fa fa-calendar-check"></i>
                                        <div>
                                            <strong>Đặt bàn</strong>
                                            <p>{{ $bookingNote }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($parkingNote !== '')
                                    <div class="location-note-item">
                                        <i class="fa fa-square-parking"></i>
                                        <div>
                                            <strong>Gửi xe</strong>
                                            <p>{{ $parkingNote }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($openNote !== '')
                                    <div class="location-note-item">
                                        <i class="fa fa-circle-info"></i>
                                        <div>
                                            <strong>Thông tin thêm</strong>
                                            <p>{{ $openNote }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endif

                    @if(!empty($availableLinks))
                        <section class="location-sidebar-card">
                            <span class="location-card-label">Kết nối</span>
                            <h2>Theo dõi cơ sở</h2>

                            <div class="location-link-list">
                                @foreach($availableLinks as $item)
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener">
                                        <span>{{ $item['label'] }}</span>
                                        <i class="fa fa-arrow-up-right-from-square"></i>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </aside>
            </div>
        </div>
    </section>
@endsection
