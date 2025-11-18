<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'manufacturer' => $this->manufacturer,
            // keep price as string to preserve decimal precision from the DB
            'price' => $this->whenNotNull(isset($this->price) ? (string) $this->price : null),
            'capacity' => $this->whenNotNull($this->capacity ?? null),
            'connector_type' => $this->whenNotNull($this->connector_type ?? null),
            'power_output' => $this->whenNotNull($this->power_output ?? null),
            'description' => $this->whenNotNull($this->description ?? null),
        ];
    }
}
