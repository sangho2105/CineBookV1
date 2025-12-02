<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Promotion;
use App\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    /**
     * Display a listing of the promotions.
     */
    public function index(Request $request)
    {
        $query = Promotion::with('movie');
        
        // Lọc theo tên khuyến mãi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', '%' . $search . '%');
        }
        
        $promotions = $query->orderBy('sort_order')->orderByDesc('created_at')
            ->paginate(8)
            ->withQueryString(); // Giữ lại query parameters khi phân trang

        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create()
    {
        $movies = Movie::orderBy('title')->get(['id', 'title']);
        $combos = Combo::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('admin.promotions.create', compact('movies', 'combos'));
    }

    /**
     * Store a newly created promotion in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['promotion', 'discount', 'event', 'movie'])],
            'description' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string'],
            'image' => ['required', 'image', 'max:4096'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'movie_id' => ['nullable', 'exists:movies,id'],
        ];

        // Validation cho discount_rules chỉ khi category là 'promotion' hoặc 'discount'
        if (in_array($request->category, ['promotion', 'discount'])) {
            // discount_rules không bắt buộc cho 'promotion', chỉ bắt buộc cho 'discount'
            // Mỗi promotion chỉ có một quy tắc (max:1)
            if ($request->category === 'discount') {
                $rules['discount_rules'] = ['required', 'array', 'min:1', 'max:1'];
            } else {
                $rules['discount_rules'] = ['nullable', 'array', 'max:1'];
            }
            
            // Nếu có discount_rules, validate các field
            if ($request->has('discount_rules') && is_array($request->discount_rules) && count($request->discount_rules) > 0) {
                $rules['discount_rules.*.discount_percentage'] = ['nullable', 'integer', 'in:5,10,15,20,25,30'];
                $rules['discount_rules.*.min_tickets'] = ['nullable', 'integer', 'min:1'];
                $rules['discount_rules.*.applies_to'] = ['nullable', 'array'];
                $rules['discount_rules.*.applies_to.*'] = ['nullable', 'string', 'in:ticket,combo,total'];
                $rules['discount_rules.*.requires_combo'] = ['nullable', 'boolean'];
                $rules['discount_rules.*.combo_ids'] = ['nullable', 'array'];
                $rules['discount_rules.*.combo_ids.*'] = ['nullable', 'integer', 'exists:combos,id'];
                $rules['discount_rules.*.requires_combo_ids'] = ['nullable', 'array'];
                $rules['discount_rules.*.requires_combo_ids.*'] = ['nullable', 'integer', 'exists:combos,id'];
                $rules['discount_rules.*.movie_id'] = ['nullable', 'integer', 'exists:movies,id'];
                $rules['discount_rules.*.gift_only'] = ['nullable', 'boolean'];
            }
        }

        $data = $request->validate($rules);

        if ($data['category'] === 'movie' && blank($data['movie_id'])) {
            return back()
                ->withErrors(['movie_id' => 'Vui lòng chọn phim khi loại chiến dịch là Phim.'])
                ->withInput();
        }

        // Xử lý discount_rules
        if (in_array($data['category'], ['promotion', 'discount'])) {
            // Nếu không có discount_rules hoặc rỗng, set null
            if (empty($data['discount_rules']) || !is_array($data['discount_rules'])) {
                $data['discount_rules'] = null;
            } else {
                // Chỉ lấy quy tắc đầu tiên (mỗi promotion chỉ có một quy tắc)
                $data['discount_rules'] = [reset($data['discount_rules'])];
                
                // Validate combo_ids nếu applies_to có 'combo'
                foreach ($data['discount_rules'] as $index => $rule) {
                    if (isset($rule['applies_to']) && in_array('combo', $rule['applies_to'])) {
                        if (empty($rule['combo_ids']) || !is_array($rule['combo_ids'])) {
                            return back()
                                ->withErrors(["discount_rules.{$index}.combo_ids" => 'Vui lòng chọn ít nhất một combo khi áp dụng cho "Giá combo".'])
                                ->withInput();
                        }
                    }
                    
                    // Validate requires_combo_ids nếu requires_combo = true
                    if (isset($rule['requires_combo']) && $rule['requires_combo']) {
                        if (empty($rule['requires_combo_ids']) || !is_array($rule['requires_combo_ids'])) {
                            return back()
                                ->withErrors(["discount_rules.{$index}.requires_combo_ids" => 'Vui lòng chọn ít nhất một combo khi chọn "Yêu cầu có combo".'])
                                ->withInput();
                        }
                    }
                    
                    // Xử lý requires_combo_ids - sync với combo_ids nếu applies_to có 'combo'
                    if (isset($rule['applies_to']) && in_array('combo', $rule['applies_to'])) {
                        if (!empty($rule['requires_combo_ids']) && is_array($rule['requires_combo_ids'])) {
                            $data['discount_rules'][$index]['combo_ids'] = $rule['requires_combo_ids'];
                        }
                    }
                    
                    // Xử lý requires_combo_ids - nếu không có thì set null
                    if (!isset($rule['requires_combo_ids']) || empty($rule['requires_combo_ids'])) {
                        $data['discount_rules'][$index]['requires_combo_ids'] = null;
                    }
                    // Xử lý movie_id - nếu không có thì set null
                    if (!isset($rule['movie_id']) || empty($rule['movie_id'])) {
                        $data['discount_rules'][$index]['movie_id'] = null;
                    }
                }
            }
        } else {
            // Nếu không phải promotion hoặc discount, set discount_rules = null
            $data['discount_rules'] = null;
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['movie_id'] = $data['category'] === 'movie' ? $data['movie_id'] : null;
        
        // Lưu ảnh vào storage
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
        $combos = Combo::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('admin.promotions.edit', compact('promotion', 'movies', 'combos'));
    }

    /**
     * Update the specified promotion in storage.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['promotion', 'discount', 'event', 'movie'])],
            'description' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'movie_id' => ['nullable', 'exists:movies,id'],
        ];

        // Validation cho discount_rules chỉ khi category là 'promotion' hoặc 'discount'
        if (in_array($request->category, ['promotion', 'discount'])) {
            // discount_rules không bắt buộc cho 'promotion', chỉ bắt buộc cho 'discount'
            // Mỗi promotion chỉ có một quy tắc (max:1)
            if ($request->category === 'discount') {
                $rules['discount_rules'] = ['required', 'array', 'min:1', 'max:1'];
            } else {
                $rules['discount_rules'] = ['nullable', 'array', 'max:1'];
            }
            
            // Nếu có discount_rules, validate các field
            if ($request->has('discount_rules') && is_array($request->discount_rules) && count($request->discount_rules) > 0) {
                $rules['discount_rules.*.discount_percentage'] = ['nullable', 'integer', 'in:5,10,15,20,25,30'];
                $rules['discount_rules.*.min_tickets'] = ['nullable', 'integer', 'min:1'];
                $rules['discount_rules.*.applies_to'] = ['nullable', 'array'];
                $rules['discount_rules.*.applies_to.*'] = ['nullable', 'string', 'in:ticket,combo,total'];
                $rules['discount_rules.*.requires_combo'] = ['nullable', 'boolean'];
                $rules['discount_rules.*.combo_ids'] = ['nullable', 'array'];
                $rules['discount_rules.*.combo_ids.*'] = ['nullable', 'integer', 'exists:combos,id'];
                $rules['discount_rules.*.requires_combo_ids'] = ['nullable', 'array'];
                $rules['discount_rules.*.requires_combo_ids.*'] = ['nullable', 'integer', 'exists:combos,id'];
                $rules['discount_rules.*.movie_id'] = ['nullable', 'integer', 'exists:movies,id'];
                $rules['discount_rules.*.gift_only'] = ['nullable', 'boolean'];
            }
        }

        $data = $request->validate($rules);

        if ($data['category'] === 'movie' && blank($data['movie_id'])) {
            return back()
                ->withErrors(['movie_id' => 'Vui lòng chọn phim khi loại chiến dịch là Phim.'])
                ->withInput();
        }

        // Xử lý discount_rules
        if (in_array($data['category'], ['promotion', 'discount'])) {
            // Nếu không có discount_rules hoặc rỗng, set null
            if (empty($data['discount_rules']) || !is_array($data['discount_rules'])) {
                $data['discount_rules'] = null;
            } else {
                // Chỉ lấy quy tắc đầu tiên (mỗi promotion chỉ có một quy tắc)
                $data['discount_rules'] = [reset($data['discount_rules'])];
                
                // Xử lý discount_rules
                foreach ($data['discount_rules'] as $index => $rule) {
                    // Validate requires_combo_ids nếu requires_combo = true
                    if (isset($rule['requires_combo']) && $rule['requires_combo']) {
                        if (empty($rule['requires_combo_ids']) || !is_array($rule['requires_combo_ids'])) {
                            return back()
                                ->withErrors(["discount_rules.{$index}.requires_combo_ids" => 'Vui lòng chọn ít nhất một combo khi chọn "Yêu cầu có combo".'])
                                ->withInput();
                        }
                    }
                    
                    // Sync combo_ids từ requires_combo_ids nếu có
                    if (!empty($rule['requires_combo_ids']) && is_array($rule['requires_combo_ids'])) {
                        // Nếu applies_to có 'combo', sync combo_ids từ requires_combo_ids
                        if (isset($rule['applies_to']) && is_array($rule['applies_to']) && in_array('combo', $rule['applies_to'])) {
                            $data['discount_rules'][$index]['combo_ids'] = $rule['requires_combo_ids'];
                        }
                    }
                    
                    // Validate combo_ids nếu applies_to có 'combo' và không có requires_combo_ids
                    if (isset($rule['applies_to']) && is_array($rule['applies_to']) && in_array('combo', $rule['applies_to'])) {
                        // Nếu không có combo_ids và không có requires_combo_ids, báo lỗi
                        if (empty($rule['combo_ids']) && empty($rule['requires_combo_ids'])) {
                            return back()
                                ->withErrors(["discount_rules.{$index}.combo_ids" => 'Vui lòng chọn combo trong "Yêu cầu có combo" khi áp dụng cho "Giá combo".'])
                                ->withInput();
                        }
                    }
                    
                    // Xử lý requires_combo_ids - nếu không có thì set null
                    if (!isset($rule['requires_combo_ids']) || empty($rule['requires_combo_ids'])) {
                        $data['discount_rules'][$index]['requires_combo_ids'] = null;
                    }
                    // Xử lý movie_id - nếu không có thì set null
                    if (!isset($rule['movie_id']) || empty($rule['movie_id'])) {
                        $data['discount_rules'][$index]['movie_id'] = null;
                    }
                }
            }
        } else {
            // Nếu không phải promotion hoặc discount, set discount_rules = null
            $data['discount_rules'] = null;
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['movie_id'] = $data['category'] === 'movie' ? $data['movie_id'] : null;

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($promotion->image_path) {
                Storage::disk('public')->delete($promotion->image_path);
            }
            // Lưu ảnh mới vào storage
            $data['image_path'] = $request->file('image')->store('promotions', 'public');
        }

        $promotion->update($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Khuyến mãi đã được cập nhật.');
    }

    /**
     * Remove the specified promotion from storage.
     * Nếu sự kiện đang trong thời gian hoạt động, chỉ cho phép ẩn (set is_active = false).
     */
    public function destroy(Promotion $promotion)
    {
        // Kiểm tra xem sự kiện có đang trong thời gian hoạt động không
        if ($promotion->isCurrentlyActive()) {
            // Nếu đang hoạt động, chỉ cho phép ẩn
            $promotion->update(['is_active' => false]);
            
            return redirect()->route('admin.promotions.index')
                ->with('error', 'Sự kiện đang trong thời gian hoạt động. Không thể xóa, chỉ có thể ẩn. Sự kiện đã được ẩn.');
        }
        
        // Nếu không đang hoạt động, cho phép xóa
        // Xóa ảnh khỏi storage
        if ($promotion->image_path) {
            Storage::disk('public')->delete($promotion->image_path);
        }

        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Khuyến mãi đã được xóa.');
    }

    /**
     * Update the sort order of promotions via AJAX.
     */
    public function updateOrder(Request $request)
    {
        try {
            $request->validate([
                'order' => 'required|array',
                'order.*' => 'required|integer|exists:promotions,id',
            ]);

            foreach ($request->order as $index => $promotionId) {
                Promotion::where('id', $promotionId)->update(['sort_order' => $index + 1]);
            }

            return response()->json(['success' => true, 'message' => 'Thứ tự đã được cập nhật thành công.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Lỗi cập nhật thứ tự promotions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thứ tự.'
            ], 500);
        }
    }
}

