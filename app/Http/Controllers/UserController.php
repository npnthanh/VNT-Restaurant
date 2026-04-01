<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;

class UserController extends Controller
{
    public function home()     
    { 
        return view('user.home'); 
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
        $regions = \App\Models\Region::whereHas('locations', function($q) {
            $q->where('status', 'active');
        })->orderBy('name')->get();
        $locations = \App\Models\Location::with('region')->where('status', 'active')->get();

        // Format times
        $locations->transform(function($location) {
            $location->formatted_time_start = $location->time_start ? \Carbon\Carbon::parse($location->time_start)->format('H:i') : '09:00';
            $location->formatted_time_end = $location->time_end ? \Carbon\Carbon::parse($location->time_end)->format('H:i') : '24:00';
            return $location;
        });

        return view('user.location', compact('regions', 'locations')); 
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
}
