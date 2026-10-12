<div class="contact-info">
    @if($website['address'])<p><i class="fa fa-home"></i> {{ $website['address'] }}</p>@endif
    <p><i class="fa fa-envelope"></i> <a href="mailto:{{ $website['email'] }}" style="color:inherit;text-decoration:none">{{ $website['email'] }}</a></p>
    <p><i class="fa fa-phone"></i> <a href="tel:{{ $website['hotline_link'] }}" style="color:inherit;text-decoration:none">{{ $website['hotline'] }}</a></p>
</div>
<div class="contact-socials">
    @foreach(['facebook' => 'fb', 'tiktok' => 'tiktok', 'instagram' => 'ig', 'youtube' => 'yt'] as $network => $icon)
        @if($website[$network])
            <a href="{{ $website[$network] }}" target="_blank" rel="noopener noreferrer nofollow" aria-label="{{ ucfirst($network) }}"><img src="{{ asset("images/web_images/icn-{$icon}-contact.svg") }}" alt="{{ ucfirst($network) }}"></a>
        @endif
    @endforeach
    @if($website['zalo'])<a href="{{ $website['zalo'] }}" target="_blank" rel="noopener noreferrer nofollow" aria-label="Zalo">Zalo</a>@endif
</div>
