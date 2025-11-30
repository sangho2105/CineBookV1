<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\ComboItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
            $query->where('name', 'like', '%' . $search . '%');
        }
        
        $combos = $query->orderBy('sort_order')->orderBy('id', 'desc')->paginate(6)->withQueryString();
        
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'price' => 'required|numeric|min:0.01',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|string|in:popcorn,drink,food',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.size' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Xử lý upload ảnh
        if ($request->hasFile('image')) {
            $validatedData['image_path'] = $request->file('image')->store('combos', 'public');
        }

        // Xử lý is_active
        $validatedData['is_active'] = $request->boolean('is_active', true);

        // Xóa key image vì đã chuyển thành image_path
        unset($validatedData['image']);

        // Lưu items
        $items = $validatedData['items'];
        unset($validatedData['items']);

        DB::transaction(function () use ($validatedData, $items) {
            $combo = Combo::create($validatedData);
            
            foreach ($items as $item) {
                ComboItem::create([
                    'combo_id' => $combo->id,
                    'item_type' => $item['item_type'],
                    'item_name' => $item['item_name'],
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }
        });

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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'price' => 'required|numeric|min:0.01',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|string|in:popcorn,drink,food',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.size' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|integer|min:1',
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

        // Lưu items
        $items = $validatedData['items'];
        unset($validatedData['items']);

        DB::transaction(function () use ($validatedData, $items, $combo) {
            $combo->update($validatedData);
            
            // Xóa items cũ
            $combo->items()->delete();
            
            // Tạo items mới
            foreach ($items as $item) {
                ComboItem::create([
                    'combo_id' => $combo->id,
                    'item_type' => $item['item_type'],
                    'item_name' => $item['item_name'],
                    'size' => $item['size'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }
        });

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
