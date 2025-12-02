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
            'price.required' => 'Vui lòng nhập giá combo.',
            'price.numeric' => 'Giá phải là số.',
            'price.min' => 'Giá phải lớn hơn 0.',
            'price.regex' => 'Giá không hợp lệ. Vui lòng nhập số thập phân với tối đa 2 chữ số sau dấu phẩy (ví dụ: 5.00, 10.50).',
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'price' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'price.required' => 'Vui lòng nhập giá combo.',
            'price.numeric' => 'Giá phải là số.',
            'price.min' => 'Giá phải lớn hơn 0.',
            'price.regex' => 'Giá không hợp lệ. Vui lòng nhập số thập phân với tối đa 2 chữ số sau dấu phẩy (ví dụ: 5.00, 10.50).',
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
        // Kiểm tra xem combo đã có khách hàng đặt hay chưa
        $hasBookings = $combo->hasBookings();

        if ($hasBookings) {
            // Nếu đã có khách hàng đặt, không cho xóa, chỉ cho ẩn
            $combo->update(['is_hidden' => true]);
            
            return redirect()->route('admin.combos.index')
                ->with('error', 'Combo này đã có khách hàng đặt. Không thể xóa, chỉ có thể ẩn. Combo đã được ẩn và khách hàng sẽ thấy combo hiển thị "hết hàng".');
        } else {
            // Nếu chưa có khách hàng đặt, xóa hoàn toàn
            // Xóa ảnh khỏi storage
            if ($combo->image_path) {
                Storage::disk('public')->delete($combo->image_path);
            }

            $combo->delete();

            return redirect()->route('admin.combos.index')
                ->with('success', 'Combo này chưa được đặt hàng và đã được xóa thành công!');
        }
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

            return response()->json(['success' => true, 'message' => 'Thứ tự đã được cập nhật thành công.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Lỗi cập nhật thứ tự combos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thứ tự.'
            ], 500);
        }
    }
}
