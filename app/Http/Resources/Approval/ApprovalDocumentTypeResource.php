<?php

declare(strict_types=1);

namespace App\Http\Resources\Approval;

use App\Enums\Approval\ApprovalDocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ApprovalDocumentType|array<string, mixed> $resource
 */
class ApprovalDocumentTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof ApprovalDocumentType) {
            return $this->resource->toOption();
        }

        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [];
    }
}
