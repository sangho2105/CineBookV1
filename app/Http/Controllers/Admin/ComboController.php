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
            'price' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'price.required' => 'Please enter the combo price.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be greater than 0.',
            'price.regex' => 'Invalid price format. Please enter a decimal number with up to 2 decimal places (e.g., 5.00, 10.50).',
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
            ->with('success', 'Combo has been created successfully!');
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
            'price' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'price.required' => 'Please enter the combo price.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be greater than 0.',
            'price.regex' => 'Invalid price format. Please enter a decimal number with up to 2 decimal places (e.g., 5.00, 10.50).',
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
            ->with('success', 'Combo has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * If combo has bookings, only allow hiding instead of deleting.
     */
    public function destroy(Combo $combo)
    {
        // Kiểm tra xem combo có khách hàng đặt hay chưa
        if ($combo->hasBookings()) {
            // Nếu có bookings, chỉ cho phép ẩn
            $combo->is_hidden = true;
            $combo->save();
            
            return redirect()->route('admin.combos.index')
                ->with('error', "Combo '{$combo->name}' has been ordered by customers. It cannot be deleted, but has been hidden instead.");
        }
        
        // Nếu chưa có bookings, cho phép xóa
        // Xóa ảnh nếu có
        if ($combo->image_path) {
            Storage::disk('public')->delete($combo->image_path);
        }
        
        $combo->delete();
        
        return redirect()->route('admin.combos.index')
            ->with('success', "Combo '{$combo->name}' has been deleted successfully!");
    }

    /**
     * Toggle ẩn/hiện combo thay vì xóa
     */
    public function toggleHidden($id)
    {
        $combo = Combo::findOrFail($id);
        $combo->is_hidden = !$combo->is_hidden;
        $combo->save();

        $status = $combo->is_hidden ? 'hidden' : 'shown';
        $message = $combo->is_hidden 
            ? "Combo '{$combo->name}' has been hidden successfully!" 
            : "Combo '{$combo->name}' has been shown successfully!";

        return redirect()->route('admin.combos.index')->with('success', $message);
    }

    /**
     * Update the sort order of combos via AJAX.
     */
    public function updateOrder(Request $request)
    {
        try {
            $request->validate([
                'order' => 'required|array',
                'order.*' => 'required|integer|exists:combos,id',
            ]);

            foreach ($request->order as $index => $comboId) {
                Combo::where('id', $comboId)->update(['sort_order' => $index + 1]);
            }

            return response()->json(['success' => true, 'message' => 'Order has been updated successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating combo order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the order.'
            ], 500);
        }
    }
}
