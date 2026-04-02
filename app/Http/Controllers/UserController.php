<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationDetail;
use App\Models\News;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Staff;

class UserController extends Controller
{
    public function home()     
    { 
        $homeNews = News::published()
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('user.home', compact('homeNews')); 
    }

    public function menu()
    {
        $categories = DB::table('category_product')
            ->join('product', 'category_product.id', '=', 'product.category_id')
            ->select('category_product.id', 'category_product.name')
            ->distinct()
            ->get();

        $products = DB::table('product')->get();

        return view('user.menu', compact('categories', 'products'));
    }

    public function location()  
    {
        $regions = Region::whereHas('locations', function($q) {
            $q->where('status', 'active');
        })->orderBy('name')->get();
        $locations = Location::with('region')
            ->where('status', 'active')
            ->get()
            ->map(fn (Location $location) => $this->decorateLocation($location));

        return view('user.location', compact('regions', 'locations')); 
    }

    public function locationShow(string $slug)
    {
        $location = Location::with(['region', 'detail.sections'])
            ->where('status', 'active')
            ->where('slug', $slug)
            ->firstOrFail();

        $location = $this->decorateLocation($location);
        $detail = $location->detail;

        if (!$detail) {
            $detail = new LocationDetail();
            $detail->setRelation('sections', collect());
        }

        return view('user.location-detail', compact('location', 'detail'));
    }

    public function news()      
    { 
        $newsItems = News::published()
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $featuredNews = $newsItems->firstWhere('is_featured', true) ?? $newsItems->first();
        $otherNews = $newsItems
            ->filter(fn (News $item) => !$featuredNews || $item->id !== $featuredNews->id)
            ->values();

        return view('user.news', compact('newsItems', 'featuredNews', 'otherNews')); 
    }

    public function newsShow(News $news)
    {
        if ($news->status !== 'published' || ($news->published_at && $news->published_at->isFuture())) {
            abort(404);
        }

        $sidebarQuery = News::published()
            ->where('id', '!=', $news->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $latestNews = (clone $sidebarQuery)
            ->limit(3)
            ->get();

        $sidebarBanners = (clone $sidebarQuery)
            ->skip(3)
            ->take(2)
            ->get();

        return view('user.news-detail', compact('news', 'latestNews', 'sidebarBanners'));
    }

    public function contact()   
    { 
        return view('user.contact'); 
    }

    private function decorateLocation(Location $location): Location
    {
        $location->formatted_time_start = $location->time_start
            ? \Carbon\Carbon::parse($location->time_start)->format('H:i')
            : '09:00';
        $location->formatted_time_end = $location->time_end
            ? \Carbon\Carbon::parse($location->time_end)->format('H:i')
            : '24:00';
        $location->detail_url = $location->slug
            ? route('location.show', ['slug' => $location->slug])
            : route('location');

        return $location;
    }
}
