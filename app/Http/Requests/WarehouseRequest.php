<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
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
        $warehouseId = $this->warehouse ? $this->warehouse->id : null;
        return [
            'name' => [
                $warehouseId ? 'sometimes' : 'required',
                'string',
                'max:255',
                // Rule Unique: Tên kho không được trùng
                // ->ignore($this->warehouse): Bỏ qua ID hiện tại khi đang Update
                Rule::unique('warehouses', 'name')->ignore($this->warehouse),
            ],
            'location' => [
                'nullable', // Có thể để trống
                'string',
                'max:255',
            ],
            'is_active' => [
                'boolean', // Phải là true/false hoặc 1/0
                'nullable',
            ],
        ];
    }

    // public function messages(): array
    // {
    //     return [

    //         // ======================
    //         // Name
    //         // ======================
    //         'name.required' => 'Warehouse name is required.',
    //         'name.string'   => 'Warehouse name must be a string.',
    //         'name.max'      => 'Warehouse name may not be greater than 255 characters.',
    //         'name.unique'   => 'This warehouse name already exists.',

    //         // ======================
    //         // Location
    //         // ======================
    //         'location.string' => 'Location must be a valid string.',
    //         'location.max'    => 'Location may not be greater than 255 characters.',

    //         // ======================
    //         // Status
    //         // ======================
    //         'is_active.boolean' => 'Status must be true or false.',
    //     ];
    // }
}
