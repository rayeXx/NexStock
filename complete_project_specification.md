# Spesifikasi Lengkap & Dokumentasi Teknis Aplikasi NexStock

Dokumen ini menyajikan rangkuman komprehensif seluruh arsitektur, skema basis data, aturan hak akses (RBAC), algoritma bisnis (FEFO & kapasitas rak), detail visual UI, serta panduan deployment aplikasi **NexStock - Inventory & Logistics Management System** (berbasis Laravel 11).

---

## 🛠️ 1. Arsitektur & Lingkungan Teknologi

Aplikasi NexStock dibangun dengan arsitektur **Model-View-Controller (MVC)** standar Laravel 11:
*   **Backend Framework**: Laravel 11.x, PHP 8.2+
*   **Basis Data**:
    *   **Pengembangan & Unit Testing**: SQLite (`database/database.sqlite` / in-memory `:memory:`)
    *   **Docker Container**: MySQL 8.0
*   **Frontend**: Vanilla HTML5, Javascript, Vanilla CSS (Premium Glassmorphism), Chart.js (Grafik Penjualan), Select2 (Pencarian dropdown dinamis).
*   **Keamanan**:
    *   **Enkripsi Data**: Atribut `harga_beli` pada model `Product` disimpan dalam bentuk terenkripsi (AES-256) menggunakan Laravel Casts untuk menjamin kerahasiaan harga modal dari query SQL langsung.

---

## 👥 2. Matriks Hak Akses / Otorisasi Role-Based Access Control (RBAC)

NexStock membagi hak akses ke dalam 3 peran utama dengan wewenang yang tegas untuk menjaga kepatuhan operasional gudang:

| Fitur / Modul | Peran Owner | Peran Admin Gudang (`admin_gudang`) | Peran Staff Gudang (`staff_gudang`) |
| :--- | :--- | :--- | :--- |
| **Dashboard** | Analitik Penjualan, Tren Produk Terlaris/Slow, AI Insights, KPI Nilai Aset (Penuh). | Ringkasan Operasional (SKU, ROP Kritis, In/Out Hari Ini). | Ringkasan Operasional (SKU, ROP Kritis, In/Out Hari Ini). |
| **Kelola Operator** | Read-Only (Tidak bisa menambah/mengubah). | Akses Penuh (Kecuali membuat, mengedit, atau menghapus akun Owner). | Tidak Ada Akses (`403 Forbidden`). |
| **Master Data (Produk, Supplier, Rak)** | Read-Only. | Akses Penuh (Tambah, Edit, Hapus). | Tidak Ada Akses (`403 Forbidden`). |
| **Purchase Order (PO)** | Read-Only. | Membuat PO Baru (Status: Draft/Ordered). | Tidak Ada Akses (`403 Forbidden`). |
| **Penerimaan Barang (Inbound)** | Tidak Ada Akses (`403 Forbidden`). | Tidak Ada Akses (`403 Forbidden`). | Melakukan Inbound (Penerimaan PO, logging kondisi barang datang, penentuan rak, & retur). |
| **Barang Keluar (Outbound)** | Tidak Ada Akses (`403 Forbidden`). | Tidak Ada Akses (`403 Forbidden`). | Memproses Barang Keluar (Pengeluaran stok otomatis menggunakan FEFO). |
| **Karantina Barang Rusak** | Read-Only. | Menyetujui (`Approved`) & Menolak (`Rejected`) Laporan Barang Rusak. | Mengajukan Laporan Barang Rusak (Status default: *Pending*). |
| **Stock Opname (Audit)** | Menyetujui / Mensahkan Audit. | Menyetujui / Mensahkan Audit. | Melakukan rekonsiliasi fisik & penginputan kuantitas fisik. |
| **Pengajuan Restock** | Tidak Ada Akses. | Meninjau & menyetujui pengajuan restock dari staf. | Mengajukan restock barang yang menipis. |

---

## 🗄️ 3. Skema & Relasi Database (Skema Tabel)

### A. Tabel Master Data

#### `users` (Manajemen Akun)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `name` (VARCHAR)
*   `email` (VARCHAR, Unique)
*   `password` (VARCHAR)
*   `role` (ENUM: `owner`, `admin_gudang`, `staff_gudang`)
*   `created_at` & `updated_at` (TIMESTAMP)

#### `m_categories` (Kategori Produk)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `nama_kategori` (VARCHAR)
*   `catatan` (TEXT, Nullable)

#### `m_products` (Daftar SKU Produk)
*   `kode_produk` (VARCHAR, Primary Key)
*   `nama_produk` (VARCHAR)
*   `kategori_id` (BIGINT, Foreign Key -> `m_categories.id`)
*   `harga_beli` (TEXT, Encrypted AES-256)
*   `stok_minimum` (INTEGER)
*   `uom` (VARCHAR) - *Satuan unit, contoh: Pcs, Pack, Dus*
*   `satuan_beli` (VARCHAR) - *Satuan pembelian dari supplier, contoh: Dus, Pack*
*   `satuan_jual` (VARCHAR) - *Satuan penjualan ritel/internal, contoh: Pcs, Pack*
*   `rasio_konversi` (INTEGER) - *Rasio jumlah satuan_jual dalam satu satuan_beli*

#### `m_racks` (Lokasi Penyimpanan Rak)
*   `kode_rak` (VARCHAR, Primary Key)
*   `kapasitas_maksimum_volume` (INTEGER) - *Kapasitas maksimum unit*
*   `kapasitas_terpakai` (INTEGER, Default: 0) - *Jumlah unit tersimpan saat ini*

#### `m_suppliers` (Daftar Supplier Mitra)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `nama_supplier` (VARCHAR)
*   `kontak` (VARCHAR)

---

### B. Tabel Transaksi Pengadaan & Persediaan

#### `t_purchase_orders` (Dokumen Purchase Order)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `po_number` (VARCHAR, Unique)
*   `supplier_id` (BIGINT, Foreign Key -> `m_suppliers.id`)
*   `status` (ENUM: `Draft`, `Ordered`, `Partially Received`, `Completed`)
*   `total_harga` (DECIMAL)
*   `created_by` (BIGINT, Foreign Key -> `users.id`)

#### `t_purchase_order_details` (Detail Item PO)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `po_id` (BIGINT, Foreign Key -> `t_purchase_orders.id`)
*   `produk_id` (VARCHAR, Foreign Key -> `m_products.kode_produk`)
*   `qty_pesan` (INTEGER)
*   `qty_diterima` (INTEGER, Default: 0)

#### `t_batch_inbounds` (Penyimpanan Berbasis Batch - FEFO Core)
*   `batch_number` (VARCHAR, Primary Key) - *Contoh: BTC-PO1-NUGGET*
*   `batch_supplier` (VARCHAR, Nullable) - *Nomor batch pabrik*
*   `produk_id` (VARCHAR, Foreign Key -> `m_products.kode_produk`)
*   `po_id` (BIGINT, Foreign Key -> `t_purchase_orders.id`, Nullable)
*   `rak_id` (VARCHAR, Foreign Key -> `m_racks.kode_rak`)
*   `expired_date` (DATE)
*   `stok_awal_batch` (INTEGER)
*   `stok_sisa_batch` (INTEGER) - *Stok siap jual yang tersedia*

#### `t_po_receiving_history` (Riwayat Pengiriman PO & Retur Masuk)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `po_id` (BIGINT, Foreign key)
*   `produk_id` (VARCHAR, Foreign key)
*   `qty_datang` (INTEGER) - *Jumlah fisik tiba*
*   `qty_rusak` (INTEGER) - *Kuantitas cacat*
*   `qty_received` (INTEGER) - *Kuantitas baik yang masuk ke rak*
*   `kondisi_barang` (VARCHAR) - *Contoh: Baik, Rusak*
*   `batch_number` (VARCHAR)
*   `batch_supplier` (VARCHAR)
*   `expired_date` (DATE)
*   `rak_id` (VARCHAR)
*   `status_retur` (ENUM: `Normal`, `Menunggu Retur`, `Sudah Diretur`)
*   `tanggal_retur` (TIMESTAMP, Nullable)
*   `catatan_retur` (TEXT, Nullable)
*   `received_at` (TIMESTAMP)
*   `received_by` (BIGINT)

---

### C. Tabel Transaksi Pengeluaran, Karantina & Audit

#### `t_outbounds` (Dokumen Barang Keluar)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `outbound_number` (VARCHAR, Unique)
*   `tujuan` (VARCHAR) - *Contoh: Hypermart, Supermarket Prima*
*   `tanggal_keluar` (TIMESTAMP)

#### `t_outbound_details` (Detail Pengeluaran Stok)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `outbound_id` (BIGINT, Foreign Key -> `t_outbounds.id`)
*   `produk_id` (VARCHAR, Foreign Key -> `m_products.kode_produk`)
*   `batch_number` (VARCHAR, Foreign Key -> `t_batch_inbounds.batch_number`)
*   `qty_keluar` (INTEGER)
*   `rak_id` (VARCHAR, Foreign Key -> `m_racks.kode_rak`, Nullable)

#### `t_damaged_reports` (Karantina Barang Rusak)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `produk_id` (VARCHAR, Foreign Key -> `m_products.kode_produk`)
*   `batch_number` (VARCHAR, Foreign Key -> `t_batch_inbounds.batch_number`)
*   `rak_id` (VARCHAR, Foreign Key -> `m_racks.kode_rak`)
*   `qty_rusak` (INTEGER)
*   `foto_bukti` (VARCHAR, Nullable)
*   `alasan` (TEXT)
*   `status` (ENUM: `Pending`, `Approved`, `Rejected`)
*   `created_by` (BIGINT, Foreign Key -> `users.id`)

#### `t_stock_opnames` (Audit Fisik Dokumen)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `tanggal_opname` (TIMESTAMP)
*   `created_by` (BIGINT, Foreign Key -> `users.id`)
*   `status` (VARCHAR, Default: 'Pending Approval')
*   `approved_by` (BIGINT, Foreign Key -> `users.id`, Nullable)
*   `approved_at` (TIMESTAMP, Nullable)

#### `t_stock_opname_details` (Hasil Rekonsiliasi Item Audit)
*   `id` (BIGINT, Primary Key, Auto Increment)
*   `stock_opname_id` (BIGINT, Foreign Key -> `t_stock_opnames.id`)
*   `produk_id` (VARCHAR, Foreign Key)
*   `batch_number` (VARCHAR, Foreign Key)
*   `qty_sistem` (INTEGER) - *Stok tercatat pada aplikasi*
*   `qty_fisik` (INTEGER) - *Stok riil di rak*
*   `selisih` (INTEGER) - *Formula: qty_fisik - qty_sistem*
*   `catatan` (TEXT, Nullable)

#### `t_restock_requests` (Pengajuan Restock Operator)
*   `id` (BIGINT, Primary key, Auto Increment)
*   `produk_id` (VARCHAR)
*   `qty_request` (INTEGER)
*   `status` (ENUM: `Pending`, `Approved`, `Rejected`)
*   `created_by` (BIGINT)
*   `notes` (TEXT, Nullable)
*   `updated_by` (BIGINT, Nullable)

---

## ⚙️ 4. Logika Bisnis & Algoritma Utama

### A. Algoritma FEFO (First Expired, First Out)
Ketika Staff Gudang memproses transaksi barang keluar (`OutboundController@store`), sistem mengambil stok dari batch yang memiliki tanggal kedaluwarsa paling dekat (`expired_date` terkecil) terlebih dahulu.

#### Alur Kerja FEFO:
1.  Sistem mencari seluruh batch aktif dari produk terkait (`t_batch_inbounds`) yang memiliki `stok_sisa_batch > 0` dan mengurutkannya secara menaik (`ASC`) berdasarkan `expired_date`.
2.  Jika jumlah kebutuhan pengeluaran $Q$ dapat dicukupi oleh batch pertama, stok langsung dipotong dari batch tersebut.
3.  Jika batch pertama tidak cukup, sistem menghabiskan sisa stok batch pertama, lalu beralih ke batch berikutnya secara berurutan (*split transaction*) hingga jumlah kebutuhan $Q$ terpenuhi.
4.  Kapasitas rak terpakai (`kapasitas_terpakai` pada `m_racks`) dikurangi secara real-time sebesar kuantitas yang dikeluarkan dari rak tersebut.
5.  Jika total stok seluruh batch tidak mencukupi, transaksi dibatalkan (`db::rollBack`) dan melempar pesan error.

---

### B. Mekanisme Karantina Laporan Barang Rusak
Untuk memastikan keakuratan stok siap jual, barang rusak diisolasi seketika:
1.  **Pengajuan**: Saat Staff membuat laporan barang rusak (`t_damaged_reports`), sistem **seketika mengurangi** `stok_sisa_batch` pada batch terkait agar tidak terjual via FEFO. Namun, `kapasitas_terpakai` rak asal **tidak dikurangi** karena fisik barang masih menumpuk di rak menunggu verifikasi.
2.  **Persetujuan (Approved)**: Admin Gudang menyetujui laporan. Sistem memotong `kapasitas_terpakai` rak asal secara real-time karena barang rusak tersebut resmi dibuang atau dikeluarkan dari gudang.
3.  **Penolakan (Rejected)**: Admin Gudang menolak laporan. Sistem mengembalikan kuantitas barang rusak ke `stok_sisa_batch`. Kapasitas rak tidak diubah karena tidak pernah dipotong saat pengajuan.

---

### C. Alokasi Kapasitas Rak Dinamis
Setiap rak memiliki batas maksimum unit. Penambahan stok (Inbound) dan pengurangan stok (Outbound/Damaged/Opname) secara konsisten memperbarui nilai kapasitas terpakai rak:
*   `Inbound` $\rightarrow$ `kapasitas_terpakai` bertambah.
*   `Outbound` $\rightarrow$ `kapasitas_terpakai` berkurang.
*   `Damaged Report` $\rightarrow$ `kapasitas_terpakai` berkurang (setelah disetujui) / tidak berubah (jika ditolak).
*   `Stock Opname` $\rightarrow$ `kapasitas_terpakai` disesuaikan setelah disahkan oleh Owner / Admin.

---

### D. Konversi Satuan UOM (Satuan Beli ke Satuan Jual)
Sistem membedakan antara satuan pemesanan/pembelian supplier (`satuan_beli`) dan satuan stok gudang/penjualan ritel (`satuan_jual`).
*   **Penerimaan Barang (Inbound)**: Jumlah barang yang diterima dihitung dengan mengalikan kuantitas dalam `satuan_beli` dengan `rasio_konversi` sebelum disimpan ke stok sisa batch (`t_batch_inbounds`) dan kapasitas rak terpakai (`kapasitas_terpakai`).
*   **Pembelian (Purchase Order)**: Data kuantitas pemesanan (`qty_pesan`) dan penerimaan (`qty_diterima`) pada PO tetap disimpan dalam bentuk `satuan_beli` asal.

---

## 🎨 5. Spesifikasi Desain & Estetika Visual Premium

NexStock menerapkan sistem antarmuka **Premium Dark Glassmorphism Layout** untuk memukau pengguna sejak pandangan pertama:
1.  **Full-Width Layout**: Seluruh container (`.main-content`) menggunakan `max-width: 100%` agar area kerja membentang secara luas pada layar monitor besar, mencegah ruang kosong tak berguna.
2.  **Glassmorphism Cards**: Elemen card (`.glass-card`) menggunakan background semitransparan berpendar (`rgba(30, 41, 59, 0.65)`), border tipis, efek blur (`backdrop-filter: blur(16px)`), serta bayangan menyebar (`box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2)`).
3.  **Efek Glow & Hover**:
    *   Kartu dashboard bersinar redup (`border-color: rgba(56, 189, 248, 0.25)`) dan terangkat naik (`translateY(-2px)`) saat disorot kursor.
    *   Menu navigasi sidebar memiliki efek geser horizontal (`padding-left: 1.25rem`) saat di-hover.
4.  **Animasi Mount Halaman**:
    *   Menerapkan animasi masuk memudar dan meluncur (`fadeInUp` & `slideInLeft`) menggunakan kurva bezier fluid (`cubic-bezier(0.16, 1, 0.3, 1)`).
    *   Mengimplementasikan penundaan bertahap (*staggered animation delay*) pada kartu dashboard agar tampil berurutan secara estetik saat dimuat.
5.  **Live Search Master Table**: Input pencarian real-time berbasis JavaScript (sisi klien) pada tabel supplier dan operator untuk penyaringan data instan tanpa kedipan reload halaman.
6.  **Grafik Interaktif Dinamis**: Grafik batang Chart.js bergradien biru yang dibekali tombol filter rentang waktu: **Hari Ini, 1 Minggu, 1 Bulan, 3 Bulan, 6 Bulan, dan 1 Tahun**.

---

## 🚀 6. Panduan Menjalankan Aplikasi Secara Mandiri

### Opsi A: Menggunakan Docker Desktop (Rekomendasi & Praktis)
Pastikan Docker Desktop sudah aktif di komputer Anda, lalu jalankan perintah berikut di direktori proyek:

```powershell
# 1. Bangun dan jalankan kontainer di background
docker compose up -d --build

# 2. Jalankan migrasi basis data dan suntik data dummy frozen food
docker exec nexstock-app-container php artisan migrate:fresh --seed --no-interaction
```
*   Aplikasi web dapat diakses langsung pada port: `http://localhost:8080`
*   Database MySQL di dalam kontainer diekspos keluar pada port lokal: `3307`
*   Kredensial Demo Masuk (Quick Login tersedia di halaman masuk):
    *   **Owner**: `owner@nexstock.com` (password: `password`)
    *   **Admin**: `admin@nexstock.com` (password: `password`)
    *   **Staff**: `staff@nexstock.com` (password: `password`)

---

### Opsi B: Menjalankan Secara Manual (Local PHP & SQLite)
Jika ingin menjalankan tanpa kontainer, pastikan PHP 8.2 dan SQLite sudah terinstal di komputer lokal:

```powershell
# 1. Salin konfigurasi env dan instalasi dependensi (jika belum)
cp .env.example .env
composer install

# 2. Jalankan migrasi fresh dan seeding data dummy distributor frozen food
php artisan migrate:fresh --seed

# 3. Jalankan server Laravel lokal
php artisan serve
```
*   Aplikasi web lokal dapat diakses pada port: `http://localhost:8000` (atau port default yang ditunjuk).

---

### C. Menjalankan Unit Testing Otomatis
Untuk memverifikasi keutuhan logika FEFO, pembatasan izin RBAC, serta alur approval barang rusak, jalankan perintah pengujian:

```powershell
php artisan test
```
*   Seluruh pengujian (**46/46 cases**) menggunakan SQLite in-memory untuk kecepatan maksimal dan harus berstatus **PASS (100% Lulus)**.
