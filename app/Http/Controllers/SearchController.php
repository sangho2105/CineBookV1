<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Theater;
use App\Models\Showtime;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Display the search page with filters
     */
    public function index(Request $request)
    {
        // Get all unique cities from theaters
        $cities = Theater::select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        // Get all unique genres from movies
        $genres = Movie::select('genre')
            ->distinct()
            ->orderBy('genre')
            ->get()
            ->pluck('genre')
            ->flatMap(function ($genre) {
                // Split genres if they contain commas
                return array_map('trim', explode(',', $genre));
            })
            ->unique()
            ->sort()
            ->values();

        // Get all unique languages from movies
        $languages = Movie::select('language')
            ->whereNotNull('language')
            ->where('language', '!=', '')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');

        // Predefined language options (label in Vietnamese, value matches DB)
        $languageOptions = collect([
            ['value' => 'Vietnamese', 'label' => 'Tiếng Việt'],
            ['value' => 'English', 'label' => 'Tiếng Anh'],
            ['value' => 'Korean', 'label' => 'Tiếng Hàn'],
            ['value' => 'Japanese', 'label' => 'Tiếng Nhật'],
            ['value' => 'Chinese', 'label' => 'Tiếng Trung'],
            ['value' => 'French', 'label' => 'Tiếng Pháp'],
            ['value' => 'Thai', 'label' => 'Tiếng Thái'],
            ['value' => 'Spanish', 'label' => 'Tiếng Tây Ban Nha'],
            ['value' => 'Hindi', 'label' => 'Tiếng Hindi'],
        ]);

        // Append any extra languages from DB not in predefined list
        $extraLanguages = collect($languages)->diff($languageOptions->pluck('value'));
        $extraLanguageOptions = $extraLanguages->map(fn ($v) => ['value' => $v, 'label' => $v])->values();
        $languageOptions = $languageOptions->concat($extraLanguageOptions)->values();

        // Get all theaters for filter
        $theaters = Theater::orderBy('name')->get();

        // Initialize query
        $query = Movie::query();

        // Apply search keyword filter
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('synopsis', 'like', "%{$keyword}%");
            });
        }

        // Apply genre filter
        if ($request->filled('genre')) {
            $query->where('genre', 'like', "%{$request->genre}%");
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply language filter
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        // Apply rating minimum filter
        if ($request->filled('rating_min')) {
            $query->where('rating_average', '>=', (float) $request->rating_min);
        }

        // Apply city filter (through theaters and showtimes)
        if ($request->filled('city')) {
            $query->whereHas('showtimes.theater', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        // Apply theater filter
        if ($request->filled('theater_id')) {
            $query->whereHas('showtimes', function ($q) use ($request) {
                $q->where('theater_id', $request->theater_id);
            });
        }

        // Apply date filter
        if ($request->filled('date')) {
            $query->whereHas('showtimes', function ($q) use ($request) {
                $q->whereDate('show_date', $request->date);
            });
        }

        // Load số lượt like và kiểm tra trạng thái like của user hiện tại
        $query->withCount('favorites');
        
        if (auth()->check()) {
            $userId = auth()->id();
            $query->withExists(['favorites as is_favorited' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }]);
        }

        // Thêm subquery để đếm số vé bán được (booking đã thanh toán completed)
        $query->addSelect([
            'tickets_sold' => DB::table('bookings')
                ->join('showtimes', 'bookings.showtime_id', '=', 'showtimes.id')
                ->whereColumn('showtimes.movie_id', 'movies.id')
                ->where('bookings.payment_status', 'completed')
                ->selectRaw('COUNT(*)')
        ]);

        // Sắp xếp phim theo số vé bán được (DESC), nếu bằng nhau thì sắp xếp theo tên
        $query->orderByRaw('COALESCE(tickets_sold, 0) DESC')
              ->orderBy('movies.title');

        // Get results with pagination
        $movies = $query->with(['showtimes.theater'])
            ->paginate(12)
            ->appends($request->all());

        // Get theaters filtered by city if city is selected
        $filteredTheaters = $theaters;
        if ($request->filled('city')) {
            $filteredTheaters = Theater::where('city', $request->city)
                ->orderBy('name')
                ->get();
        }

        return view('search.index', compact(
            'movies',
            'cities',
            'genres',
            'languageOptions',
            'theaters',
            'filteredTheaters'
        ));
    }

    /**
     * AJAX endpoint for getting theaters by city
     */
    public function getTheatersByCity(Request $request)
    {
        $city = $request->city;
        
        $theaters = Theater::where('city', $city)
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'address']);

        return response()->json($theaters);
    }

    /**
     * AJAX endpoint for autocomplete search
     */
    public function autocomplete(Request $request)
    {
        $keyword = $request->keyword;
        
        $movies = Movie::where('title', 'like', "%{$keyword}%")
            ->orWhere('genre', 'like', "%{$keyword}%")
            ->limit(10)
            ->get(['id', 'title', 'genre', 'poster_url']);

        // Map để thêm poster_image_url (accessor)
        $movies = $movies->map(function ($movie) {
            return [
                'id' => $movie->id,
                'title' => $movie->title,
                'genre' => $movie->genre,
                'poster_url' => $movie->poster_url,
                'poster_image_url' => $movie->poster_image_url,
            ];
        });

        return response()->json($movies);
    }
}
