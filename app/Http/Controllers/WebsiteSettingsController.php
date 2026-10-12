<?php

namespace App\Http\Controllers;

use App\Models\WebsiteSetting;
use App\Services\WebsiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WebsiteSettingsController extends Controller
{
    public function edit(Request $request, WebsiteSettings $settings)
    {
        $values = $settings->all();
        $existing = collect($values['banners'])->keyBy('id');
        $rows = $request->old('banners', $values['banners']);
        $banners = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!is_array($row)) continue;
                $base = $existing->get($row['id'] ?? '') ?? ['id' => null, 'desktop' => null, 'mobile' => null, 'enabled' => true, 'alt' => ''];
                $banners[] = array_replace($base, Arr::only($row, ['id', 'enabled', 'alt', 'reset_desktop', 'reset_mobile']));
            }
        }

        return response()->view('admin.website-settings', ['settings' => $values, 'banners' => $banners])
            ->header('Cache-Control', 'no-store');
    }

    public function update(Request $request, WebsiteSettings $settings)
    {
        $imageRule = 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=8000,max_height=8000';
        $rules = [
            'site_name' => 'required|string|max:100',
            'hotline' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]{4,28}$/'],
            'email' => 'required|email|max:254',
            'address' => 'nullable|string|max:500',
            'copyright' => 'nullable|string|max:200',
            'hero_kicker' => 'nullable|string|max:150',
            'hero_title' => 'nullable|string|max:200',
            'slogan_title' => 'nullable|string|max:200',
            'slogan_description' => 'nullable|string|max:1000',
            'logo' => $imageRule,
            'reset_logo' => 'nullable|boolean',
            'favicon' => 'nullable|file|mimes:png,jpg,jpeg,webp,ico|max:5120|dimensions:max_width=1024,max_height=1024',
            'reset_favicon' => 'nullable|boolean',
            'banners' => 'required|array|min:1',
            'banners.*' => 'array:id,enabled,alt,desktop,mobile,reset_desktop,reset_mobile',
            'banners.*.id' => 'nullable|string|max:64|distinct',
            'banners.*.enabled' => 'required|boolean',
            'banners.*.alt' => 'nullable|string|max:150',
            'banners.*.desktop' => $imageRule,
            'banners.*.mobile' => $imageRule,
            'banners.*.reset_desktop' => 'nullable|boolean',
            'banners.*.reset_mobile' => 'nullable|boolean',
        ];
        $attributes = [
            'site_name' => 'tên website', 'hotline' => 'hotline', 'email' => 'email',
            'address' => 'địa chỉ', 'copyright' => 'bản quyền', 'hero_kicker' => 'lời giới thiệu',
            'hero_title' => 'tiêu đề banner', 'slogan_title' => 'khẩu hiệu',
            'slogan_description' => 'mô tả trang chủ', 'logo' => 'logo', 'favicon' => 'favicon',
            'banners' => 'danh sách banner', 'banners.*.desktop' => 'ảnh máy tính của banner',
            'banners.*.mobile' => 'ảnh điện thoại của banner', 'banners.*.alt' => 'mô tả banner',
        ];
        foreach (['facebook', 'tiktok', 'instagram', 'youtube', 'zalo'] as $social) {
            $rules[$social] = ['nullable', 'url', 'max:500', 'regex:/^https?:\/\//i'];
            $attributes[$social] = 'liên kết '.ucfirst($social);
        }
        $validated = $request->validate($rules, [
            'required' => 'Vui lòng nhập :attribute.',
            'email' => ':attribute không đúng định dạng.',
            'url' => ':attribute phải là URL đầy đủ.',
            'regex' => ':attribute không đúng định dạng (liên kết cần bắt đầu bằng https:// hoặc http://).',
            'image' => ':attribute phải là hình ảnh.',
            'mimes' => ':attribute chỉ hỗ trợ JPG, PNG hoặc WebP.',
            'max.file' => ':attribute không được vượt quá 5 MB.',
            'max.string' => ':attribute quá dài (tối đa :max ký tự).',
            'dimensions' => ':attribute không được vượt quá 8000 × 8000 pixel.',
            'favicon.mimes' => 'Favicon chỉ hỗ trợ ICO, PNG, JPG hoặc WebP.',
            'favicon.dimensions' => 'Favicon phải là ảnh hợp lệ, không vượt quá 1024 × 1024 pixel.',
            'banners.min' => 'Vui lòng giữ ít nhất một banner.',
            'distinct' => 'Mỗi banner chỉ được xuất hiện một lần.',
        ], $attributes);

        if (!collect($validated['banners'])->contains(fn ($row) => (bool) $row['enabled'])) {
            throw ValidationException::withMessages(['banners' => 'Vui lòng bật ít nhất một banner trang chủ.']);
        }

        $uploaded = [];
        $obsolete = [];
        $disk = Storage::disk('website');
        $storeImage = function ($file) use (&$uploaded) {
            $path = $file->store('', 'website');
            $uploaded[] = $path;
            return 'uploads/website/'.$path;
        };
        try {
            DB::transaction(function () use ($request, $validated, $storeImage, &$obsolete) {
                $record = WebsiteSetting::whereKey(1)->lockForUpdate()->firstOrFail();
                $previous = WebsiteSettings::withDefaults($record->data ?? []);
                $data = array_replace($previous, Arr::except($validated, ['logo', 'reset_logo', 'favicon', 'reset_favicon', 'banners']));
                $existing = collect($previous['banners'])->keyBy('id');
                $data['banners'] = [];
                foreach ($validated['banners'] as $index => $row) {
                    $id = $row['id'] ?? null;
                    if ($id && !$existing->has($id)) {
                        throw ValidationException::withMessages(["banners.{$index}.id" => 'Banner này không còn tồn tại. Vui lòng tải lại trang cấu hình.']);
                    }
                    $banner = $id ? $existing->get($id) : ['id' => (string) Str::uuid(), 'desktop' => null, 'mobile' => null];
                    $default = WebsiteSettings::defaultBanner($id);
                    if ($request->boolean("banners.{$index}.reset_desktop")) {
                        if (!$default) {
                            throw ValidationException::withMessages(["banners.{$index}.desktop" => 'Banner tự thêm không có ảnh mặc định. Vui lòng tải ảnh mới.']);
                        }
                        $banner['desktop'] = $default['desktop'];
                        $banner['mobile'] = $default['mobile'];
                    }
                    if ($request->hasFile("banners.{$index}.desktop")) {
                        $banner['desktop'] = $storeImage($request->file("banners.{$index}.desktop"));
                        $banner['mobile'] = null;
                    }
                    if ($request->boolean("banners.{$index}.reset_mobile")) $banner['mobile'] = null;
                    if ($request->hasFile("banners.{$index}.mobile")) {
                        $banner['mobile'] = $storeImage($request->file("banners.{$index}.mobile"));
                    }
                    if (!$banner['desktop']) {
                        throw ValidationException::withMessages(["banners.{$index}.desktop" => 'Vui lòng tải ảnh máy tính cho banner mới.']);
                    }
                    $banner['enabled'] = (bool) $row['enabled'];
                    $banner['alt'] = $row['alt'] ?? '';
                    $data['banners'][] = $banner;
                }
                if ($request->boolean('reset_logo')) $data['logo'] = config('website.defaults.logo');
                if ($request->hasFile('logo')) $data['logo'] = $storeImage($request->file('logo'));
                if ($request->boolean('reset_favicon')) $data['favicon'] = config('website.defaults.favicon');
                if ($request->hasFile('favicon')) $data['favicon'] = $storeImage($request->file('favicon'));
                $record->update(['data' => $data]);
                $paths = fn ($values) => array_filter(array_merge([$values['logo'], $values['favicon']], collect($values['banners'])->flatMap(fn ($banner) => [$banner['desktop'], $banner['mobile']])->all()));
                $obsolete = array_diff($paths($previous), $paths($data));
            });
        } catch (\Throwable $exception) {
            $disk->delete($uploaded);
            throw $exception;
        }

        foreach ($obsolete as $path) {
            if (str_starts_with($path, 'uploads/website/')) $disk->delete(substr($path, strlen('uploads/website/')));
        }
        $settings->forget();

        return redirect()->route('admin.website-settings.edit')->with('success', 'Đã lưu cấu hình. Trang khách đã được cập nhật.');
    }
}
