<?php

namespace App\Http\Requests;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;

class ProductDataRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', 'in:'.implode(',', ProductType::values())],
            'q' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'price_from' => ['nullable', 'numeric', 'gte:0'],
            'price_to' => ['nullable', 'numeric', 'gt:0'],
            'capacity_from' => ['nullable', 'numeric', 'gte:0'],
            'capacity_to' => ['nullable', 'numeric', 'gt:0'],
            'power_from' => ['nullable', 'numeric', 'gte:0'],
            'power_to' => ['nullable', 'numeric', 'gt:0'],
            'connector_type' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('type')) {
            $this->merge(['type' => ProductType::BATTERIES->value]);
        }
    }

    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($v) {
            $data = $this->all();
            $this->validatePriceRange($v, $data);
            $this->validateCapacityRange($v, $data);
            $this->validatePowerRange($v, $data);
        });
    }

    private function validatePriceRange($validator, array $data): void
    {
        $from = isset($data['price_from']) && $data['price_from'] !== '' ? (float) $data['price_from'] : null;
        $to = isset($data['price_to']) && $data['price_to'] !== '' ? (float) $data['price_to'] : null;

        if ($from !== null && $from < 0) {
            $validator->errors()->add('price_from', 'The price from must be at least 0.');
        }
        if ($to !== null && $to <= 0) {
            $validator->errors()->add('price_to', 'The price to must be greater than 0.');
        }
        if ($from !== null && $to !== null && $from > $to) {
            $validator->errors()->add('price_from', 'The price from must be less than or equal to price to.');
        }
    }

    private function validateCapacityRange($validator, array $data): void
    {
        $cFrom = isset($data['capacity_from']) && $data['capacity_from'] !== '' ? (float) $data['capacity_from'] : null;
        $cTo = isset($data['capacity_to']) && $data['capacity_to'] !== '' ? (float) $data['capacity_to'] : null;
        if ($cFrom !== null && $cFrom < 0) {
            $validator->errors()->add('capacity_from', 'The capacity from must be at least 0.');
        }
        if ($cTo !== null && $cTo <= 0) {
            $validator->errors()->add('capacity_to', 'The capacity to must be greater than 0.');
        }
        if ($cFrom !== null && $cTo !== null && $cFrom > $cTo) {
            $validator->errors()->add('capacity_from', 'The capacity from must be less than or equal to capacity to.');
        }
    }

    private function validatePowerRange($validator, array $data): void
    {
        $pFrom = isset($data['power_from']) && $data['power_from'] !== '' ? (float) $data['power_from'] : null;
        $pTo = isset($data['power_to']) && $data['power_to'] !== '' ? (float) $data['power_to'] : null;
        if ($pFrom !== null && $pFrom < 0) {
            $validator->errors()->add('power_from', 'The power from must be at least 0.');
        }
        if ($pTo !== null && $pTo <= 0) {
            $validator->errors()->add('power_to', 'The power to must be greater than 0.');
        }
        if ($pFrom !== null && $pTo !== null && $pFrom > $pTo) {
            $validator->errors()->add('power_from', 'The power from must be less than or equal to power to.');
        }
    }
}
