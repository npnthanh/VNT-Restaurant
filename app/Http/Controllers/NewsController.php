<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function index()
    {
        $newsItems = News::orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('pos.news', compact('newsItems'));
    }

    public function show($id)
    {
        $news = News::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $news,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $news = News::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Thêm bài viết thành công',
            'data' => $news,
        ]);
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);
        $data = $this->validatePayload($request, $news->id);
        $news->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật bài viết thành công',
            'data' => $news->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa bài viết thành công',
        ]);
    }

    protected function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('news', 'slug')->ignore($ignoreId),
            ],
            'category' => 'required|string|max:100',
            'summary' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => 'nullable|date',
            'is_featured' => 'nullable|boolean',
        ]);

        $data['slug'] = $this->generateUniqueSlug(
            $data['slug'] ?? $data['title'],
            $ignoreId
        );

        $data['is_featured'] = $request->boolean('is_featured');

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = now()->format('YmdHis') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/news'), $filename);
            $data['image'] = 'images/news/' . $filename;
        } else {
            unset($data['image']);
        }

        return $data;
    }

    protected function generateUniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($source);
        if ($baseSlug === '') {
            $baseSlug = 'tin-tuc';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (
            News::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
