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
- **Modul Masa Depan**: Modul baru (misalnya `Inventory`, `Finance`, `Sales`) wajib mengikuti pola modularitas yang sama.

---

### 2. Standar Struktur Folder Per Layer

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── Core/            <-- Modul Core Controllers
│   │       └── V1/
│   │           └── Purchasing/  <-- Modul Purchasing Controllers
│   ├── Requests/
│   │   ├── Core/                <-- Form Requests Modul Core
│   │   └── Purchasing/          <-- Form Requests Modul Purchasing
│   └── Resources/
│       ├── Core/                <-- API Resources Modul Core
│       └── Purchasing/          <-- API Resources Modul Purchasing
├── Models/
│   ├── Core/                    <-- Eloquent Models Core
│   └── Purchasing/              <-- Eloquent Models Purchasing
├── Repositories/
│   ├── Core/                    <-- Database Queries & Filters Core
│   └── Purchasing/              <-- Database Queries & Filters Purchasing
└── Services/
    ├── Core/                    <-- Business Logic Core
    └── Purchasing/              <-- Business Logic Purchasing
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
