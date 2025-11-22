<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie; // Import model Movie
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Movie::query();
        
        // Lọc theo tên phim
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', '%' . $search . '%');
        }
        
        // Sắp xếp theo ID tăng dần (phim nào thêm trước sẽ có STT nhỏ hơn)
        $movies = $query->orderBy('id', 'asc')->get();
        return view('admin.movies.index', compact('movies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.movies.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'poster' => ['nullable', 'image', 'max:4096'],
            'poster_url' => ['nullable', 'url'],
            'genre' => 'required',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'language' => 'required',
            'duration_minutes' => 'required|integer',
            'trailer_url' => 'nullable',
            'synopsis' => 'nullable',
            'release_date' => 'required|date',
            'rated' => 'nullable|string|in:K,T13,T16,T18,P',
            'status' => 'required|in:upcoming,now_showing,ended',
        ]);

        // Xử lý poster: ưu tiên file upload, nếu không có thì dùng URL
        if ($request->hasFile('poster')) {
            $validatedData['poster_url'] = $request->file('poster')->store('movies', 'public');
        } elseif (empty($validatedData['poster_url'])) {
            return back()
                ->withErrors(['poster' => 'Vui lòng chọn ảnh poster hoặc nhập URL ảnh poster.'])
                ->withInput();
        }

        // Xóa key poster vì đã chuyển thành poster_url
        unset($validatedData['poster']);
        
        // Đặt giá trị mặc định cho rating_average nếu không có
        if (!isset($validatedData['rating_average'])) {
            $validatedData['rating_average'] = 0;
        }

        Movie::create($validatedData);

        return redirect()->route('admin.movies.index')->with('success', 'Phim đã được thêm thành công!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        return view('admin.movies.show', compact('movie'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $movie = Movie::findOrFail($id);
        return view('admin.movies.edit', compact('movie'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'poster' => ['nullable', 'image', 'max:4096'],
            'poster_url' => ['nullable', 'url'],
            'genre' => 'required',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'language' => 'required',
            'duration_minutes' => 'required|integer',
            'trailer_url' => 'nullable',
            'synopsis' => 'nullable',
            'release_date' => 'required|date',
            'rated' => 'nullable|string|in:K,T13,T16,T18,P',
            'status' => 'required|in:upcoming,now_showing,ended',
        ]);

        $movie = Movie::findOrFail($id);
        
        // Giữ nguyên rating_average hiện tại nếu không cung cấp
        if (!isset($validatedData['rating_average'])) {
            $validatedData['rating_average'] = $movie->rating_average ?? 0;
        }

        // Xử lý poster: ưu tiên file upload
        if ($request->hasFile('poster')) {
            // Xóa ảnh cũ nếu là file trong storage
            if ($movie->poster_url && !filter_var($movie->poster_url, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($movie->poster_url);
            }
            $validatedData['poster_url'] = $request->file('poster')->store('movies', 'public');
        } elseif (empty($validatedData['poster_url']) && empty($movie->poster_url)) {
            return back()
                ->withErrors(['poster' => 'Vui lòng chọn ảnh poster hoặc nhập URL ảnh poster.'])
                ->withInput();
        } elseif (empty($validatedData['poster_url'])) {
            // Giữ nguyên poster_url hiện tại nếu không upload file mới và không nhập URL mới
            $validatedData['poster_url'] = $movie->poster_url;
        }

        // Xóa key poster vì đã chuyển thành poster_url
        unset($validatedData['poster']);

        $movie->update($validatedData);

        return redirect()->route('admin.movies.index')->with('success', 'Phim đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        
        // Xóa poster khỏi storage nếu là file (không phải URL)
        if ($movie->poster_url && !filter_var($movie->poster_url, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($movie->poster_url);
        }

        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Phim đã được xóa thành công!');
    }
}
