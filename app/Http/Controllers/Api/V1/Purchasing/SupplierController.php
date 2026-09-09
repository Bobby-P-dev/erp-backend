<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Purchasing\SupplierResource;
use App\Models\Purchasing\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Search active suppliers returning id and name.
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $query = Supplier::query()
            ->select(['id', 'name'])
            ->where('is_active', true);

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('supplier_code', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('name', 'asc')->limit(5)->get();

        return SupplierResource::collection($data)->additional([
            'message' => 'Supplier search results fetched successfully',
        ])->response()->setStatusCode(200);
    }
}
