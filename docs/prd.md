# PRODUCT REQUIREMENTS DOCUMENT
## NEXSTOCK - Inventory & Logistics Management Information System

**Aplikasi Sistem Informasi Inventaris dan Logistik Terintegrasi Berbasis Web Responsive untuk UMKM Distributor Makanan dan Minuman.**

---

* **Version**: v1.0 - Final Draft
* **Date**: 25 Juni 2026
* **Team**: Kelompok 9:
    1. Najwa Khairun Nisa - 24523263
    2. Rayhan Fadhlurrahman - 24523223
    3. Aliyan Pandhu Fadilanta - 24523044
    4. Harunsyah - 24523225
* **Product Owner**: Najwa Khairun Nisa
* **Client/Stakeholder**: Pemilik Usaha (Owner) / Manajemen Gudang UMKM Distributor Makanan & Minuman
* **Status**: Final Draft

---

## PART 1: PROBLEM, OBJECTIVES & SCOPE

### 1. Problem Statement
#### 1.1 Background & Context
Banyak UMKM distributor makanan dan minuman masih mengelola inventaris menggunakan pencatatan manual atau spreadsheet sederhana. Karakteristik utama industri makanan dan minuman (M&M) menuntut perputaran barang yang dinamis karena terikat oleh tanggal kedaluwarsa (*expired date*) dan nomor kelompok produksi (*batch number*). Penggunaan spreadsheet terpisah menyebabkan integrasi data antara transaksi gudang dan manajemen pengadaan menjadi terputus.

#### 1.2 Problem Statement
Pencatatan inventaris yang tidak terintegrasi memicu tingginya tingkat kesalahan pencatatan total stok siap jual, risiko kerugian akibat produk kedaluwarsa yang terlewat di area lorong rak gudang, serta keterlambatan dalam melakukan pesanan pengadaan ulang (*restock*). Selain itu, tidak adanya indikator penilaian objektif terhadap ketepatan pengiriman dari pemasok (*supplier*) menyulitkan penentuan mitra pengadaan terbaik bagi efisiensi logistik usaha.

#### 1.3 Who is Affected
* **Staff Gudang**: Kesulitan memetakan rotasi penempatan produk fisik secara optimal dan rentan melakukan kesalahan penginputan kuantitas data barang tanpa validasi sistem otomatis.
* **Admin Gudang**: Kesulitan mengontrol validitas administrasi pembuatan dokumen pemesanan (*Purchase Order*) dan melakukan penyesuaian stok secara manual satu per satu.
* **Owner (Pemilik Usaha)**: Mengalami kerugian modal akibat rusaknya produk di gudang serta hilangnya potensi penjualan akibat kehabisan persediaan siap jual secara tiba-tiba tanpa prediksi dini.

---

### 2. Objectives
#### 2.1 Business Objectives
| # | Objective | Why it matters | Success indicator |
| :--- | :--- | :--- | :--- |
| 1 | **Menjamin Akurasi Finansial & Stok** | Mencegah kerugian kapitalisasi akibat selisih barang dan rusaknya produk kedaluwarsa. | Data stok fisik vs database 98% cocok saat audit. |
| 2 | **Mengoptimalkan Kontrol Pengadaan** | Memastikan siklus pemesanan barang ke supplier terpantau secara transparan dan terstruktur. | Seluruh pemesanan wajib melalui modul *Approval PO* digital. |
| 3 | **Efisiensi Alur Logistik Pergudangan** | Memotong waktu pencarian dan penataan posisi tata letak barang secara sistemis. | Rekomendasi penempatan rak diterbitkan instan oleh sistem. |

#### 2.2 User Objectives
| Actor | What they need to accomplish | What stops them today |
| :--- | :--- | :--- |
| **Staff Gudang** | Mencatat mutasi penerimaan dan pengeluaran barang serta mengkarantina produk cacat dengan cepat dan presisi. | Penginputan manual pada form longgar rawan memicu salah ketik (*typo*) angka kuantitas massal. |
| **Admin Gudang** | Mengelola master data logistik (barang, supplier, rak, user) dan menerbitkan dokumen pengadaan resmi secara terpusat. | Data operasional tersebar di spreadsheet terpisah yang rentan terhapus atau tidak sinkron. |
| **Owner (Pemilik)** | Memantau grafik nilai total aset, mengesahkan *Purchase Order*, dan mendeteksi risiko tanggal kedaluwarsa secara proaktif. | Harus memeriksa gudang secara fisik atau menunggu laporan rekap bulanan yang sering terlambat. |

---

### 3. Success Metrics
| Metric | Baseline (now) | Target (3 months) | How it is measured |
| :--- | :--- | :--- | :--- |
| **Akurasi Pencatatan Stok** | Sering terjadi selisih pencatatan tanpa alasan jelas. | $\ge$ 98% Cocok | Hasil rekonsiliasi data *Stock Opname* berkala (Fisik Riil vs Aplikasi). |
| **Tingkat Produk Kedaluwarsa Terlewat** | Risiko produk kedaluwarsa tinggi karena rotasi barang acak. | 0% Produk kedaluwarsa lolos ke pelanggan | Implementasi otomasi metode FEFO (*First Expired, First Out*) pada sistem pengeluaran. |
| **Waktu Pemrosesan Restock** | Terlambat memesan ulang karena pemantauan manual. | Pemesanan dilakukan sebelum stok kosong | Pemanfaatan grafik *Restock Forecast* yang dikalkulasi otomatis dari data transaksi 30 hari terakhir. |

---

### 4. Scope
#### 4.1 In Scope & Out of Scope (MVP v1.0)
| IN Scope (MVP v1.0) | X OUT of Scope (v1.0) |
| :--- | :--- |
| **Master Data Terpusat**: Data Barang, Data Supplier, Data Rak dinamis, Data Kategori, dan Akun User (RBAC). | Integrasi otomatis dengan API E-commerce / Marketplace eksternal. |
| **Inventory Management**: Pencatatan Barang Masuk (*Inbound*), Barang Keluar (*Outbound*), *Stock Opname*, dan Karantina Barang Rusak. | Modul integrasi ERP skala besar atau akuntansi eksternal. |
| **Procurement Management**: Pembuatan draf *Purchase Order* (PO), sistem *Approval* PO oleh Owner, dan kalkulasi Persentase Performa Supplier. | Gerbang pembayaran digital (*Payment Gateway*) untuk pelunasan transaksi PO. |
| **Dashboard MIS & Smart Features**: Tampilan indikator KPI utama, *Smart Rack Recommendation*, *Restock Forecast*, *Expired Risk Detection*, dan *Error Detection*. | Pembuatan aplikasi native *mobile* (.apk / .ipa) untuk Playstore atau Appstore. |
| **Platform Accessibility**: Pengembangan sistem berbasis web responsif yang kompatibel diakses via Desktop maupun Browser Mobile. | Integrasi fungsional pelacakan posisi kurir menggunakan *GPS Tracking*. |
| | Fitur login via scan perangkat keras dan operasional berbasis *Barcode Scanner Hardware*. |

#### 4.2 Assumptions & Constraints
| Type | Description |
| :--- | :--- |
| **Assumption** | Perangkat komputer meja administrasi, koneksi internet stabil, dan *smartphone* operasional staf telah tersedia di lingkungan internal gudang distributor UMKM. |
| **Constraint** | Hak akses staf gudang dikunci untuk tidak dapat memanipulasi master data, mengubah harga beli produk, atau menyetujui dokumen pengadaan secara sepihak demi transparansi kerja. |

---

## PART 2: FUNCTIONAL REQUIREMENTS & WORKFLOWS

### 5. Functional Requirements
#### 5.1 FR Table: Staff Gudang (Operasional Lapangan)
| FR ID | Actor | The system shall... | Condition / Trigger | Priority | MoSCoW |
| :--- | :--- | :--- | :--- | :--- | :--- |
| FR-01 | Staff Gudang | Membuka halaman Barang Masuk dan memvalidasi kecocokan data pesanan berdasarkan referensi dokumen pembelian. | Saat Staff memasukkan Nomor PO berstatus `Approved`. | High | M |
| FR-02 | Staff Gudang | Memproses pengurangan stok otomatis dengan menerapkan kalkulasi urutan masa kedaluwarsa terdekat. | Saat Staff mengeksekusi transaksi pada halaman "Barang Keluar" (**FEFO System**). | High | M |
| FR-03 | Staff Gudang | Memindahkan status produk dari stok siap jual ke area isolasi sementara tanpa menghapus permanen data dasar. | Saat Staff mengirimkan entri data baru pada menu "Laporkan Barang Rusak". | High | M |
| FR-04 | Staff Gudang | Menyediakan kolom isian stok riil dan menyembunyikan display kuantitas stok versi sistem untuk menjaga objektifitas audit. | Saat Staff membuka menu dan melakukan agenda "Stock Opname". | High | M |

#### 5.2 FR Table: Admin Gudang (Administrasi Data)
| FR ID | Actor | The system shall... | Condition / Trigger | Priority | MoSCoW |
| :--- | :--- | :--- | :--- | :--- | :--- |
| FR-05 | Admin Gudang | Menyediakan form CRUD master produk lengkap beserta parameter Batas Minimum Stok dan pilihan Satuan Barang (*UoM*). | Saat Admin masuk ke halaman sub-menu "Manajemen Barang". | High | M |
| FR-06 | Admin Gudang | Menyediakan form CRUD pembuatan data akun operator lapangan baru untuk membagi wewenang akses login. | Saat Admin mengakses sub-menu khusus "Manajemen User". | High | M |
| FR-07 | Admin Gudang | Menyimpan rekaman transaksi pemesanan stok baru ke status *Draft* sebelum dikirimkan ke level otorisasi yang lebih tinggi. | Saat Admin mengklik tombol "Simpan PO" di menu *Procurement*. | High | M |

#### 5.3 FR Table: Owner (Manajerial & Pengambil Keputusan)
| FR ID | Actor | The system shall... | Condition / Trigger | Priority | MoSCoW |
| :--- | :--- | :--- | :--- | :--- | :--- |
| FR-08 | Owner | Menyediakan tombol eksekusi digital untuk mengubah status dokumen pembelian menjadi sah atau membatalkannya. | Saat Owner memeriksa daftar antrean menu "Approval Purchase Order". | High | M |
| FR-09 | Owner | Menampilkan notifikasi persetujuan pemusnahan atau pemulihan unit aset berdasarkan bukti visual dokumen terunggah. | Saat Owner membuka riwayat pengajuan di dalam menu "Approval Barang Rusak". | High | M |
| FR-10 | Owner | Menyajikan rangkuman grafik total nilai kapitalisasi seluruh stok gudang dan persentase ranking efisiensi pengiriman pemasok. | Saat Owner mengakses halaman utama "Dashboard MIS". | Medium | S |

---

### 6. User Workflows

#### 6.1 Workflow: [Penerimaan Barang Masuk Berbasis Validasi PO]
* **Actor**: Staff Gudang
* **Goal**: Mencatat barang datang, memverifikasi kesesuaian dokumen pembelian, dan menaruhnya di lokasi optimal sesuai petunjuk sistem.
* **FRs covered**: FR-01, FR-05
* **Ideal Path**:
    1. Staff Gudang membuka menu "Barang Masuk" lewat browser perangkat gudang.
    2. Staff memasukkan atau memilih Nomor PO resmi yang dikirim supplier.
    3. Sistem memvalidasi status PO di database. Jika PO terdaftar berstatus `Approved`, sistem otomatis memunculkan daftar nama produk beserta batas kuantitas pesanan awal.
    4. Staff menghitung fisik barang yang diturunkan dari truk, lalu menginput parameter: Jumlah Nyata Diterima (`qty_terima`), Nomor Produksi Pabrik (`batch_number`), dan Tanggal Kedaluwarsa (`expired_date`).
    5. Sistem memproses parameter data, memperbarui nilai kuantitas stok di tabel transaksi, dan menampilkan **Smart Rack Recommendation** (Misal: "Letakkan di Rak A1") di layar.
    6. Staff memindahkan barang fisik ke lokasi rak yang direkomendasikan dan mengklik tombol "Selesai".

* **Decision Points**:
    | Decision Point | YES / Success path | NO / Error path |
    | :--- | :--- | :--- |
    | **Nomor PO Valid & Approved?** | Sistem membuka form isian kuantitas penerimaan barang secara transparan. | Sistem mengunci form dan menampilkan pesan: *"Akses Ditolak: Dokumen PO Belum Disetujui Owner atau Tidak Ditemukan"*. |
    | **Qty Terima $\le$ Qty Pesan PO?** | Transaksi dilanjutkan ke tahap kalkulasi penentuan lokasi rak. | Sistem memblokir tombol simpan dan memunculkan notifikasi: *"Gagal: Jumlah input barang datang melebihi kuantitas pesanan resmi"*. |

* **Edge Cases**:
    * **Input Tanggal Kedaluwarsa Kadaluarsa (Past Date)**: Jika Staff tidak sengaja memasukkan `expired_date` yang nilainya kurang dari atau sama dengan tanggal hari berjalan ($\le$ Hari Ini), sistem akan mendeteksi anomali tersebut, memblokir penyimpanan data ke database MySQL, dan memunculkan pesan peringatan: *"Gagal: Tanggal kedaluwarsa produk tidak valid atau sudah terlampaui!"*.

---

#### 6.2 Workflow: [Pencatatan Barang Keluar Otomatis Berbasis FEFO]
* **Actor**: Staff Gudang
* **Goal**: Memotong jumlah persediaan produk secara akurat berdasarkan prinsip keamanan pangan terstruktur tanpa perlu memilih *batch* secara manual.
* **FRs covered**: FR-02
* **Ideal Path**:
    1. Staff Gudang membuka form "Barang Keluar", lalu memilih badan tujuan pengiriman (Reseller/Cabang/Pelanggan).
    2. Staff memilih nama produk yang akan dikirim, memasukkan total jumlah volume pengeluaran barang, lalu mengklik tombol "Validasi Stok".
    3. Sistem menjalankan fungsi kueri internal, memindai seluruh *batch* aktif dari produk tersebut, lalu mengurutkannya berdasarkan tanggal kedaluwarsa terdekat yang masih memiliki sisa saldo stok di rak.
    4. Sistem otomatis membagi (*split*) alokasi pemotongan stok pada data batch tertua dan memunculkan instruksi pengambilan lokasi fisik di layar monitor.
    5. Staff mengambil fisik barang di lorong rak sesuai petunjuk teks tertera, mengemas barang, dan mengklik tombol "Selesai" untuk memperbarui database secara *real-time*.

* **Decision Points**:
    | Decision Point | YES / Success path | NO / Error path |
    | :--- | :--- | :--- |
    | **Total Stok Kumulatif Cukup?** | Transaksi diproses, sistem menyajikan rincian kode batch dan instruksi rak pengambilan. | Sistem membatalkan transaksi dan memunculkan pesan: *"Gagal: Sisa total stok produk di sistem tidak mencukupi permintaan"*. |

---

#### 6.3 Workflow: [Pelaporan dan Otorisasi Karantina Barang Rusak]
* **Actor**: Staff Gudang & Owner
* **Goal**: Mengisolasi produk cacat dari stok siap jual untuk mencegah salah kirim, serta mencatat nilai kerugian finansial setelah divalidasi pemilik.
* **FRs covered**: FR-03, FR-09
* **Ideal Path**:
    1. Staff Gudang menemukan produk cacat di dalam rak saat aktivitas harian.
    2. Staff membuka menu "Laporkan Barang Rusak", memilih kode produk, memilih nomor batch terkait, menginput jumlah item rusak, serta memotret fisik barang untuk diunggah ke sistem.
    3. Setelah diklik kirim, sistem seketika memotong jumlah tersebut dari saldo stok siap jual di database, lalu mengunci status data item tersebut ke dalam kondisi `Karantina / Menunggu Approval`.
    4. Notifikasi dokumen pengajuan masuk secara *real-time* ke akun Owner. Owner meninjau lampiran alasan dan foto bukti fisik dari dasbor manajerial.
    5. Owner mengklik tombol persetujuan akhir tindak lanjut.

* **Decision Points**:
    | Decision Point | YES / Success path (Owner Approve) | NO / Error path (Owner Reject) |
    | :--- | :--- | :--- |
    | **Keputusan Validasi Owner?** | Status barang berubah menjadi `Disetujui`, data dihapus permanen dari stok aktif, dan kerugian finansial dicatat di *Discrepancy Log*. | Status karantina dibatalkan, laporan diarsipkan sebagai ditolak, dan jumlah unit barang otomatis dikembalikan ke saldo stok siap jual di lokasi rak semula. |

---

## PART 3: TECHNICAL & DATA REQUIREMENTS

### 7. Design Considerations
#### DC-01 - Aksesibilitas Elemen Interaktif Antarmuka Web Mobile Staff
* **Constraint**: Seluruh komponen interaktif (tombol input transaksi, link navigasi menu utama, dan kolom pencarian barang) pada visualisasi layar web responsive ketika dibuka via perangkat browser *smartphone* milik staf wajib memiliki area sentuh minimum sebesar $44\times44$ px. Hal ini diwajibkan guna memastikan operator lapangan dapat mengeksekusi tombol opsi secara lancar meskipun dalam kondisi pergerakan aktif di area lorong gudang.
* **Dasar**: WCAG 2.1 Level AA Success Criterion 2.5.5 (Target Size).
* **Metode Verifikasi**: Audit elemen UI menggunakan fitur Axe Dev Tools pada browser emulasi mobile di lingkungan pengetesan staging sebelum dilakukan persetujuan rilis.

#### DC-02 - Kompatibilitas Resolusi Tampilan Dasbor Desktop Manajerial
* **Constraint**: Seluruh struktur halaman utama, grafik informasi, dan tabel matriks manajemen PO pada komputer desktop stasioner milik Admin/Owner harus mampu merender data secara utuh melebar pada batas resolusi monitor minimum 1024px tanpa memunculkan horizontal scroll bar.
* **Dasar**: Menjamin kenyamanan pandangan mata Owner dan Admin dalam membaca grafik laporan analisis data MIS serta tabel fungsional dalam satu monitor kerja tunggal.
* **Metode Verifikasi**: Pengujian penyesuaian layout menggunakan fitur emulasi responsive layar Chrome DevTools secara berkala di tahap pengembangan UI/UX.

---

### 8. Data Requirements
#### 8.1 Entitas Data Utama (Database Schema Guidance)
##### Entitas 1: Master Product (`m_products`)
* **Primary Key**: `kode_produk` (String, Unik - SKU Pabrik).
* **Atribut Utama**: `nama_produk` (String), `kategori_id` (FK), `harga_beli` (Decimal), `stok_minimum` (Integer), `uom` (Enum: 'Pcs', 'Dus', 'Pack').
* **Business Constraint**: Parameter `harga_beli` dikunci secara ketat di tingkat enkripsi server; hanya boleh dirender di view konsol milik akun Role Owner dan Admin Gudang.

##### Entitas 2: Master Rack (`m_racks`)
* **Primary Key**: `kode_rak` (String, Unik - Contoh: 'A1', 'B3').
* **Atribut Utama**: `kapasitas_maksimum_volume` (Integer), `kapasitas_terpakai` (Integer).
* **Business Constraint**: Nilai `kapasitas_terpakai` dimutakhirkan secara otomatis melalui fungsi *database system trigger* atau hook Laravel setiap kali ada eksekusi mutasi final. *Atribut koordinat rak dilepas dari master produk agar penempatan batch barang bersifat fleksibel dinamis.*

##### Entitas 3: Batch Inbound Transaksi (`t_batch_inbounds`)
* **Primary Key**: `batch_number` (String, Unik - Kode Produksi Pabrik).
* **Foreign Keys**: `produk_id` (FK ke `m_products`), `po_id` (FK ke `t_purchase_orders`), `rak_id` (FK ke `m_racks`).
* **Atribut Utama**: `expired_date` (Date), `stok_awal_batch` (Integer), `stok_sisa_batch` (Integer).
* **Business Constraint**: Nilai properti `stok_sisa_batch` dilarang keras diubah secara manual di luar fungsi resmi transaksi logistik masuk (*Inbound*), keluar (*Outbound* Berbasis FEFO), atau penyesuaian hasil akhir konfirmasi *Stock Opname*.

#### 8.2 Kebijakan Retensi & Privasi Data
* **Klasifikasi Data**: Data transaksi internal logistik, nilai kapitalisasi pengadaan barang milik UMKM, data akun kredensial, serta rekam histori kinerja riwayat pengiriman mitra pemasok.
* **Retensi Data**: Seluruh data riwayat mutasi, manifes dokumen PO, dan laporan audit *Stock Opname* wajib disimpan secara utuh di dalam database utama selama minimal 3 tahun demi kebutuhan pembukuan internal usaha dan pelaporan pajak UMKM. Setelah melewati masa 3 tahun, data lama dipindahkan ke penyimpanan sekunder (*cold storage database*).

---

### 9. Non-Functional Requirements (NFR)

#### NFR-11 [Performance - System Response Time]
* **Pernyataan Requirement**: Proses eksekusi kueri pencarian data barang, pemrosesan logika algoritma urutan FEFO pada backend, dan pemuatan halaman web responsive harus menghasilkan waktu respons < 1.5 detik untuk 95% total permintaan pengguna pada kondisi beban puncak (*peak load*).
* **Rasionalisasi**: Perputaran bisnis distributor menuntut kecepatan pencatatan tinggi saat bongkar muat logistik; latensi sistem yang lambat akan memicu antrean penumpukan fisik kendaraan di area bongkar gudang.
* **Metode Pengujian**: Simulasi pengujian performa kueri beban secara berkala menggunakan alat uji beban k6 load test di lingkungan staging.
* **FR Terkait**: FR-01, FR-02, dan FR-04.

#### NFR-12 [Security - Data Protection & Guardrails]
* **Pernyataan Requirement**: Seluruh lalu lintas pengiriman data transaksi harian wajib dienkripsi menggunakan protokol TLS 1.2 atau versi di atasnya, serta database utama MySQL dikonfigurasi menggunakan metode enkripsi *data-at-rest* AES-256. Sistem wajib menerapkan batas maksimal 5 kali kegagalan input kata sandi dalam kurun waktu 10 menit sebelum akun user bersangkutan diblokir sementara (*suspend*) oleh sistem selama 30 menit.
* **Rasionalisasi**: Mencegah kebocoran data nilai aset keuangan internal, manipulasi stok sepihak dari luar, serta proteksi terhadap aksi serangan siber berbasis *brute-force*.
* **Metode Pengujian**: Pengujian penetrasi internal (*penetration test*) terhadap *endpoint* API form login sebelum status proyek dinyatakan siap meluncur (*go-live*).
* **FR Terkait**: Seluruh fungsi fungsional sistem (FR-01 s.d FR-10).

---

### 10. Release Plan & Milestone Schedule

#### 10.1 Version Roadmap
| v1.0 - MVP Base (Bulan 1-3) | v1.5 - Enhancement (Bulan 4-6) | v2.0 - Scale (Bulan 7-12) |
| :--- | :--- | :--- |
| • Implementasi fungsional Manajemen Master Data & Role User Terpusat. | • Integrasi bot notifikasi pengingat otomatis via WhatsApp Business / Telegram gratisan ke HP Owner. | • Integrasi otomatis via API dengan sistem penjualan manajemen toko e-commerce eksternal. |
| • Modul pengadaan *Purchase Order* terintegrasi fitur *Approval* Owner. | • Fitur ekspor berkas laporan stok bulanan otomatis menjadi format Excel/PDF. | • Penerapan algoritma prediksi tren stok masa depan berbasis Kecerdasan Buatan (AI Forecasting). |
| • Otomasi pemotongan stok logistik berbasis aturan **FEFO**. | • Fitur pencetakan dokumen visual label barcode penanda produk internal. | • Pengembangan aplikasi berbasis *Native Mobile* khusus untuk iOS dan Android. |
| • Modul 5 Fitur Pintar (*Smart Features Layout Framework*). | | |

#### 10.2 3-Milestone Schedule (v1.0 MVP)
| Milestone | Minggu | Owner | Acceptance Criterion |
| :--- | :--- | :--- | :--- |
| **M1 - PRD Finalized & Design Sign-off** | Minggu 2 | PM + UI/UX Designer | Seluruh komponen dokumen PRD NexStock disetujui penuh oleh tim Kelompok 9. Desain cetak biru skema UI/UX selesai di Figma dengan keterangan penjelasan alur fungsional yang jelas. |
| **M2 - Core Backend API Complete & Alpha Test** | Minggu 8 | Tech Lead + QA Lead | Semua endpoint fungsional utama (Otomasi FEFO, kalkulasi *Restock Forecast*, logika *Error Detection*) lulus uji unit test 100%. Lingkungan staging stabil untuk dicoba simulasi internal. |
| **M3 - UAT Signed Off & Go-Live System** | Minggu 11-12 | PM + DevOps | Seluruh ambang batas kualitas performa (NFR-11 dan NFR-12) tervalidasi sukses. Bebas dari bug berstatus tingkat kritikal, sistem database cadangan aktif, dan aplikasi web resmi tayang publik. |

#### 10.3 Definition of Done (DoD)
1. Seluruh fungsi spesifikasi kebutuhan fungsional (FR-01 s.d FR-10) wajib lulus pengujian skenario fungsional *test case* 100% tanpa malafungsi.
2. Batas nilai ambang performa kecepatan kueri backend (< 1.5 detik) sukses divalidasi pada pengujian server staging.
3. Tidak ada *error code* atau kutu sistem (bug) berstatus tingkat bahaya tinggi (*critical / high-severity*) yang masih berstatus terbuka di papan pelacakan tim.
4. Komponen elemen interaktif pada web mobile lolos audit ukuran luas tap sentuh minimum sesuai regulasi standar DC-01.
5. Panduan manual tata cara operasional penggunaan aplikasi (*user manual guide*) telah selesai ditulis lengkap menggunakan Bahasa Indonesia yang lugas dan informatif.
6. Prosedur pencadangan otomatis (*database backup recovery system*) dikonfigurasi dan diuji tingkat keberhasilan pemulihannya dengan sukses oleh bagian DevOps.

---

### Revision History
| Version | Date | Author | Changes |
| :--- | :--- | :--- | :--- |
| v1.0 | 25/06/2026 | Kelompok 9 | Draf Awal: Transformasi total konsep sistem dari arsitektur *hardware-heavy* (SWMS) menjadi sistem informasi logistik cerdas terintegrasi (*software-driven*) khusus distributor Makanan & Minuman bernama **NEXSTOCK**. Penegasan implasi aturan penempatan rak dinamis, penguncian validasi PO masuk, penambahan parameter konversi satuan terkunci (*UoM*), integrasi variabel *Lead Time* pada fitur prediksi pengadaan, serta otomasi alur pemotongan berbasis metode ketat **FEFO**. |