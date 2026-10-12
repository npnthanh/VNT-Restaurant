@php
    $iconMap = [
        'site_name' => 'fa-solid fa-store',
        'hotline' => 'fa-solid fa-phone-volume',
        'email' => 'fa-solid fa-envelope',
        'address' => 'fa-solid fa-location-dot',
        'copyright' => 'fa-solid fa-copyright',
        'facebook' => 'fa-brands fa-facebook-f',
        'tiktok' => 'fa-brands fa-tiktok',
        'instagram' => 'fa-brands fa-instagram',
        'youtube' => 'fa-brands fa-youtube',
        'zalo' => 'fa-solid fa-comment-dots',
        'hero_kicker' => 'fa-solid fa-sparkles',
        'hero_title' => 'fa-solid fa-heading',
        'slogan_title' => 'fa-solid fa-quote-left',
        'slogan_description' => 'fa-solid fa-align-left',
    ];
    $icon = $iconMap[$name] ?? (str_contains($name, '_alt') ? 'fa-solid fa-closed-captioning' : null);
@endphp

<div class="field {{ $wide ?? false ? 'wide' : '' }} field-{{ $name }}">
    <div class="field-label-row">
        <label for="{{ $name }}" class="field-label">
            @if($icon)<i class="{{ $icon }} field-icon-label"></i>@endif
            <span>{{ $label }}</span>
            @if($required ?? false)<span class="required" title="Bắt buộc">*</span>@endif
        </label>
        @if(isset($max))
            <span class="char-counter" data-for="{{ $name }}">{{ mb_strlen((string) old($name, $settings[$name] ?? '')) }}/{{ $max }}</span>
        @endif
    </div>

    <div class="input-wrapper {{ $icon ? 'has-icon' : '' }}">
        @if($icon && !($multiline ?? false))
            <span class="input-icon-pill"><i class="{{ $icon }}"></i></span>
        @endif

        @if($multiline ?? false)
            <textarea id="{{ $name }}"
                      name="{{ $name }}"
                      rows="{{ $rows ?? 3 }}"
                      maxlength="{{ $max ?? 200 }}"
                      @if($required ?? false) required @endif
                      @if(isset($placeholder)) placeholder="{{ $placeholder }}" @endif
                      @error($name) aria-invalid="true" aria-describedby="error-{{ $name }}" @enderror
                      class="form-control textarea-control">{{ old($name, $settings[$name] ?? '') }}</textarea>
        @else
            <input id="{{ $name }}"
                   name="{{ $name }}"
                   type="{{ $type ?? 'text' }}"
                   value="{{ old($name, $settings[$name] ?? '') }}"
                   maxlength="{{ $max ?? 200 }}"
                   @if($required ?? false) required @endif
                   @if(isset($placeholder)) placeholder="{{ $placeholder }}" @endif
                   @error($name) aria-invalid="true" aria-describedby="error-{{ $name }}" @enderror
                   class="form-control input-control">
        @endif
    </div>

    @if(isset($hint))
        <div class="field-hint"><i class="fa-regular fa-circle-question"></i> {{ $hint }}</div>
    @endif

    @error($name)
        <div class="field-error" id="error-{{ $name }}"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
    @enderror
</div>
