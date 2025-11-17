<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Theater;
use App\Models\Showtime;
class ShowtimeController extends Controller
{
   
    public function index()
    {
        $showtimes = Showtime::with(['movie', 'theater', 'bookings.seats', 'bookings.combos'])
            ->latest()
            ->get()
            ->map(function ($showtime) {
                $completedBookings = $showtime->bookings->where('payment_status', 'completed');
                $seatCount = 0;
                $byCategory = ['Gold' => 0, 'Platinum' => 0, 'Box' => 0];
                foreach ($completedBookings as $bk) {
                    foreach ($bk->seats as $s) {
                        $seatCount += 1;
                        if (isset($byCategory[$s->seat_category])) {
                            $byCategory[$s->seat_category] += 1;
                        }
                    }
                }
                $comboTotals = [];
                foreach ($completedBookings as $bk) {
                    foreach ($bk->combos as $cb) {
                        $comboTotals[$cb->combo_name] = ($comboTotals[$cb->combo_name] ?? 0) + $cb->quantity;
                    }
                }
                $showtime->stats = [
                    'seat_count' => $seatCount,
                    'by_category' => $byCategory,
                    'combos' => $comboTotals,
                ];
                return $showtime;
            });
        return view('showtimes.index', compact('showtimes'));
    }
public function create()
{
    $movies = Movie::all();
    $theaters = Theater::all();
    return view('showtimes.create', compact('movies', 'theaters'));
}
public function store(Request $request)
{
    $validated = $request->validate([
        'movie_id' => 'required|exists:movies,id',
        'theater_id' => 'required|exists:theaters,id',
        'show_date' => 'required|date',
        'show_time' => 'required',
        'gold_price' => 'required|numeric|min:1|max:1000',
        'platinum_price' => 'required|numeric|min:1|max:1000',
        'box_price' => 'required|numeric|min:1|max:1000',
        'is_peak_hour' => 'boolean',
    ]);

    Showtime::create($validated);

    return redirect()->route('admin.showtimes.index')
            ->with('success', 'Created successfully');
}
public function show(Showtime $showtime)
{
    $showtime->load(['movie', 'theater']);
    return view('showtimes.show', compact('showtime'));
}
public function edit(Showtime $showtime)
{
    $movies = Movie::all();
    $theaters = Theater::all();
    return view('showtimes.edit', compact('showtime', 'movies', 'theaters'));
}
public function update(Request $request, Showtime $showtime)
{
    $validated = $request->validate([
        'movie_id' => 'required|exists:movies,id',
        'theater_id' => 'required|exists:theaters,id',
        'show_date' => 'required|date',
        'show_time' => 'required',
        'gold_price' => 'required|numeric|min:1|max:1000',
        'platinum_price' => 'required|numeric|min:1|max:1000',
        'box_price' => 'required|numeric|min:1|max:1000',
        'is_peak_hour' => 'boolean',
    ]);

    $showtime->update($validated);

    return redirect()->route('admin.showtimes.index')
        ->with('success', 'Updated successfully');
}
public function destroy(Showtime $showtime)
{
    $showtime->delete();

    return redirect()->route('admin.showtimes.index')
        ->with('success', 'Deleted successfully');
}
}