<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComboController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Combo::query();
        
        // Lọc theo tên combo
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', '%' . $search . '%');
        }
        
        $combos = $query->orderBy('id', 'desc')->paginate(6)->withQueryString();
        
        return view('admin.combos.index', compact('combos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.combos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|max:4096',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Xử lý upload ảnh
        if ($request->hasFile('image')) {
            $validatedData['image_path'] = $request->file('image')->store('combos', 'public');
        }

        // Xử lý is_active
        $validatedData['is_active'] = $request->boolean('is_active', true);

        // Xóa key image vì đã chuyển thành image_path
        unset($validatedData['image']);

        Combo::create($validatedData);

        return redirect()->route('admin.combos.index')
            ->with('success', 'Combo đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Combo $combo)
    {
        return view('admin.combos.show', compact('combo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Combo $combo)
    {
        return view('admin.combos.edit', compact('combo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Combo $combo)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Xử lý upload ảnh mới
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($combo->image_path) {
                Storage::disk('public')->delete($combo->image_path);
            }
            // Lưu ảnh mới
            $validatedData['image_path'] = $request->file('image')->store('combos', 'public');
        }

        // Xử lý is_active
        $validatedData['is_active'] = $request->boolean('is_active', true);

        // Xóa key image vì đã chuyển thành image_path
        unset($validatedData['image']);

        $combo->update($validatedData);

        return redirect()->route('admin.combos.index')
            ->with('success', 'Combo đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Combo $combo)
    {
        // Xóa ảnh khỏi storage
        if ($combo->image_path) {
            Storage::disk('public')->delete($combo->image_path);
        }

        $combo->delete();

        return redirect()->route('admin.combos.index')
            ->with('success', 'Combo đã được xóa thành công!');
    }
}
