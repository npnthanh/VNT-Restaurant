@php
    $isEnabled = (bool) $banner['enabled'];
    $bannerDefault = \App\Services\WebsiteSettings::defaultBanner($banner['id'] ?? null);
@endphp
<div class="banner-panel {{ $isEnabled ? 'is-enabled' : 'is-disabled' }}" data-banner-panel="{{ $index }}">
    <input type="hidden" name="banners[{{ $index }}][id]" value="{{ $banner['id'] ?? '' }}">
    <div class="banner-heading">
        <div class="banner-title-group">
            <div class="banner-badge-num">{{ is_numeric($index) ? $index + 1 : '' }}</div>
            <div class="banner-title-texts">
                <h3 data-banner-title>Banner {{ is_numeric($index) ? $index + 1 : '' }}</h3>
                <span class="banner-status-tag {{ $isEnabled ? 'active' : 'inactive' }}">
                    <i class="fa-solid {{ $isEnabled ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                    <span class="status-text">{{ $isEnabled ? 'Đang bật hiển thị' : 'Đang tạm ẩn' }}</span>
                </span>
            </div>
        </div>
        <label class="switch-toggle-label">
            <input type="hidden" name="banners[{{ $index }}][enabled]" value="0">
            <input type="checkbox" name="banners[{{ $index }}][enabled]" value="1" class="banner-toggle-checkbox" @checked($isEnabled) aria-label="Hiển thị banner">
            <span class="toggle-slider"></span><span class="toggle-text-status">Hiển thị</span>
        </label>
    </div>
    <div class="banner-actions">
        <button type="button" class="banner-action" data-banner-up><i class="fa-solid fa-arrow-up" aria-hidden="true"></i> Lên</button>
        <button type="button" class="banner-action" data-banner-down><i class="fa-solid fa-arrow-down" aria-hidden="true"></i> Xuống</button>
        <button type="button" class="banner-action danger" data-banner-remove><i class="fa-solid fa-trash" aria-hidden="true"></i> Xóa banner</button>
    </div>
    <div class="banner-images-grid">
        @foreach(['desktop' => 'Ảnh máy tính (Desktop)', 'mobile' => 'Ảnh điện thoại (Mobile)'] as $device => $label)
            @php
                $imageId = "banner_{$index}_{$device}";
                $source = $device === 'mobile' ? ($banner['mobile'] ?: $banner['desktop']) : $banner['desktop'];
            @endphp
            <div class="image-upload" data-upload-group="{{ $imageId }}">
                <div class="upload-label-row"><label for="{{ $imageId }}" class="upload-label"><i class="fa-solid {{ $device === 'desktop' ? 'fa-display' : 'fa-mobile-screen' }}"></i> {{ $label }}</label></div>
                <div class="upload-dropzone">
                    <div class="image-preview">
                        <img id="preview-{{ $imageId }}" @if($source) src="{{ asset($source) }}" @else hidden @endif data-default-src="{{ !empty($bannerDefault[$device]) ? asset($bannerDefault[$device]) : '' }}" alt="{{ $label }}" loading="lazy">
                        <span class="preview-empty" @if($source) hidden @endif>Chưa chọn ảnh</span>
                        <div class="preview-overlay"><span class="overlay-text"><i class="fa-solid fa-camera"></i> Đổi ảnh mới</span></div>
                    </div>
                    <div class="upload-controls">
                        <div class="file-picker-wrapper">
                            <input id="{{ $imageId }}" type="file" name="banners[{{ $index }}][{{ $device }}]" accept="image/jpeg,image/png,image/webp" data-preview="preview-{{ $imageId }}" data-device="{{ $device }}" data-reset-name="banners[{{ $index }}][reset_{{ $device }}]" class="file-input-hidden" @if($device === 'desktop' && !$banner['desktop']) required @endif>
                            <label for="{{ $imageId }}" class="btn-select-file"><i class="fa-solid fa-cloud-arrow-up"></i><span>Tải tệp ảnh</span></label>
                            <span class="file-hint-text">JPG, PNG, WebP &le; 5MB</span>
                        </div>
                        @if($device === 'mobile' || $bannerDefault)
                            <label class="check-row reset-check-row"><input type="checkbox" name="banners[{{ $index }}][reset_{{ $device }}]" value="1" data-reset-file="{{ $imageId }}" @checked($banner['reset_'.$device] ?? false)><span class="custom-checkbox-label">{{ $device === 'mobile' ? 'Dùng ảnh máy tính' : 'Khôi phục ảnh gốc' }}</span></label>
                        @endif
                    </div>
                </div>
                @error("banners.{$index}.{$device}")<div class="field-error">{{ $message }}</div>@enderror
            </div>
        @endforeach
    </div>
    <div class="banner-caption-row field">
        <div class="field-label-row"><label for="banner_{{ $index }}_alt" class="field-label">Văn bản mô tả ảnh</label><span class="char-counter" data-for="banner_{{ $index }}_alt">{{ mb_strlen((string) ($banner['alt'] ?? '')) }}/150</span></div>
        <input id="banner_{{ $index }}_alt" name="banners[{{ $index }}][alt]" value="{{ $banner['alt'] ?? '' }}" maxlength="150" class="form-control input-control">
        @error("banners.{$index}.alt")<div class="field-error">{{ $message }}</div>@enderror
    </div>
    @error("banners.{$index}.id")<div class="field-error">{{ $message }}</div>@enderror
</div>
