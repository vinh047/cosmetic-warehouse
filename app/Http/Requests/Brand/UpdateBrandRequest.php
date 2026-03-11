<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy ID của brand đang được sửa từ Route Model Binding
        // Cách chuẩn nhất trong FormRequest của Laravel là dùng $this->route('tên_param')
        $brandId = $this->route('brand')->id; 

        return [
            'name'      => 'sometimes|string|max:255|unique:brands,name,' . $brandId,
            'country'   => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ];
    }
}