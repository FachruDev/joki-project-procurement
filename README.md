# Procurement System (Laravel + Livewire)

Dokumentasi ini menjelaskan alur aplikasi Procurement System saat ini agar user baru, tim operasional, dan tim teknis mudah memahami proses end-to-end.

## 1) Ringkasan Aplikasi

Aplikasi ini digunakan untuk mengelola proses pengadaan dari awal hingga akhir:

1. Vendor mendaftar akun
2. Admin mereview & approve vendor
3. Procurement membuat RFQ ke vendor terpilih
4. Vendor mengirim penawaran harga
5. Procurement membuat Purchase Order (PO)
6. Vendor mengunggah invoice
7. Tim approver menyetujui/menolak invoice
8. Monitoring dilakukan melalui dashboard, laporan, notifikasi, dan audit log

## 2) Teknologi yang Digunakan

- PHP 8.3
- Laravel 13
- Livewire 4
- Flux UI
- Laravel Fortify (auth, reset password, email verification, 2FA)
- Spatie Permission (RBAC role & permission)
- Spatie Media Library (file invoice)
- Spatie Activity Log (audit trail)
- Laravel Excel (export laporan dashboard)

## 3) Role Pengguna & Hak Akses

### SuperAdmin / Admin
- Akses penuh modul management
- Review/approve vendor
- Kelola user
- Kelola role & permission
- Melihat semua modul RFQ, PO, Invoice, Report

### Procurement
- Kelola vendor (manage)
- Buat & kelola RFQ
- Buat & kelola PO
- Buat Goods Receipt
- Lihat invoice
- Lihat report

### Vendor
- Melihat RFQ yang ditugaskan
- Mengirim/ubah respons RFQ
- Melihat PO milik vendor
- Mengunggah invoice
- Melihat status invoice sendiri
- Wajib status vendor **approved** untuk aksi kritikal (respond RFQ & upload invoice)

## 4) Alur Bisnis Utama (Flow Apps)

### A. Vendor Onboarding

1. User register dari halaman auth (otomatis diberi role `Vendor`)
2. Sistem membuat profil vendor dengan status `pending`
3. Admin/SuperAdmin melakukan review pada menu Vendor Review
4. Status vendor diubah menjadi `approved` atau `rejected`
5. Jika approved, vendor bisa mengikuti RFQ dan upload invoice

### B. RFQ → PO

1. Procurement membuat RFQ dan memilih vendor **approved**
2. Sistem mengirim notifikasi in-app ke vendor terpilih
3. Vendor membuka RFQ dan submit/ubah penawaran harga
4. Procurement mengevaluasi respons vendor
5. Procurement membuat PO (bisa terkait RFQ)
6. Jika PO dibuat dari RFQ, status RFQ ditutup (`closed`)

### C. PO → Invoice Approval

1. Vendor membuka PO miliknya
2. Vendor upload file invoice (pdf/jpg/jpeg/png, max 10MB)
3. Invoice masuk status `pending`
4. User dengan izin `invoice.approve` melakukan approve/reject
5. Vendor menerima notifikasi hasil approval

## 5) Status Penting di Sistem

- Vendor: `pending`, `approved`, `rejected`
- RFQ: `open`, `closed`
- Purchase Order: `draft`, `approved`, `completed`
- Invoice: `pending`, `approved`, `rejected`

## 6) Menu Utama di Sidebar

- **Main**: Dashboard, Reports
- **Administration**: User Management, Role & Permission
- **Vendor**: Vendor List, Vendor Review
- **RFQ**: RFQ List, My RFQ
- **Purchase Orders**: PO List, My PO
- **Invoices**: Invoice List, My Invoice, Invoice Approval

Catatan: Menu tampil berdasarkan permission masing-masing user.

## 7) Route Utama Aplikasi

Semua route utama berada pada:
- `/home/runner/work/joki-project-procurement/joki-project-procurement/routes/web.php`
- `/home/runner/work/joki-project-procurement/joki-project-procurement/routes/settings.php`

Route route ini sudah diproteksi dengan kombinasi:
- `auth`, `verified`
- permission middleware (`permission:*`, `can:*`)
- middleware custom `approved_vendor`

## 8) Setup Lokal

### Prasyarat
- PHP 8.3+
- Composer
- Node.js + npm

### Instalasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate --no-interaction
php artisan migrate --force --no-interaction
php artisan db:seed --no-interaction
npm run build
```

### Menjalankan aplikasi

```bash
composer run dev
```

## 9) Akun Demo (Seeder)

Setelah `php artisan db:seed`, akun default:

- SuperAdmin: `superadmin@procurement.test` / `password`
- Admin: `admin@procurement.test` / `password`
- Procurement: `procurement@procurement.test` / `password`

Vendor demo lain dibuat oleh `ProcurementDemoSeeder`.

## 10) Notifikasi & Audit

- Notifikasi in-app digunakan pada event penting:
  - Undangan RFQ
  - Respons RFQ masuk/diupdate
  - PO baru
  - Invoice diupload
  - Invoice diapprove/reject
  - Vendor diapprove/reject
- Audit trail aktivitas penting tersedia melalui Spatie Activity Log.

## 11) Struktur Modul (Ringkas)

- `app/Livewire/RFQ/*` → modul RFQ
- `app/Livewire/PO/*` → modul PO
- `app/Livewire/GR/*` → goods receipt
- `app/Livewire/Invoice/*` → invoice upload/list/approval
- `app/Livewire/Vendor/*` → dashboard vendor + management
- `app/Livewire/Admin/*` → user management + permission
- `app/Livewire/Report/*` → laporan

## 12) Flow Cepat untuk User Baru

### Jika Anda Procurement
1. Buka Dashboard
2. Buat RFQ
3. Pantau respons vendor
4. Buat PO
5. Pantau invoice
6. Lihat report

### Jika Anda Vendor
1. Pastikan profil vendor sudah approved
2. Cek menu My RFQ
3. Submit penawaran
4. Cek menu My PO
5. Upload invoice
6. Pantau status invoice di My Invoice

### Jika Anda Admin
1. Review vendor baru
2. Kelola user/permission
3. Monitor dashboard, report, dan invoice approval

---

Dokumentasi ini mengikuti implementasi kode saat ini. Jika flow bisnis berubah, update bagian **Flow Apps**, **Role**, dan **Status** terlebih dahulu agar tetap sinkron dengan aplikasi.
