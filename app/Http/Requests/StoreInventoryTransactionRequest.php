<?php

namespace App\Http\Requests;

use App\Enums\InventoryReferenceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreInventoryTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_batch_id' => 'required|exists:product_batches,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => ['required', Rule::in(['IN', 'OUT', 'ADJUST'])],
            'quantity' => 'required|integer|min:1',
            'reference_type' => ['nullable', new Enum(InventoryReferenceType::class)],
            'reference_id' => 'nullable|integer',
        ];
    }
}
