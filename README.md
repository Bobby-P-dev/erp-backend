# ERP Backend System

A modular monolith ERP Backend application built with **Laravel 11** on **PHP 8.3**.

---

## 🏛️ Arsitektur: Clean Code & Modular Monolith

Aplikasi ini mengadopsi prinsip **Clean Code** dan arsitektur **Modular Monolith**, di mana setiap modul bisnis dipisahkan secara tegas ke dalam folder domain masing-masing pada setiap layer.

### 1. Pembagian Modul Domain
- **`Core`**: Berperan sebagai fondasi dan *Master Data Utama* sistem perusahaan:
  - Organisasi & Perusahaan: `Company`, `Division`, `Position`, `JobLevel`
  - Pengguna & Otorisasi: `Employee`, `User`, `Role`, `Permission`, `PermissionCategory`
  - Finansial & Akuntansi: `AccountingCategory`, `AccountingSubcategory`, `AccountingAccount`
- **`Purchasing`**: Menangani operasional pengadaan barang dan manajemen mitra:
  - Transaksi Pengadaan: `PurchaseRequisition`, `PurchaseRequisitionItem`
  - Manajemen Pemasok: `Supplier`, `SupplierContact`, `SupplierBankAccount`, `SupplierDocument`
  - Katalog Mitra: `SupplierItem`
- **`Approval`**: Dynamic Multi-Tier Approval Engine universal lintas modul:
  - Konfigurasi & Step: `ApprovalConfiguration`, `ApprovalConfigurationLevel`
  - Transaksi & Histori: `ApprovalRequest`, `ApprovalRequestLevel`, `ApprovalAction`
- **Modul Masa Depan**: Modul baru (misalnya `Inventory`, `Finance`, `Sales`) wajib mengikuti pola modularitas yang sama.

---

### 2. Standar Struktur Folder Per Layer

```text
app/
├── Contracts/
│   └── Approval/            <-- Interfaces & Kontrak (misal: Approvable)
├── Enums/
│   └── Approval/            <-- Enums Status, Scopes, Actions, DocumentTypes
├── Events/
│   └── Approval/            <-- Domain Lifecycle Events
├── Exceptions/
│   └── Approval/            <-- Custom Domain Exceptions
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── Core/        <-- Modul Core Controllers
│   │       └── V1/
│   │           ├── Approval/    <-- Modul Approval Controllers
│   │           └── Purchasing/  <-- Modul Purchasing Controllers
│   ├── Requests/
│   │   ├── Core/            <-- Form Requests Modul Core
│   │   └── Purchasing/      <-- Form Requests Modul Purchasing
│   └── Resources/
│       ├── Approval/        <-- API Resources Modul Approval
│       ├── Core/            <-- API Resources Modul Core
│       └── Purchasing/      <-- API Resources Modul Purchasing
├── Models/
│   ├── Approval/            <-- Eloquent Models Approval (termasuk Concerns/)
│   ├── Core/                <-- Eloquent Models Core
│   └── Purchasing/          <-- Eloquent Models Purchasing
├── Repositories/
│   ├── Core/                <-- Database Queries & Filters Core
│   └── Purchasing/          <-- Database Queries & Filters Purchasing
└── Services/
    ├── ApprovalService.php  <-- Approval Engine Orchestration
    ├── Core/                <-- Business Logic Core
    └── Purchasing/          <-- Business Logic Purchasing
```

---

## 🔄 Pola Alur: Repository & Service

### A. Repository Pattern (Wajib untuk Query Database)
- Semua pemanggilan query Eloquent/DB (`where`, `join`, `orderBy`, `paginate`, `with`) **WAJIB** berada di dalam layer `Repository`.
- Controller dilarang melakukan query database langsung yang kompleks.

### B. Service Pattern (Khusus Business Logic)
Gunakan `Service` jika terdapat salah satu kondisi berikut:
1. Terdapat logika bisnis yang berat atau kalkulasi harga/pajak.
2. Melibatkan operasi multi-step atau `DB::transaction` lintas tabel.
3. Auto-generation nomor dokumen atau kode unik (misal `PR-2026-000001`, `SUP-0001`).
4. Auto-reset state data (misal: saat kontak/rekening baru ditandai `is_primary = true`, kontak lama di-reset ke `false`).

*Catatan:* Jangan membuat Service kosong yang hanya menjadi perantara tanpa logika (*empty passthrough proxy*).

### C. Alur yang Diizinkan Sesuai Kebutuhan
1. **`Controller -> Repository`**
   - Digunakan untuk: CRUD standar, paginasi filter sederhana, dan pengambilan data langsung tanpa logika bisnis kompleks.
   - *Contoh:* `AccountingCategoryController`, `AccountingAccountController`.
2. **`Controller -> Service -> Repository`**
   - Digunakan untuk: Fitur yang memiliki aturan bisnis, validasi lintas data, orkestrasi relasi, atau auto-reset.
   - *Contoh:* `SupplierController`, `SupplierContactController`, `PurchaseRequisitionController`.
3. **`Controller -> Service`**
   - Digunakan untuk: Fitur atau utilitas tanpa penyimpanan database langsung, integrasi API pihak ketiga, atau perhitungan matematis murni.
   - *Contoh:* `DocumentNumberService`.

---

## 📋 Aturan Request & Response API

### 1. Form Request Validation
- **Wajib menggunakan Form Request dedicated** jika jumlah field input request **> 4 parameter**, atau jika aturan validasinya rumit (misalnya *custom validation rule*, regex, conditional rules, atau pengecualian ID pada unique rule).
- **Inline validation (`$request->validate(...)`)** hanya diperkenankan untuk request kecil yang `<= 4 parameter` sederhana.
- **Konvensi Penamaan:** Gunakan pola `{Action}{Model}Request`:
  - *Benar:* `StoreSupplierRequest`, `UpdateSupplierRequest`, `StoreEmployeeRequest`
  - *Hindari:* `EmployeeStoreRequest` (tidak konsisten)

### 2. API Response & Eloquent Resources
- **Setiap endpoint API WAJIB menggunakan API Resource**. Dilarang mengembalikan raw Model Eloquent atau array associative langsung.
- Resource ditempatkan pada folder modul yang relevan: `App\Http\Resources\{Module}\{Model}Resource`.
- Seluruh response JSON harus konsisten menyediakan key `message` dan `data` (serta `meta` untuk pagination).

### 3. Dependency Injection (PHP 8.3)
- Wajib menggunakan **Constructor Property Promotion**:
  ```php
  public function __construct(
      protected SupplierService $service
  ) {}
  ```
- **Dilarang** melakukan instansiasi manual dengan operator `new` di dalam controller (misal: `$this->repo = new SupplierRepository;`).

---

---

## ⚡ Dynamic Multi-Tier Approval Engine

Sistem ini dilengkapi dengan **Dynamic Multi-Tier Approval Engine** universal yang dirancang fleksibel untuk menangani alur persetujuan di seluruh modul ERP (Purchasing, HR, Sales, Inventory, Finance) tanpa perlu mengubah skema database engine.

### 1. Karakteristik Utama Engine
- **Universal & Polimorfik**: Dapat disambungkan ke model dokumen manapun via relasi polimorfik Laravel (`approvable`).
- **Mendukung Alur Finansial & Non-Finansial**: Kolom `min_amount` dan `max_amount` bersifat *nullable*. Dokumen non-finansial (seperti Cuti atau Mutasi Barang) dievaluasi berbasis hierarki organisasi.
- **Snapshot Data Terintegrasi**: `approval_requests` secara otomatis menyimpan snapshot `document_number` (misal nomor PR/PO) dan `document_title` (peruntukan pengajuan) untuk kemudahan audit dan pencarian tanpa join berlebih.
- **Deadlock-Free Concurrency**: Menerapkan urutan penguncian transaksi yang konsisten: `Approvable -> ApprovalRequest -> ApprovalRequestLevel`.
- **Maker-Checker Security**: Pembuat dokumen secara default dilarang menyetujui pengajuannya sendiri.
- **Siklus Resubmit Revisi**: Pengajuan dokumen yang diminta revisi akan me-reset status `Revision` kembali ke `Pending` dan mengulang alur tanpa membuat ID approval baru.

---

### 2. Cara Mendaftarkan Dokumen Baru ke Engine (Developer Guide)

Untuk menghubungkan dokumen baru (misal: `SalesOrder` atau `LeaveRequest`) ke sistem Approval:

#### Langkah 1: Implementasikan Contract `Approvable` & Trait `HasApprovals`
```php
namespace App\Models\Sales;

use App\Contracts\Approval\Approvable;
use App\Models\Approval\Concerns\HasApprovals;
use App\Models\Approval\ApprovalRequest;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model implements Approvable
{
    use HasApprovals;

    public function getApprovalCompanyId(): ?int
    {
        return $this->company_id;
    }

    public function getApprovalDivisionId(): ?int
    {
        return $this->division_id;
    }

    public function getApprovalTotalAmount(): ?float
    {
        return (float) $this->total_amount; // Kembalikan null jika dokumen non-finansial
    }

    public function getApprovalDocumentNumber(): ?string
    {
        return $this->so_number;
    }

    public function getApprovalDocumentTitle(): ?string
    {
        return $this->customer_name . ' - ' . $this->project_name;
    }

    public function onApprovalApproved(ApprovalRequest $request): void
    {
        $this->update(['status' => 'approved']);
    }

    public function onApprovalRejected(ApprovalRequest $request, ?string $reason): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function onApprovalRevisionRequested(ApprovalRequest $request, ?string $reason): void
    {
        $this->update(['status' => 'revision_needed']);
    }
}
```

#### Langkah 2: Daftarkan ke Enum `ApprovalDocumentType`
Cukup tambahkan 1 baris kasus pada [app/Enums/Approval/ApprovalDocumentType.php](file:///var/www/erp-backend/app/Enums/Approval/ApprovalDocumentType.php):
```php
case SalesOrder = 'sales_order';
```
Dan lengkapi method `label()`, `module()`, `modelClass()`, serta `hasAmount()`.

**Hasilnya:**
1. Laravel Morph Map otomatis terdaftar secara terpusat di [AppServiceProvider.php](file:///var/www/erp-backend/app/Providers/AppServiceProvider.php).
2. Endpoint API dropdown frontend otomatis menyediakan opsi dokumen tersebut.
3. Database tetap bersih dengan menyimpan morph slug (`sales_order`).

---

### 3. Struktur 5 Tabel Inti Engine

| Tabel | Fungsi |
| :--- | :--- |
| `approval_configurations` | Master template alur persetujuan per tipe dokumen, company, dan range nominal |
| `approval_configuration_levels` | Master tingkatan step persetujuan berurutan, approver scope, mode, dan kondisi dinamis |
| `approval_requests` | Transaksi pengajuan persetujuan aktif untuk record dokumen tertentu |
| `approval_request_levels` | Instance langkah persetujuan aktif beserta status per level (Pending, Approved, Skipped) |
| `approval_actions` | Jejak audit log riwayat tindakan approver (Approve, Reject, Revision, Reassign) |

---

### 4. Skope Approver & Mode Persetujuan

#### Approver Scope (`ApproverScope`)
- **`role_only`**: Siapapun yang memiliki role tertentu (misal: *Manager Purchasing*).
- **`role_and_division`**: Role tertentu yang berada di divisi yang sama dengan pemohon (misal: *Supervisor Produksi*).
- **`job_level_and_division`**: Job Level tertentu di divisi pemohon (misal: *Manager* di divisi pemohon).
- **`department_head`**: Pejabat Kepala Divisi / Departemen pemohon (`employees.is_department_head = true`).
- **`specific_user`**: Pengguna spesifik yang ditunjuk langsung.

#### Approval Mode (`ApprovalMode`)
- **`any`**: Cukup 1 orang approver yang menyetujui, level langsung selesai (*first-to-approve*).
- **`all`**: Seluruh approver yang memenuhi kualifikasi pada level tersebut wajib menyetujui.

---

### 5. API Endpoint untuk Frontend

#### Mengambil Daftar Pilihan Modul/Dokumen untuk Dropdown
Frontend memanggil endpoint berikut saat Admin membuat/mengedit konfigurasi approval:

```http
GET /api/v1/approval-configurations/document-types
Authorization: Bearer <token>
```

**Query Parameters:**
- `grouped=true` *(default)*: Menghasilkan data yang dikelompokkan per modul (cocok untuk tag `<optgroup>`).
- `grouped=false`: Menghasilkan flat list (cocok untuk komponen select biasa atau search autocomplete).

**Contoh Response (`grouped=true`):**
```json
{
  "message": "Approval document types retrieved successfully",
  "data": [
    {
      "module": "Purchasing",
      "items": [
        {
          "value": "purchase_requisition",
          "label": "Purchase Requisition (Pengajuan Pembelian)",
          "module": "Purchasing",
          "model_class": "App\\Models\\Purchasing\\PurchaseRequisition",
          "has_amount": true
        },
        {
          "value": "purchase_order",
          "label": "Purchase Order (Pesanan Pembelian)",
          "module": "Purchasing",
          "model_class": "App\\Models\\Purchasing\\PurchaseOrder",
          "has_amount": true
        }
      ]
    }
  ]
}
```

---

## 🧪 Panduan Testing

Aplikasi ini menggunakan **PHPUnit** dengan penegakan uji fitur (*Feature Tests*) untuk setiap endpoint:
```bash
# Menjalankan seluruh test suite
php artisan test --compact

# Menjalankan test per modul
php artisan test tests/Feature/Core --compact
php artisan test tests/Feature/Purchasing --compact
```

---

## 🎨 Code Style & Formatting

Format kode distandarisasi menggunakan **Laravel Pint**:
```bash
vendor/bin/pint --format agent
```
