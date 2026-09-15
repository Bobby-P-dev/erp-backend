<?php

namespace Database\Seeders;

use App\Models\Core\AccountingAccount;
use App\Models\Core\AccountingCategory;
use App\Models\Core\AccountingSubcategory;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            [
                'code' => '1',
                'name' => 'Aset',
                'description' => 'Aset dan seluruh sumber daya ekonomi perusahaan',
                'is_active' => true,
                'subcategories' => [
                    [
                        'code' => '11',
                        'name' => 'Aset Lancar',
                        'description' => 'Kas, bank, piutang usaha, dan persediaan operasional',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '1101', 'name' => 'Kas Kecil (Petty Cash Operasional)', 'description' => 'Dana kas tunai harian di pabrik dan kantor'],
                            ['code' => '1102', 'name' => 'Bank Mandiri Operasional', 'description' => 'Rekening giro penerimaan dan pengeluaran utama'],
                            ['code' => '1103', 'name' => 'Bank BCA Operasional & Payroll', 'description' => 'Rekening transaksi vendor dan payroll karyawan'],
                            ['code' => '1104', 'name' => 'Piutang Usaha', 'description' => 'Tagihan piutang dagang kepada pelanggan'],
                            ['code' => '1105', 'name' => 'Persediaan Bahan Baku (Raw Material)', 'description' => 'Stok bahan baku besi, baja, plastik, resin'],
                            ['code' => '1106', 'name' => 'Persediaan Barang Dalam Proses (WIP)', 'description' => 'Stok produk dalam proses perakitan dan machining'],
                            ['code' => '1107', 'name' => 'Persediaan Barang Jadi (Finished Goods)', 'description' => 'Stok produk manufaktur siap kirim ke customer'],
                            ['code' => '1108', 'name' => 'Persediaan Suku Cadang & Consumables', 'description' => 'Stok sparepart mesin, oli, baut, dan perlengkapan'],
                            ['code' => '1109', 'name' => 'Uang Muka Pembelian Supplier', 'description' => 'Down payment / uang muka PO pengadaan ke supplier'],
                            ['code' => '1110', 'name' => 'PPN Masukan', 'description' => 'Pajak Pertambahan Nilai masukan atas pembelian'],
                        ],
                    ],
                    [
                        'code' => '12',
                        'name' => 'Aset Tetap',
                        'description' => 'Properti, tanah, bangunan, mesin pabrik, dan kendaraan',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '1201', 'name' => 'Tanah & Lahan Kawasan Industri', 'description' => 'Tanah area pabrik dan pergudangan'],
                            ['code' => '1202', 'name' => 'Bangunan Pabrik & Kantor', 'description' => 'Struktur bangunan gedung pabrik, workshop, dan kantor'],
                            ['code' => '1203', 'name' => 'Mesin & Peralatan Produksi CNC / Stamping', 'description' => 'Mesin manufaktur, stamping press, bubut CNC'],
                            ['code' => '1204', 'name' => 'Kendaraan Operasional & Logistik', 'description' => 'Armada truk forklift, mobil operasional'],
                            ['code' => '1205', 'name' => 'Peralatan Kantor & Infrastruktur IT', 'description' => 'Komputer server, workstation, printer pabrik'],
                            ['code' => '1206', 'name' => 'Akumulasi Penyusutan Aset Tetap', 'description' => 'Akumulasi penyusutan aset berwujud'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '2',
                'name' => 'Kewajiban',
                'description' => 'Kewajiban jangka pendek dan kewajiban jangka panjang',
                'is_active' => true,
                'subcategories' => [
                    [
                        'code' => '21',
                        'name' => 'Kewajiban Jangka Pendek',
                        'description' => 'Utang usaha dagang, utang gaji, dan kewajiban pajak lancar',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '2101', 'name' => 'Utang Usaha - Pemasok Lokal', 'description' => 'Kewajiban pembayaran faktur ke vendor lokal'],
                            ['code' => '2102', 'name' => 'Utang Usaha - Pemasok Impor', 'description' => 'Kewajiban pembayaran material impor (LC/TT)'],
                            ['code' => '2103', 'name' => 'Beban Yang Masih Harus Dibayar (Accrued)', 'description' => 'Beban akrual utilitas listrik, air, dan subcon'],
                            ['code' => '2104', 'name' => 'Utang Gaji & Tunjangan Karyawan', 'description' => 'Kewajiban upah pekerja dan tunjangan belum cair'],
                            ['code' => '2105', 'name' => 'Utang PPN Keluaran', 'description' => 'PPN keluaran yang dipungut dari customer'],
                            ['code' => '2106', 'name' => 'Utang PPh Pasal 21 / 23 / 26', 'description' => 'Potongan pajak penghasilan karyawan dan vendor jasa'],
                            ['code' => '2107', 'name' => 'Uang Muka Penjualan Customer', 'description' => 'DP penjualan dari pelanggan atas Sales Order'],
                        ],
                    ],
                    [
                        'code' => '22',
                        'name' => 'Kewajiban Jangka Panjang',
                        'description' => 'Pinjaman bank jangka panjang dan leasing pembiayaan mesin',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '2201', 'name' => 'Utang Bank Investasi Jangka Panjang', 'description' => 'Pinjaman modal kerja dan investasi bank'],
                            ['code' => '2202', 'name' => 'Utang Sewa Pembiayaan (Leasing)', 'description' => 'Kewajiban leasing mesin pabrik jangka panjang'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '3',
                'name' => 'Ekuitas',
                'description' => 'Modal saham disetor, cadangan, dan saldo laba',
                'is_active' => true,
                'subcategories' => [
                    [
                        'code' => '31',
                        'name' => 'Modal Saham',
                        'description' => 'Modal saham ditempatkan dan disetor penuh',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '3101', 'name' => 'Modal Saham Disetor', 'description' => 'Modal disetor para pemegang saham'],
                            ['code' => '3102', 'name' => 'Tambahan Modal Disetor (Agio Saham)', 'description' => 'Selisih lebih setoran modal atas nilai nominal'],
                        ],
                    ],
                    [
                        'code' => '32',
                        'name' => 'Saldo Laba',
                        'description' => 'Akumulasi laba ditahan dan hasil usaha periode berjalan',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '3201', 'name' => 'Saldo Laba Ditahan (Retained Earnings)', 'description' => 'Akumulasi sisa laba tahun-tahun sebelumnya'],
                            ['code' => '3202', 'name' => 'Laba / Rugi Tahun Berjalan', 'description' => 'Laba bersih operasional tahun berjalan'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '4',
                'name' => 'Pendapatan',
                'description' => 'Pendapatan penjualan manufaktur dan pendapatan lain-lain',
                'is_active' => true,
                'subcategories' => [
                    [
                        'code' => '41',
                        'name' => 'Pendapatan Operasional',
                        'description' => 'Penjualan produk manufaktur komponen presisi dan suku cadang',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '4101', 'name' => 'Penjualan Komponen Presisi Otomotif', 'description' => 'Pendapatan utama penjualan parts ke tier-1 OEM'],
                            ['code' => '4102', 'name' => 'Penjualan Komponen Elektronik & Industri', 'description' => 'Pendapatan penjualan parts industri umum'],
                            ['code' => '4103', 'name' => 'Diskon & Potongan Harga Penjualan', 'description' => 'Potongan volume atau pembayaran cepat pelanggan'],
                            ['code' => '4104', 'name' => 'Retur Penjualan', 'description' => 'Pengembalian barang reject / cacat dari pelanggan'],
                        ],
                    ],
                    [
                        'code' => '42',
                        'name' => 'Pendapatan Non-Operasional',
                        'description' => 'Pendapatan di luar operasi manufaktur utama',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '4201', 'name' => 'Pendapatan Bunga Bank & Jasa Giro', 'description' => 'Bunga rekening bank operasional'],
                            ['code' => '4202', 'name' => 'Keuntungan Selisih Kurs Mata Uang Asing', 'description' => 'Keuntungan fluktuasi valas USD/JPY/EUR'],
                            ['code' => '4203', 'name' => 'Pendapatan Penjualan Scrap / Limbah Logam', 'description' => 'Hasil penjualan sisa potongan plat dan gram bubut'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '5',
                'name' => 'Beban',
                'description' => 'Beban pokok penjualan, biaya pabrikasi overhead, dan operasional SG&A',
                'is_active' => true,
                'subcategories' => [
                    [
                        'code' => '51',
                        'name' => 'Beban Pokok Penjualan (HPP)',
                        'description' => 'Biaya bahan baku langsung, tenaga kerja langsung, dan overhead produksi',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '5101', 'name' => 'Biaya Pemakaian Bahan Baku Besi & Logam', 'description' => 'Material besi, baja lembaran, stainless'],
                            ['code' => '5102', 'name' => 'Biaya Pemakaian Bahan Baku Plastik & Kimia', 'description' => 'Resin biji plastik dan bahan aditif'],
                            ['code' => '5103', 'name' => 'Upah Tenaga Kerja Langsung Operator', 'description' => 'Gaji dan lembur operator mesin produksi'],
                            ['code' => '5104', 'name' => 'Biaya Jasa Maklon / Subkontrak Luar', 'description' => 'Biaya perlakuan panas (heat treatment), plating, coating'],
                            ['code' => '5105', 'name' => 'Biaya Bahan Pembantu & Pelumas Mesin', 'description' => 'Cutting oil, cairan coolant, majun, gas argon'],
                            ['code' => '5106', 'name' => 'Biaya Pengemasan & Box Karton', 'description' => 'Pallet kayu, bubble wrap, kardus kemasan'],
                        ],
                    ],
                    [
                        'code' => '52',
                        'name' => 'Beban Overhead Pabrik (FOH)',
                        'description' => 'Biaya tidak langsung pemeliharaan pabrik, listrik, dan mesin',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '5201', 'name' => 'Biaya Listrik PLN Industri Pabrik', 'description' => 'Tagihan listrik gardu pabrik tegangan menengah'],
                            ['code' => '5202', 'name' => 'Biaya Pemeliharaan Mesin & Kalibrasi', 'description' => 'Servis berkala mesin CNC dan kalibrasi presisi'],
                            ['code' => '5203', 'name' => 'Biaya Penggantian Suku Cadang Mesin', 'description' => 'Beli sparepart belt, bearing, pneumatic'],
                            ['code' => '5204', 'name' => 'Biaya Penyusutan Gedung & Mesin Pabrik', 'description' => 'Penyusutan bulanan fasilitas pabrik'],
                            ['code' => '5205', 'name' => 'Biaya Alat Pelindung Diri (K3 / APD)', 'description' => 'Safety shoes, kacamata safety, masker pabrik'],
                        ],
                    ],
                    [
                        'code' => '53',
                        'name' => 'Beban Operasional & Administrasi (SG&A)',
                        'description' => 'Beban kantor manajemen, IT, legal, dan logistik pemasaran',
                        'is_active' => true,
                        'accounts' => [
                            ['code' => '5301', 'name' => 'Gaji Staf Kantor & Manajemen Purchasing', 'description' => 'Gaji tim purchasing, accounting, HR, dan manajemen'],
                            ['code' => '5302', 'name' => 'Beban Internet, Server ERP & Software License', 'description' => 'Langganan software CAD, hosting cloud, bandwidth internet'],
                            ['code' => '5303', 'name' => 'Beban Pengiriman Logistik & Ekspedisi', 'description' => 'Biaya kirim barang ke customer'],
                            ['code' => '5304', 'name' => 'Beban Alat Tulis Kantor (ATK) & Konsumsi', 'description' => 'Kertas, perlengkapan kantor, konsumsi meeting'],
                            ['code' => '5305', 'name' => 'Beban Legal, Notaris & Konsultan Audit', 'description' => 'Jasa kantor akuntan publik dan legalitas'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($categoriesData as $catData) {
            $category = AccountingCategory::updateOrCreate(
                ['code' => $catData['code']],
                [
                    'name' => $catData['name'],
                    'description' => $catData['description'],
                    'is_active' => $catData['is_active'],
                ]
            );

            foreach ($catData['subcategories'] as $subcatData) {
                $subcategory = AccountingSubcategory::updateOrCreate(
                    [
                        'accounting_category_id' => $category->id,
                        'code' => $subcatData['code'],
                    ],
                    [
                        'name' => $subcatData['name'],
                        'description' => $subcatData['description'],
                        'is_active' => $subcatData['is_active'],
                    ]
                );

                foreach ($subcatData['accounts'] as $accData) {
                    AccountingAccount::updateOrCreate(
                        [
                            'accounting_subcategory_id' => $subcategory->id,
                            'code' => $accData['code'],
                        ],
                        [
                            'name' => $accData['name'],
                            'description' => $accData['description'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
