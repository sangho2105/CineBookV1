<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie; // Import model Movie
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $movies = Movie::all(); // Lấy tất cả các phim
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
            'poster_url' => 'required',
            'genre' => 'required',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'language' => 'required',
            'duration_minutes' => 'required|integer',
            'trailer_url' => 'nullable',
            'synopsis' => 'nullable',
            'release_date' => 'required|date',
            'rating_average' => 'required|numeric',
            'status' => 'required|in:upcoming,now_showing,ended',
        ]);

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
            'poster_url' => 'required',
            'genre' => 'required',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'language' => 'required',
            'duration_minutes' => 'required|integer',
            'trailer_url' => 'nullable',
            'synopsis' => 'nullable',
            'release_date' => 'required|date',
            'rating_average' => 'required|numeric',
            'status' => 'required|in:upcoming,now_showing,ended',
        ]);

        $movie = Movie::findOrFail($id);
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
        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Phim đã được xóa thành công!');
    }
}
