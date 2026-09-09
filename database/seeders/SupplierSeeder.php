<?php

namespace Database\Seeders;

use App\Models\Core\Company;
use App\Models\Core\File;
use App\Models\Purchasing\Supplier;
use App\Models\Purchasing\SupplierBankAccount;
use App\Models\Purchasing\SupplierContact;
use App\Models\Purchasing\SupplierDocument;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::where('code', 'PSI')->first() ?? Company::first();
        $adminUser = User::whereNotNull('employee_id')->first() ?? User::first();

        $suppliersData = [
            [
                'supplier_code' => 'SUP-001',
                'name' => 'PT Krakatau Steel Tbk',
                'supplier_type' => 'Manufacturer',
                'bussines_type' => 'PT',
                'company_category' => 'Raw Material',
                'bussines_field' => 'Metal & Steel',
                'address' => 'Jl. Industri No. 5, Kawasan Industri Krakatau',
                'city' => 'Cilegon',
                'region' => 'Banten',
                'postal_code' => '42435',
                'country' => 'Indonesia',
                'phone' => '0254-392159',
                'email' => 'sales@krakatausteel.com',
                'tax_id' => '01.000.513.0-051.000',
                'payment_term' => 'Net 60',
                'lead_time_days' => 14,
                'contacts' => [
                    [
                        'name' => 'Bambang Wijaya',
                        'email' => 'bambang.w@krakatausteel.com',
                        'phone' => '081122334455',
                        'title' => 'Sales Manager',
                        'is_primary' => true,
                    ],
                    [
                        'name' => 'Dewi Lestari',
                        'email' => 'finance@krakatausteel.com',
                        'phone' => '081122334456',
                        'title' => 'Finance Staff',
                        'is_primary' => false,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'Bank Mandiri',
                        'bank_account_number' => '123-00-0987654-1',
                        'bank_account_name' => 'PT Krakatau Steel Tbk',
                        'branch' => 'KC Cilegon',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-NPWP-SUP-001',
                    'document_type' => 'NPWP',
                    'file_name' => 'NPWP_Krakatau_Steel.pdf',
                ],
            ],
            [
                'supplier_code' => 'SUP-002',
                'name' => 'PT Chandra Asri Petrochemical Tbk',
                'supplier_type' => 'Manufacturer',
                'bussines_type' => 'PT',
                'company_category' => 'Raw Material',
                'bussines_field' => 'Chemical & Petrochemical',
                'address' => 'Jl. Raya Anyer Km. 123, Ciwandan',
                'city' => 'Cilegon',
                'region' => 'Banten',
                'postal_code' => '42447',
                'country' => 'Indonesia',
                'phone' => '0254-601501',
                'email' => 'commercial@chandra-asri.com',
                'tax_id' => '01.001.234.5-052.000',
                'payment_term' => 'Net 45',
                'lead_time_days' => 10,
                'contacts' => [
                    [
                        'name' => 'Hendra Setiawan',
                        'email' => 'hendra.s@chandra-asri.com',
                        'phone' => '081234567890',
                        'title' => 'Key Account Manager',
                        'is_primary' => true,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'BCA',
                        'bank_account_number' => '873-512-3490',
                        'bank_account_name' => 'PT Chandra Asri Petrochemical',
                        'branch' => 'KCU Cilegon',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-NIB-SUP-002',
                    'document_type' => 'NIB',
                    'file_name' => 'NIB_Chandra_Asri.pdf',
                ],
            ],
            [
                'supplier_code' => 'SUP-003',
                'name' => 'PT Tsubaki Indonesia Trading',
                'supplier_type' => 'Distributor',
                'bussines_type' => 'PT',
                'company_category' => 'Sparepart',
                'bussines_field' => 'Industrial Chain & Power Transmission',
                'address' => 'Wisma 46 Kota BNI Lt. 15, Jl. Jend. Sudirman Kav. 1',
                'city' => 'Jakarta Pusat',
                'region' => 'DKI Jakarta',
                'postal_code' => '10220',
                'country' => 'Indonesia',
                'phone' => '021-5744230',
                'email' => 'info@tsubaki.co.id',
                'tax_id' => '02.456.789.1-011.000',
                'payment_term' => 'Net 30',
                'lead_time_days' => 7,
                'contacts' => [
                    [
                        'name' => 'Siti Rahmawati',
                        'email' => 'siti.r@tsubaki.co.id',
                        'phone' => '081398765432',
                        'title' => 'Sales Executive',
                        'is_primary' => true,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'Bank Danamon',
                        'bank_account_number' => '003-567-8910',
                        'bank_account_name' => 'PT Tsubaki Indonesia Trading',
                        'branch' => 'KC Sudirman',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-SIUP-SUP-003',
                    'document_type' => 'SIUP',
                    'file_name' => 'SIUP_Tsubaki_Indonesia.pdf',
                ],
            ],
            [
                'supplier_code' => 'SUP-004',
                'name' => 'PT Misumi Indonesia',
                'supplier_type' => 'Distributor',
                'bussines_type' => 'PT',
                'company_category' => 'Tooling & Sparepart',
                'bussines_field' => 'Factory Automation & Mechanical Components',
                'address' => 'Delta Silicon Industrial Park, Jl. Meranti Blok L3 No. 1',
                'city' => 'Bekasi',
                'region' => 'Jawa Barat',
                'postal_code' => '17550',
                'country' => 'Indonesia',
                'phone' => '021-89907777',
                'email' => 'cs@misumi.co.id',
                'tax_id' => '01.789.654.3-413.000',
                'payment_term' => 'Net 30',
                'lead_time_days' => 5,
                'contacts' => [
                    [
                        'name' => 'Dedi Pratama',
                        'email' => 'dedi.p@misumi.co.id',
                        'phone' => '081512349876',
                        'title' => 'Technical Support & Sales',
                        'is_primary' => true,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'BCA',
                        'bank_account_number' => '542-019-2831',
                        'bank_account_name' => 'PT Misumi Indonesia',
                        'branch' => 'KCU Cikarang',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-NPWP-SUP-004',
                    'document_type' => 'NPWP',
                    'file_name' => 'NPWP_Misumi_Indonesia.pdf',
                ],
            ],
            [
                'supplier_code' => 'SUP-005',
                'name' => 'PT Aneka Gas Industri Tbk',
                'supplier_type' => 'Manufacturer',
                'bussines_type' => 'PT',
                'company_category' => 'Consumable',
                'bussines_field' => 'Industrial Gases',
                'address' => 'Gedung Samator Lt. 8, Jl. Kedung Baruk No. 25-28',
                'city' => 'Surabaya',
                'region' => 'Jawa Timur',
                'postal_code' => '60298',
                'country' => 'Indonesia',
                'phone' => '031-8706858',
                'email' => 'industrial@samator.com',
                'tax_id' => '01.002.567.8-092.000',
                'payment_term' => 'Net 30',
                'lead_time_days' => 3,
                'contacts' => [
                    [
                        'name' => 'Agus Riyanto',
                        'email' => 'agus.riyanto@samator.com',
                        'phone' => '081288990011',
                        'title' => 'Distribution Officer',
                        'is_primary' => true,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'Bank BRI',
                        'bank_account_number' => '0341-01-002345-50-8',
                        'bank_account_name' => 'PT Aneka Gas Industri Tbk',
                        'branch' => 'KC Rungkut',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-ISO-SUP-005',
                    'document_type' => 'Sertifikat ISO',
                    'file_name' => 'ISO_Samator_Gas.pdf',
                ],
            ],
            [
                'supplier_code' => 'SUP-006',
                'name' => 'PT DNP Indonesia',
                'supplier_type' => 'Manufacturer',
                'bussines_type' => 'PT',
                'company_category' => 'Packaging',
                'bussines_field' => 'Printing & Packaging Solutions',
                'address' => 'Jl. Pulogadung No. 16, Kawasan Industri Pulogadung',
                'city' => 'Jakarta Timur',
                'region' => 'DKI Jakarta',
                'postal_code' => '13920',
                'country' => 'Indonesia',
                'phone' => '021-4603030',
                'email' => 'order@dnp.co.id',
                'tax_id' => '01.321.654.9-003.000',
                'payment_term' => 'Net 45',
                'lead_time_days' => 14,
                'contacts' => [
                    [
                        'name' => 'Maya Anggraini',
                        'email' => 'maya.a@dnp.co.id',
                        'phone' => '081765432109',
                        'title' => 'Customer Service Lead',
                        'is_primary' => true,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'Bank CIMB Niaga',
                        'bank_account_number' => '800-12-98765-00',
                        'bank_account_name' => 'PT DNP Indonesia',
                        'branch' => 'KC Pulogadung',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-NPWP-SUP-006',
                    'document_type' => 'NPWP',
                    'file_name' => 'NPWP_DNP_Indonesia.pdf',
                ],
            ],
            [
                'supplier_code' => 'SUP-007',
                'name' => 'CV Baut Pratama Sentosa',
                'supplier_type' => 'Distributor',
                'bussines_type' => 'CV',
                'company_category' => 'Hardware & Fasteners',
                'bussines_field' => 'Bolts, Nuts & Screws',
                'address' => 'Ruko Daan Mogot Permai Blok B No. 12',
                'city' => 'Tangerang',
                'region' => 'Banten',
                'postal_code' => '15118',
                'country' => 'Indonesia',
                'phone' => '021-5523412',
                'email' => 'sales@bautpratama.com',
                'tax_id' => '03.888.999.0-416.000',
                'payment_term' => 'COD',
                'lead_time_days' => 2,
                'contacts' => [
                    [
                        'name' => 'Rudy Gunawan',
                        'email' => 'rudy@bautpratama.com',
                        'phone' => '081809876543',
                        'title' => 'Owner & Head of Sales',
                        'is_primary' => true,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'BCA',
                        'bank_account_number' => '604-123-9900',
                        'bank_account_name' => 'CV Baut Pratama Sentosa',
                        'branch' => 'KCU Tangerang',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-NIB-SUP-007',
                    'document_type' => 'NIB',
                    'file_name' => 'NIB_Baut_Pratama.pdf',
                ],
            ],
            [
                'supplier_code' => 'SUP-008',
                'name' => 'PT Omron Electronics Components Indonesia',
                'supplier_type' => 'Manufacturer',
                'bussines_type' => 'PT',
                'company_category' => 'Electrical & Electronics',
                'bussines_field' => 'Relays, Sensors & Switches',
                'address' => 'EJIP Industrial Park Plot 5C, Cikarang Selatan',
                'city' => 'Bekasi',
                'region' => 'Jawa Barat',
                'postal_code' => '17550',
                'country' => 'Indonesia',
                'phone' => '021-8970111',
                'email' => 'sales-ecb@omron.co.id',
                'tax_id' => '01.555.444.3-431.000',
                'payment_term' => 'Net 60',
                'lead_time_days' => 21,
                'contacts' => [
                    [
                        'name' => 'Faisal Tanjung',
                        'email' => 'faisal.t@omron.co.id',
                        'phone' => '081299887766',
                        'title' => 'Industrial Sales Engineer',
                        'is_primary' => true,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'Bank BTPN (SMBC)',
                        'bank_account_number' => '100-293-8472',
                        'bank_account_name' => 'PT Omron Electronics Components',
                        'branch' => 'KC Cikarang',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-SIUP-SUP-008',
                    'document_type' => 'SIUP',
                    'file_name' => 'SIUP_Omron_Electronics.pdf',
                ],
            ],
            [
                'supplier_code' => 'SUP-009',
                'name' => 'PT Dupont Indonesia',
                'supplier_type' => 'Distributor',
                'bussines_type' => 'PT',
                'company_category' => 'Chemical & Polymers',
                'bussines_field' => 'High Performance Plastics & Resins',
                'address' => 'Beltway Office Park Tower B Lt. 5, Jl. TB Simatupang No. 41',
                'city' => 'Jakarta Selatan',
                'region' => 'DKI Jakarta',
                'postal_code' => '12550',
                'country' => 'Indonesia',
                'phone' => '021-7822555',
                'email' => 'id.sales@dupont.com',
                'tax_id' => '01.123.456.7-018.000',
                'payment_term' => 'Net 45',
                'lead_time_days' => 12,
                'contacts' => [
                    [
                        'name' => 'Lestari Utami',
                        'email' => 'lestari.u@dupont.com',
                        'phone' => '08111223344',
                        'title' => 'Polymer Product Specialist',
                        'is_primary' => true,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'Bank Permata',
                        'bank_account_number' => '710-234-5678',
                        'bank_account_name' => 'PT Dupont Indonesia',
                        'branch' => 'KC Simatupang',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-NPWP-SUP-009',
                    'document_type' => 'NPWP',
                    'file_name' => 'NPWP_Dupont_Indonesia.pdf',
                ],
            ],
            [
                'supplier_code' => 'SUP-010',
                'name' => 'PT United Tractors Pandu Engineering',
                'supplier_type' => 'Service Provider & Fabricator',
                'bussines_type' => 'PT',
                'company_category' => 'Machinery & Fabrication',
                'bussines_field' => 'Engineering, Tooling & Custom Parts',
                'address' => 'Jl. Jababeka XI Blok H30-40, Kawasan Industri Jababeka',
                'city' => 'Bekasi',
                'region' => 'Jawa Barat',
                'postal_code' => '17530',
                'country' => 'Indonesia',
                'phone' => '021-8935010',
                'email' => 'patria.sales@patria.co.id',
                'tax_id' => '01.234.567.8-413.000',
                'payment_term' => 'Net 30',
                'lead_time_days' => 18,
                'contacts' => [
                    [
                        'name' => 'Wahyu Hidayat',
                        'email' => 'wahyu.h@patria.co.id',
                        'phone' => '081345678901',
                        'title' => 'Engineering Project Manager',
                        'is_primary' => true,
                    ],
                ],
                'bank_accounts' => [
                    [
                        'bank_name' => 'Bank Mandiri',
                        'bank_account_number' => '156-00-1122334-4',
                        'bank_account_name' => 'PT United Tractors Pandu Engineering',
                        'branch' => 'KC Jababeka',
                        'is_primary' => true,
                    ],
                ],
                'document' => [
                    'document_number' => 'DOC-NIB-SUP-010',
                    'document_type' => 'NIB',
                    'file_name' => 'NIB_PATRIA_Engineering.pdf',
                ],
            ],
        ];

        foreach ($suppliersData as $data) {
            // 1. Create or update Supplier
            $supplier = Supplier::updateOrCreate(
                ['supplier_code' => $data['supplier_code']],
                [
                    'company_id' => $company?->id,
                    'name' => $data['name'],
                    'supplier_type' => $data['supplier_type'],
                    'bussines_type' => $data['bussines_type'],
                    'company_category' => $data['company_category'],
                    'bussines_field' => $data['bussines_field'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'region' => $data['region'],
                    'postal_code' => $data['postal_code'],
                    'country' => $data['country'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'tax_id' => $data['tax_id'],
                    'payment_term' => $data['payment_term'],
                    'lead_time_days' => $data['lead_time_days'],
                    'approval_status' => 'approved',
                    'approved_by' => $adminUser?->id,
                    'approved_at' => now(),
                    'is_active' => true,
                ]
            );

            // 2. Create or update User account for Supplier (allows login via email)
            User::updateOrCreate(
                ['supplier_id' => $supplier->id],
                [
                    'account_type' => 'supplier',
                    'password' => Hash::make('password123'),
                ]
            );

            // 3. Create or update Contacts
            foreach ($data['contacts'] as $contact) {
                SupplierContact::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'email' => $contact['email'],
                    ],
                    [
                        'name' => $contact['name'],
                        'phone' => $contact['phone'],
                        'title' => $contact['title'],
                        'is_primary' => $contact['is_primary'],
                    ]
                );
            }

            // 4. Create or update Bank Accounts
            foreach ($data['bank_accounts'] as $bank) {
                SupplierBankAccount::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'bank_account_number' => $bank['bank_account_number'],
                    ],
                    [
                        'bank_name' => $bank['bank_name'],
                        'bank_account_name' => $bank['bank_account_name'],
                        'branch' => $bank['branch'],
                        'is_primary' => $bank['is_primary'],
                        'is_active' => true,
                    ]
                );
            }

            // 5. Create or update File and SupplierDocument
            if (isset($data['document']) && $adminUser) {
                $doc = $data['document'];
                $file = File::updateOrCreate(
                    [
                        'stored_name' => 'docs/suppliers/'.$data['supplier_code'].'_'.$doc['file_name'],
                    ],
                    [
                        'original_name' => $doc['file_name'],
                        'file_path' => 'uploads/suppliers/'.$doc['file_name'],
                        'file_type' => 'document',
                        'file_size' => '204800',
                        'file_extension' => 'pdf',
                        'file_mime_type' => 'application/pdf',
                        'disk' => 'local',
                        'uploaded_by' => $adminUser->id,
                    ]
                );

                SupplierDocument::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'document_number' => $doc['document_number'],
                    ],
                    [
                        'file_id' => $file->id,
                        'document_type' => $doc['document_type'],
                        'issue_date' => now()->subMonths(6)->toDateString(),
                        'expiry_date' => now()->addYears(2)->toDateString(),
                        'is_active' => true,
                        'is_verified' => true,
                        'verified_by' => $adminUser->id,
                        'verified_at' => now(),
                        'notes' => 'Verified by Procurement / Legal Team',
                    ]
                );
            }
        }
    }
}
