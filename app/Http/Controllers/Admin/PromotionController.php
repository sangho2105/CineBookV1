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
        $combos = Combo::active()->visible()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

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
                ->withErrors(['movie_id' => 'Please select a movie when category is Movie.'])
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
                                ->withErrors(["discount_rules.{$index}.combo_ids" => 'Please select at least one combo when applying to "Combo Price".'])
                                ->withInput();
                        }
                    }
                    
                    // Không validate requires_combo_ids nữa vì:
                    // - Nếu chỉ tặng quà (không giảm giá): có thể không chọn combo (chỉ cần mua vé)
                    // - Nếu có giảm giá: validation sẽ được xử lý ở phần applies_to
                    
                    // Xử lý logic tặng quà: nếu không có discount_percentage và có gift_only
                    $hasDiscount = !empty($rule['discount_percentage']);
                    $isGiftOnly = isset($rule['gift_only']) && $rule['gift_only'];
                    
                    if (!$hasDiscount && $isGiftOnly) {
                        // Trường hợp chỉ tặng quà (không giảm giá)
                        // Nếu có combo được chọn → tặng quà cho những combo đó
                        // Nếu không có combo nào → chỉ cần mua vé là được tặng
                        if (!empty($rule['requires_combo_ids']) && is_array($rule['requires_combo_ids'])) {
                            // Có combo được chọn → lưu vào requires_combo_ids
                            $data['discount_rules'][$index]['requires_combo_ids'] = $rule['requires_combo_ids'];
                        } else {
                            // Không có combo nào → set null (chỉ cần mua vé)
                            $data['discount_rules'][$index]['requires_combo_ids'] = null;
                        }
                    } else {
                        // Trường hợp có giảm giá hoặc không phải gift_only
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
            ->with('success', 'Promotion has been created successfully.');
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(Promotion $promotion)
    {
        $movies = Movie::orderBy('title')->get(['id', 'title']);
        $combos = Combo::active()->visible()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

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
                ->withErrors(['movie_id' => 'Please select a movie when category is Movie.'])
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
                    // Không validate requires_combo_ids nữa vì:
                    // - Nếu chỉ tặng quà (không giảm giá): có thể không chọn combo (chỉ cần mua vé)
                    // - Nếu có giảm giá: validation sẽ được xử lý ở phần applies_to
                    
                    // Xử lý logic tặng quà: nếu không có discount_percentage và có gift_only
                    $hasDiscount = !empty($rule['discount_percentage']);
                    $isGiftOnly = isset($rule['gift_only']) && $rule['gift_only'];
                    
                    if (!$hasDiscount && $isGiftOnly) {
                        // Trường hợp chỉ tặng quà (không giảm giá)
                        // Nếu có combo được chọn → tặng quà cho những combo đó
                        // Nếu không có combo nào → chỉ cần mua vé là được tặng
                        if (!empty($rule['requires_combo_ids']) && is_array($rule['requires_combo_ids'])) {
                            // Có combo được chọn → lưu vào requires_combo_ids
                            $data['discount_rules'][$index]['requires_combo_ids'] = $rule['requires_combo_ids'];
                        } else {
                            // Không có combo nào → set null (chỉ cần mua vé)
                            $data['discount_rules'][$index]['requires_combo_ids'] = null;
                        }
                    } else {
                        // Trường hợp có giảm giá hoặc không phải gift_only
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
                                    ->withErrors(["discount_rules.{$index}.combo_ids" => 'Please select a combo from "Available Combos" when applying to "Combo Price".'])
                                    ->withInput();
                            }
                        }
                        
                        // Xử lý requires_combo_ids - nếu không có thì set null
                        if (!isset($rule['requires_combo_ids']) || empty($rule['requires_combo_ids'])) {
                            $data['discount_rules'][$index]['requires_combo_ids'] = null;
                        }
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
            ->with('success', 'Promotion has been updated successfully.');
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
                ->with('error', 'The event is currently active. It cannot be deleted, only hidden. The event has been hidden.');
        }
        
        // Nếu không đang hoạt động, cho phép xóa
        // Xóa ảnh khỏi storage
        if ($promotion->image_path) {
            Storage::disk('public')->delete($promotion->image_path);
        }

        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion has been deleted successfully.');
    }

    /**
     * Save apply rules (shared/exclusive) for promotions.
     */
    public function saveRules(Request $request)
    {
        try {
            $request->validate([
                'rules' => 'required|array',
                'rules.*' => 'required|string|in:shared,exclusive',
            ]);

            foreach ($request->rules as $promotionId => $applyType) {
                Promotion::where('id', $promotionId)->update(['apply_type' => $applyType]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Apply rules have been updated successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating apply rules: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating apply rules.'
            ], 500);
        }
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

            return response()->json(['success' => true, 'message' => 'Order has been updated successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating promotion order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the order.'
            ], 500);
        }
    }
}

