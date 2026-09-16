<?php

declare(strict_types=1);

namespace App\Enums\Approval;

use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchaseRequisition;

enum ApprovalDocumentType: string
{
    case PurchaseRequisition = 'purchase_requisition';
    case PurchaseOrder = 'purchase_order';

    /**
     * Nama label ramah user untuk tampilan dropdown (Human-readable label).
     */
    public function label(): string
    {
        return match ($this) {
            self::PurchaseRequisition => 'Purchase Requisition (Pengajuan Pembelian)',
            self::PurchaseOrder => 'Purchase Order (Pesanan Pembelian)',
        };
    }

    /**
     * Nama modul bisnis induk dokumen.
     */
    public function module(): string
    {
        return match ($this) {
            self::PurchaseRequisition, self::PurchaseOrder => 'Purchasing',
        };
    }

    /**
     * Nama class Eloquent model terkait.
     *
     * @return class-string
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::PurchaseRequisition => PurchaseRequisition::class,
            self::PurchaseOrder => PurchaseOrder::class,
        };
    }

    /**
     * Menentukan apakah dokumen ini memiliki nilai finansial (amount / budget).
     * Jika true, form di frontend dapat menampilkan input Min/Max Amount.
     */
    public function hasAmount(): bool
    {
        return match ($this) {
            self::PurchaseRequisition, self::PurchaseOrder => true,
        };
    }

    /**
     * Konversi single enum ke format opsi dropdown frontend.
     *
     * @return array<string, mixed>
     */
    public function toOption(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'module' => $this->module(),
            'model_class' => $this->modelClass(),
            'has_amount' => $this->hasAmount(),
        ];
    }

    /**
     * Daftar semua opsi dokumen yang dikelompokkan berdasarkan nama modul.
     * Sangat cocok untuk komponen Dropdown OptGroup / Menu bertingkat di UI.
     *
     * @return array<int, array{module: string, items: array<int, array<string, mixed>>}>
     */
    public static function groupedOptions(): array
    {
        $grouped = [];

        foreach (self::cases() as $case) {
            $module = $case->module();
            if (! isset($grouped[$module])) {
                $grouped[$module] = [
                    'module' => $module,
                    'items' => [],
                ];
            }

            $grouped[$module]['items'][] = $case->toOption();
        }

        return array_values($grouped);
    }

    /**
     * Daftar flat opsi untuk select biasa tanpa grouping.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function flatOptions(): array
    {
        return array_map(static fn (self $case): array => $case->toOption(), self::cases());
    }

    /**
     * Menghasilkan peta Morph Map untuk Relation::morphMap().
     *
     * @return array<string, class-string>
     */
    public static function morphMap(): array
    {
        $map = [];
        foreach (self::cases() as $case) {
            $map[$case->value] = $case->modelClass();
        }

        return $map;
    }

    /**
     * Coba resolve enum baik dari slug/key (misal 'purchase_requisition')
     * ataupun dari fully-qualified class string (misal PurchaseRequisition::class).
     */
    public static function tryFromModelOrValue(string $value): ?self
    {
        $fromValue = self::tryFrom($value);
        if ($fromValue !== null) {
            return $fromValue;
        }

        foreach (self::cases() as $case) {
            if ($case->modelClass() === $value) {
                return $case;
            }
        }

        return null;
    }
}
