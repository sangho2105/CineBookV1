<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theater;
use Illuminate\Http\Request;

class TheaterController extends Controller
{
    public function index()
    {
        $theaters = Theater::orderBy('city')->orderBy('name')->paginate(8);
        return view('admin.theaters.index', compact('theaters'));
    }

    public function create()
    {
        return view('admin.theaters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'address' => 'required|string',
            'seating_capacity' => 'required|integer|min:1',
        ]);

        Theater::create($request->all());

        return redirect()->route('admin.theaters.index')
            ->with('success', 'Rạp chiếu phim đã được thêm thành công!');
    }

    public function show(Theater $theater)
    {
        return view('admin.theaters.show', compact('theater'));
    }

    public function edit(Theater $theater)
    {
        return view('admin.theaters.edit', compact('theater'));
    }

    public function update(Request $request, Theater $theater)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'address' => 'required|string',
            'seating_capacity' => 'required|integer|min:1',
        ]);

        $theater->update($request->all());

        return redirect()->route('admin.theaters.index')
            ->with('success', 'Rạp chiếu phim đã được cập nhật thành công!');
    }

    public function destroy(Theater $theater)
    {
        $theater->delete();

        return redirect()->route('admin.theaters.index')
            ->with('success', 'Rạp chiếu phim đã được xóa thành công!');
    }
}
