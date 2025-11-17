<?php

namespace App\Http\Controllers;

use App\Models\Theater;
use Illuminate\Http\Request;

class TheaterController extends Controller
{
    public function index()
    {
        $theaters = Theater::orderBy('city')->orderBy('name')->get();
        return view('theaters.index', compact('theaters'));
    }

    public function create()
    {
        return view('theaters.create');
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

        return redirect()->route('theaters.index')
            ->with('success', 'Theater created successfully!');
    }

    public function show(Theater $theater)
    {
        return view('theaters.show', compact('theater'));
    }

    public function edit(Theater $theater)
    {
        return view('theaters.edit', compact('theater'));
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

        return redirect()->route('theaters.index')
            ->with('success', 'Theater updated successfully!');
    }

    public function destroy(Theater $theater)
    {
        $theater->delete();

        return redirect()->route('theaters.index')
            ->with('success', 'Theater deleted successfully!');
    }
}