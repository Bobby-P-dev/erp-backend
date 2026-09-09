<?php

namespace App\Services\Core;

use App\Models\Core\DocumentNumberSequence;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DocumentNumberService
{
    /**
     * Generate the next document number for the given company, category, and period.
     *
     * @throws RuntimeException
     */
    public function generate(int $companyId, string $category, ?string $period = null): string
    {
        $period = $period ?? (string) date('Y');

        return DB::transaction(function () use ($companyId, $category, $period) {
            $sequence = DocumentNumberSequence::where('company_id', $companyId)
                ->where('category', $category)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                throw new RuntimeException(
                    "Document number sequence not found for category '{$category}', company ID {$companyId}, and period '{$period}'."
                );
            }

            if (! $sequence->is_active) {
                throw new RuntimeException(
                    "Document number sequence for category '{$category}', company ID {$companyId}, and period '{$period}' is inactive."
                );
            }

            $nextNumber = $sequence->current_number + 1;
            $sequence->current_number = $nextNumber;
            $sequence->save();

            $formattedNumber = str_pad(
                (string) $nextNumber,
                $sequence->number_length,
                '0',
                STR_PAD_LEFT
            );

            $format = $sequence->format ?: '{prefix}-{period}-{number}';

            return str_replace(
                ['{prefix}', '{period}', '{number}'],
                [$sequence->prefix, $sequence->period, $formattedNumber],
                $format
            );
        });
    }

    /**
     * Convenience helper to generate a Purchase Requisition document number.
     *
     * @throws RuntimeException
     */
    public function generatePurchaseRequisitionNumber(int $companyId, ?string $period = null): string
    {
        return $this->generate($companyId, 'PR', $period);
    }
}
