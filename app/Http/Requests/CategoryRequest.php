<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // update
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 1. Lấy ID của category từ URL (nếu đang Update)
        // Lưu ý: Nếu route là Route::apiResource('categories', ...), tham số mặc định là 'category'
        $category = $this->route('category');
        $categoryId = $category ? $category->id : null;

        return [
            'name' => [
                // Logic: 
                // - STORE ($categoryId null): Bắt buộc (required)
                // - UPDATE ($categoryId có): Có thể không gửi (sometimes), nhưng nếu gửi thì phải check
                $categoryId ? 'sometimes' : 'required',
                'string',
                'max:255',
                // Kiểm tra trùng tên, nhưng bỏ qua chính nó (ignore) nếu đang update
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],

            'description' => [
                'nullable', // Cho phép gửi null hoặc không gửi
                'string',
                'max:1000', // Nên giới hạn độ dài text để tránh spam DB
            ],

            'is_active' => [
                'sometimes',
                'boolean', // Chấp nhận: true, false, 1, 0, "1", "0"
            ],
        ];
    }
}
