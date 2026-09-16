<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Approval;

use App\Enums\Approval\ApprovalDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Approval\ApprovalDocumentTypeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalDocumentTypeController extends Controller
{
    /**
     * Dapatkan daftar dokumen yang mendukung alur approval untuk dropdown frontend.
     *
     * @queryParam grouped boolean Mengelompokkan dokumen berdasarkan nama modul (default: true).
     */
    public function index(Request $request): JsonResponse
    {
        $grouped = filter_var($request->query('grouped', 'true'), FILTER_VALIDATE_BOOLEAN);

        if ($grouped) {
            $data = ApprovalDocumentType::groupedOptions();

            return response()->json([
                'message' => 'Approval document types retrieved successfully',
                'data' => $data,
            ], 200);
        }

        $data = ApprovalDocumentTypeResource::collection(ApprovalDocumentType::cases());

        return response()->json([
            'message' => 'Approval document types retrieved successfully',
            'data' => $data,
        ], 200);
    }
}
