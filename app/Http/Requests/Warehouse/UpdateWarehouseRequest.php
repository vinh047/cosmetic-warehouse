<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy model warehouse đang được sửa từ Route
        $warehouse = $this->route('warehouse');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                // Bỏ qua ID kho hiện tại
                Rule::unique('warehouses', 'name')->ignore($warehouse->id),
            ],
            'location'  => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}