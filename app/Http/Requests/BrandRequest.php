<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
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
        // Lấy ID của brand từ route để loại trừ khi update
        // Laravel tự hiểu $this->brand là object Brand nhờ Route Model Binding
        $brandId = $this->brand ? $this->brand->id : null;

        // dump($brandId);

        return [
            'name' => [
                $brandId ? 'sometimes' : 'required',
                'string',
                'max:255',
                // Rule unique linh hoạt:
                // Nếu có brandId (Update) -> bỏ qua ID đó
                // Nếu không có (Store) -> check toàn bộ
                'unique:brands,name,' . $brandId,
            ],
            'country' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
