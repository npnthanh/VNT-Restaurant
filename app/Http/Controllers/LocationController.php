<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationDetail;
use App\Models\LocationDetailSection;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index()
    {
        $regions = Region::orderBy('name')->get();
        $locations = Location::with('region')->orderBy('id')->get();

        return view('pos.location', compact('regions', 'locations'));
    }

    public function show($id)
    {
        $location = Location::with(['region', 'detail.sections'])->findOrFail($id);

        if (!$location->detail) {
            $location->setRelation(
                'detail',
                LocationDetail::create(['location_id' => $location->id])->load('sections')
            );
        }

        return response()->json([
            'success' => true,
            'data'    => $location
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $detailPayload = $this->validateDetailPayload($request);

        $location = DB::transaction(function () use ($data, $detailPayload, $request) {
            $location = Location::create($data);
            $this->syncLocationDetail($location, $detailPayload, $request);

            return $location;
        });

        return response()->json([
            'success' => true,
            'data'    => $location->load(['region', 'detail.sections'])
        ]);
    }

    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);
        $data = $this->validatePayload($request, $location->id);
        $detailPayload = $this->validateDetailPayload($request);

        DB::transaction(function () use ($location, $data, $detailPayload, $request) {
            $location->update($data);
            $this->syncLocationDetail($location, $detailPayload, $request);
        });

        return response()->json([
            'success' => true,
            'data' => $location->fresh(['region', 'detail.sections']),
        ]);
    }

    public function toggleStatus($id)
    {
        $location = Location::findOrFail($id);
        $location->status = $location->status === 'active' ? 'inactive' : 'active';
        $location->save();

        return response()->json([
            'success' => true,
            'status'  => $location->status
        ]);
    }

    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return response()->json(['success' => true]);
    }

    protected function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'time_start' => $this->normalizeTimeValue($request->input('time_start')),
            'time_end' => $this->normalizeTimeValue($request->input('time_end')),
        ]);

        $rules = [
            'region_id' => 'required|exists:regions,id',
            'code'      => 'required|string|max:150',
            'name'      => 'required|string|max:150',
            'slug'      => [
                'nullable',
                'string',
                'max:180',
                Rule::unique('location', 'slug')->ignore($ignoreId),
            ],
            'capacity'  => 'nullable|integer|min:0',
            'area'      => 'nullable|numeric|min:0',
            'floors'    => 'nullable|integer|min:0',
            'time_start'=> 'nullable|date_format:H:i',
            'time_end'  => 'nullable|date_format:H:i',
            'map_url'   => 'nullable|string|max:500',
            'status'    => 'required|in:active,inactive',
        ];

        if ($request->hasFile('thumbnail')) {
            $rules['thumbnail'] = 'nullable|image|max:2048';
        } else {
            $rules['thumbnail'] = 'nullable|string|max:255';
        }

        $data = $request->validate($rules);
        $data['slug'] = $this->generateUniqueSlug(
            $data['slug'] ?? $data['name'] ?? $data['code'],
            $ignoreId
        );

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/location'), $filename);
            $data['thumbnail'] = 'images/location/' . $filename;
        } elseif (array_key_exists('thumbnail', $data) && $data['thumbnail'] === '') {
            $data['thumbnail'] = null;
        }

        if (array_key_exists('map_url', $data) && $data['map_url'] === '') {
            $data['map_url'] = null;
        }

        return $data;
    }

    protected function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($source);
        if ($baseSlug === '') {
            $baseSlug = 'co-so';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (
            Location::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function validateDetailPayload(Request $request): array
    {
        $validated = $request->validate([
            'detail' => 'nullable|array',
            'detail.logo_image' => 'nullable|string|max:255',
            'detail.cover_image' => 'nullable|string|max:255',
            'detail.summary' => 'nullable|string',
            'detail.intro_title' => 'nullable|string|max:255',
            'detail.intro_content' => 'nullable|string',
            'detail.menu_title' => 'nullable|string|max:255',
            'detail.menu_image' => 'nullable|string|max:255',
            'detail.closing_title' => 'nullable|string|max:255',
            'detail.closing_content' => 'nullable|string',
            'detail.address' => 'nullable|string|max:255',
            'detail.hotline' => 'nullable|string|max:50',
            'detail.rating' => 'nullable|numeric|min:0|max:5',
            'detail.review_count' => 'nullable|integer|min:0',
            'detail.website_url' => 'nullable|string|max:500',
            'detail.facebook_url' => 'nullable|string|max:500',
            'detail.tiktok_url' => 'nullable|string|max:500',
            'detail.booking_note' => 'nullable|string',
            'detail.parking_note' => 'nullable|string',
            'detail.open_note' => 'nullable|string',
            'detail_logo_image_file' => 'nullable|image|max:4096',
            'detail_cover_image_file' => 'nullable|image|max:4096',
            'detail_menu_image_file' => 'nullable|image|max:4096',
            'sections' => 'nullable|array',
            'sections.*.title' => 'nullable|string|max:255',
            'sections.*.content' => 'nullable|string',
            'sections.*.image' => 'nullable|string|max:255',
            'sections.*.sort_order' => 'nullable|integer|min:0',
            'section_image_files.*' => 'nullable|image|max:4096',
        ]);

        return [
            'detail' => $validated['detail'] ?? [],
            'sections' => $validated['sections'] ?? [],
        ];
    }

    protected function syncLocationDetail(Location $location, array $payload, Request $request): void
    {
        $detail = LocationDetail::firstOrCreate(['location_id' => $location->id]);
        $detailData = $payload['detail'] ?? [];

        $detailImageMap = [
            'logo_image' => 'detail_logo_image_file',
            'cover_image' => 'detail_cover_image_file',
            'menu_image' => 'detail_menu_image_file',
        ];

        foreach ($detailImageMap as $column => $fileKey) {
            if ($request->hasFile($fileKey)) {
                $detailData[$column] = $this->storeLocationImage($request->file($fileKey));
            } elseif (array_key_exists($column, $detailData) && $detailData[$column] === '') {
                $detailData[$column] = null;
            }
        }

        $detail->fill($detailData);
        $detail->location_id = $location->id;
        $detail->save();

        $detail->sections()->delete();

        foreach ($payload['sections'] ?? [] as $index => $sectionData) {
            $title = trim((string) ($sectionData['title'] ?? ''));
            $content = trim((string) ($sectionData['content'] ?? ''));
            $image = $sectionData['image'] ?? null;

            if ($request->hasFile("section_image_files.$index")) {
                $image = $this->storeLocationImage($request->file("section_image_files.$index"));
            } elseif ($image === '') {
                $image = null;
            }

            if ($title === '' && $content === '' && empty($image)) {
                continue;
            }

            $detail->sections()->create([
                'title' => $title !== '' ? $title : 'Khối nội dung ' . ($index + 1),
                'content' => $content !== '' ? $content : null,
                'image' => $image,
                'sort_order' => isset($sectionData['sort_order']) && $sectionData['sort_order'] !== ''
                    ? (int) $sectionData['sort_order']
                    : $index + 1,
            ]);
        }
    }

    protected function storeLocationImage(UploadedFile $file): string
    {
        $filename = now()->format('YmdHis') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images/location'), $filename);

        return 'images/location/' . $filename;
    }

    protected function normalizeTimeValue(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})/', $value, $matches)) {
            return str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . $matches[2];
        }

        return $value;
    }
}
