<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    /**
     * Display a listing of the promotions.
     */
    public function index()
    {
        $promotions = Promotion::with('movie')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create()
    {
        $movies = Movie::orderBy('title')->get(['id', 'title']);

        return view('admin.promotions.create', compact('movies'));
    }

    /**
     * Store a newly created promotion in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['promotion', 'discount', 'event', 'movie'])],
            'description' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string', 'max:2000'],
            'image' => ['required', 'image', 'max:4096'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'movie_id' => ['nullable', 'exists:movies,id'],
        ]);

        if ($data['category'] === 'movie' && blank($data['movie_id'])) {
            return back()
                ->withErrors(['movie_id' => 'Vui lòng chọn phim khi loại chiến dịch là Phim.'])
                ->withInput();
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['movie_id'] = $data['category'] === 'movie' ? $data['movie_id'] : null;
        $data['image_path'] = $request->file('image')->store('promotions', 'public');

        Promotion::create($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Khuyến mãi đã được tạo thành công.');
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(Promotion $promotion)
    {
        $movies = Movie::orderBy('title')->get(['id', 'title']);

        return view('admin.promotions.edit', compact('promotion', 'movies'));
    }

    /**
     * Update the specified promotion in storage.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['promotion', 'discount', 'event', 'movie'])],
            'description' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'movie_id' => ['nullable', 'exists:movies,id'],
        ]);

        if ($data['category'] === 'movie' && blank($data['movie_id'])) {
            return back()
                ->withErrors(['movie_id' => 'Vui lòng chọn phim khi loại chiến dịch là Phim.'])
                ->withInput();
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['movie_id'] = $data['category'] === 'movie' ? $data['movie_id'] : null;

        if ($request->hasFile('image')) {
            if ($promotion->image_path) {
                Storage::disk('public')->delete($promotion->image_path);
            }
            $data['image_path'] = $request->file('image')->store('promotions', 'public');
        }

        $promotion->update($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Khuyến mãi đã được cập nhật.');
    }

    /**
     * Remove the specified promotion from storage.
     */
    public function destroy(Promotion $promotion)
    {
        if ($promotion->image_path) {
            Storage::disk('public')->delete($promotion->image_path);
        }

        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Khuyến mãi đã được xóa.');
    }
}

