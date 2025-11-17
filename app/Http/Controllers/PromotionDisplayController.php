<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Carbon\Carbon;

class PromotionDisplayController extends Controller
{
    /**
     * Danh sách các khuyến mãi/sự kiện công khai.
     */
    public function index()
    {
        $promotions = Promotion::with('movie')
            ->active()
            ->whereIn('category', ['promotion', 'discount', 'event'])
            ->latest('start_date')
            ->paginate(9);

        return view('promotions.index', compact('promotions'));
    }

    /**
     * Hiển thị chi tiết khuyến mãi công khai.
     */
    public function show(Promotion $promotion)
    {
        $today = Carbon::today();

        abort_unless(
            $promotion->is_active
            && $promotion->start_date->lte($today)
            && ($promotion->end_date === null || $promotion->end_date->gte($today)),
            404
        );

        if ($promotion->category === 'movie') {
            return $promotion->movie
                ? redirect()->route('movie.show', $promotion->movie)
                : abort(404);
        }

        $promotion->load('movie');

        return view('promotions.show', compact('promotion'));
    }
}

