<!-- FOOTER START -->
<div class="footer">
    <div class="footer-content">
        <div class="footer-left">
            <ul class="content-left">
                <li>
                    <a href="{{ route('home') }}" class="logo">
                        <img src="{{ asset($website['logo']) }}" alt="{{ $website['site_name'] }}">
                    </a>
                </li>
                <li><button class="btn-booking" type="button" data-open-booking>ĐẶT BÀN</button></li>
            </ul>

            <ul class="list-nav">
                <li><a href="{{ route('menu') }}" class="link-nav">Thực đơn</a></li>
                <li><a href="{{ route('location') }}" class="link-nav">Cơ sở</a></li>
                <li><a href="{{ route('news') }}" class="link-nav">Ưu đãi</a></li>
                <li><a href="{{ route('contact') }}" class="link-nav">Liên hệ</a></li>
            </ul>
        </div>

        <div class="footer-right">
            <div class="info">
                <div class="info-type">
                    <div class="type">Hotline</div>
                    <a href="tel:{{ $website['hotline_link'] }}" class="type-text">{{ $website['hotline'] }}</a>
                </div>
                <div class="info-type">
                    <div class="type">Email</div>
                    <a href="mailto:{{ $website['email'] }}" class="type-text">{{ $website['email'] }}</a>
                </div>
            </div>

            <div class="credit">
                <div class="list-social">
                    @foreach(['facebook' => 'fb', 'tiktok' => 'tiktok', 'instagram' => 'ig', 'youtube' => 'yt'] as $network => $class)
                        @if($website[$network])
                            <a href="{{ $website[$network] }}" target="_blank" rel="noopener noreferrer nofollow" aria-label="{{ ucfirst($network) }}" class="social-link {{ $class }}"><span class="icon"></span></a>
                        @endif
                    @endforeach
                    @if($website['zalo'])<a href="{{ $website['zalo'] }}" target="_blank" rel="noopener noreferrer nofollow" class="social-link" aria-label="Zalo"><span style="font-weight:700;color:inherit">Zalo</span></a>@endif
                </div>
                <div class="cre">{{ $website['copyright'] }}</div>
            </div>
        </div>

        <a href="javascript:;" id="scrollButton" class="scrollTop">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="24" fill="#FFA827"></circle>
                <path d="M24 36V20" stroke="#333333" stroke-width="2" stroke-miterlimit="10"></path>
                <path d="M18 26L24 20L30 26" stroke="#333333" stroke-width="2" stroke-miterlimit="10" stroke-linecap="square"></path>
                <path d="M14 15H34" stroke="#333333" stroke-width="2" stroke-miterlimit="10" stroke-linecap="square"></path>
            </svg>
        </a>
    </div>
</div>
<!-- FOOTER END -->
