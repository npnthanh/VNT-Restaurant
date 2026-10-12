<div class="image-upload {{ $name === 'logo' ? 'logo-upload' : ($name === 'favicon' ? 'favicon-upload' : '') }}" data-upload-group="{{ $name }}">
    <div class="upload-label-row">
        <label for="{{ $name }}" class="upload-label">
            <i class="fa-solid {{ $name === 'logo' ? 'fa-gem' : ($name === 'favicon' ? 'fa-window-maximize' : (str_contains($name, 'desktop') ? 'fa-display' : 'fa-mobile-screen')) }}"></i>
            {{ $label }}
        </label>
        <span class="upload-spec-badge">
            @if($name === 'logo')
                Logo trong suốt
            @elseif($name === 'favicon')
                Ảnh vuông
            @elseif(str_contains($name, 'desktop'))
                Tỉ lệ ngang 16:9
            @else
                Tỉ lệ dọc 9:16
            @endif
        </span>
    </div>

    <div class="upload-dropzone">
        <div class="image-preview" id="container-{{ $name }}">
            <img id="preview-{{ $name }}" src="{{ asset($settings[$name] ?: ($fallback ?? $settings['logo'])) }}" data-default-src="{{ asset(config('website.defaults.'.$name)) }}" alt="{{ $label }}" loading="lazy">
            <div class="preview-overlay">
                <span class="overlay-text"><i class="fa-solid fa-camera"></i> Đổi ảnh mới</span>
            </div>
        </div>

        <div class="upload-controls">
            <div class="file-picker-wrapper">
                <input id="{{ $name }}" type="file" name="{{ $name }}" accept="{{ $name === 'favicon' ? '.ico,image/x-icon,image/vnd.microsoft.icon,image/jpeg,image/png,image/webp' : 'image/jpeg,image/png,image/webp' }}" data-preview="preview-{{ $name }}" class="file-input-hidden">
                <label for="{{ $name }}" class="btn-select-file">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Tải tệp ảnh</span>
                </label>
                <span class="file-hint-text">{{ $name === 'favicon' ? 'ICO, PNG, JPG, WebP' : 'JPG, PNG, WebP' }} &le; 5MB</span>
            </div>

            <label class="check-row reset-check-row">
                <input type="checkbox" name="reset_{{ $name }}" value="1" @checked(old('reset_'.$name))>
                <span class="custom-checkbox-label">
                    <i class="fa-solid {{ str_ends_with($name, '_mobile') ? 'fa-arrows-rotate' : 'fa-rotate-left' }}"></i>
                    {{ str_ends_with($name, '_mobile') ? 'Dùng ảnh máy tính' : 'Khôi phục ảnh gốc' }}
                </span>
            </label>
        </div>
    </div>

    @if($name === 'favicon')
        <div class="field-hint">Biểu tượng trên tab trình duyệt. Nên dùng ảnh vuông 32 × 32 hoặc 64 × 64 pixel.</div>
    @endif

    @error($name)
        <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
    @enderror
</div>
