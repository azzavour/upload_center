# Panduan Membersihkan Tabel Department Lama

## 🎯 Tujuan

Menghapus tabel-tabel department yang sudah dibuat otomatis oleh migration, agar user bisa membuat tabel baru dari awal melalui form Create Format.

---

## ⚠️ PERINGATAN

**HATI-HATI!** Proses ini akan menghapus:
- Semua tabel dengan prefix `dept_`
- Semua data di tabel-tabel tersebut
- Tidak bisa di-undo!

**Lakukan backup database terlebih dahulu jika data penting!**

---

## 📋 Tabel yang Akan Dihapus

Berdasarkan screenshot, tabel-tabel ini akan dihapus:
```
✗ dept_fin_produk
✗ dept_hr_produk
✗ dept_it_pembelian
✗ dept_it_produk
✗ dept_it_tracks
✗ dept_ops_produk
✗ dept_sales_penjualan
✗ dept_sales_penjualan_coffee
```

---

## 🔧 Cara Membersihkan

### Opsi 1: Via SQL Script (Recommended)

**Langkah 1: Backup Database (WAJIB!)**
```bash
# Backup database
pg_dump -U postgres -d upload-center > backup_before_cleanup_$(date +%Y%m%d_%H%M%S).sql
```

**Langkah 2: Jalankan Script Cleanup**
```bash
# Masuk ke psql
psql -U postgres -d upload-center

# Jalankan script
\i cleanup_department_tables.sql

# Atau copy-paste manual:
DROP TABLE IF EXISTS dept_fin_produk CASCADE;
DROP TABLE IF EXISTS dept_hr_produk CASCADE;
DROP TABLE IF EXISTS dept_it_pembelian CASCADE;
DROP TABLE IF EXISTS dept_it_produk CASCADE;
DROP TABLE IF EXISTS dept_it_tracks CASCADE;
DROP TABLE IF EXISTS dept_ops_produk CASCADE;
DROP TABLE IF EXISTS dept_sales_penjualan CASCADE;
DROP TABLE IF EXISTS dept_sales_penjualan_coffee CASCADE;
```

**Langkah 3: Verifikasi**
```sql
-- Cek apakah tabel sudah terhapus
SELECT tablename 
FROM pg_tables 
WHERE schemaname = 'public' 
AND tablename LIKE 'dept_%';

-- Harusnya kosong atau hanya tersisa tabel yang tidak di-drop
```

---

### Opsi 2: Via Laravel Tinker

**Langkah 1: Masuk ke Tinker**
```bash
cd c:\Annisa\Magang\Magang_2\upload-center
php artisan tinker
```

**Langkah 2: Jalankan Command**
```php
// Lihat semua tabel department
$tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename LIKE 'dept_%'");
foreach($tables as $table) {
    echo $table->tablename . "\n";
}

// Hapus satu per satu
Schema::dropIfExists('dept_fin_produk');
Schema::dropIfExists('dept_hr_produk');
Schema::dropIfExists('dept_it_pembelian');
Schema::dropIfExists('dept_it_produk');
Schema::dropIfExists('dept_it_tracks');
Schema::dropIfExists('dept_ops_produk');
Schema::dropIfExists('dept_sales_penjualan');
Schema::dropIfExists('dept_sales_penjualan_coffee');

// Verifikasi
$tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename LIKE 'dept_%'");
echo "Remaining tables: " . count($tables) . "\n";
```

---

### Opsi 3: Via Migration (Untuk Production)

Jika Anda ingin cara yang lebih terstruktur, buat migration baru:

**Langkah 1: Buat Migration**
```bash
php artisan make:migration cleanup_old_department_tables
```

**Langkah 2: Edit Migration**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus tabel-tabel lama
        $tables = [
            'dept_fin_produk',
            'dept_hr_produk',
            'dept_it_pembelian',
            'dept_it_produk',
            'dept_it_tracks',
            'dept_ops_produk',
            'dept_sales_penjualan',
            'dept_sales_penjualan_coffee',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // Tidak bisa restore, jadi kosongkan
    }
};
```

**Langkah 3: Jalankan Migration**
```bash
php artisan migrate
```

---

## 🧹 Membersihkan Data Format Lama (Opsional)

Jika Anda juga ingin menghapus data format yang sudah ada di tabel `excel_formats`:

```sql
-- Backup dulu
SELECT * FROM excel_formats;

-- Hapus semua format (HATI-HATI!)
DELETE FROM excel_formats;

-- Atau hapus format tertentu saja
DELETE FROM excel_formats WHERE id = 1;
DELETE FROM excel_formats WHERE format_name = 'Format Data Produk';
```

**Via Tinker:**
```php
// Lihat semua format
ExcelFormat::all();

// Hapus semua
ExcelFormat::truncate();

// Atau hapus satu per satu
ExcelFormat::find(1)->delete();
```

---

## ✅ Setelah Cleanup

Setelah tabel-tabel lama dihapus:

### 1. **Test Create Format**
```
1. Login sebagai user dengan department
2. Menu → Formats → Tambah Format Baru
3. Isi form:
   - Nama Format: Format Data Produk Baru
   - Nama Tabel: produk_baru
   - Kolom: Kode, Nama, Harga, Stok
4. Submit
5. Cek database: Tabel dept_xxx_produk_baru harus terbuat
```

### 2. **Verifikasi di Database**
```sql
-- Cek tabel baru
SELECT tablename 
FROM pg_tables 
WHERE schemaname = 'public' 
AND tablename LIKE 'dept_%'
ORDER BY tablename;

-- Cek struktur tabel
\d dept_fin_produk_baru
```

### 3. **Test Upload**
```
1. Menu → Upload
2. Pilih format yang baru dibuat
3. Upload file Excel
4. Cek apakah data masuk ke tabel
```

---

## 🔄 Alur Kerja Baru

### Sebelum Cleanup:
```
Database sudah punya tabel:
- dept_fin_produk
- dept_hr_produk
- dll (dibuat oleh migration)

User create format → Tabel sudah ada → Tidak jelas
```

### Setelah Cleanup:
```
Database kosong (tidak ada tabel dept_*)

User create format "produk_baru"
→ Sistem buat tabel dept_fin_produk_baru
→ Jelas bahwa user yang membuat tabel
```

---

## 📊 Checklist

### Sebelum Cleanup:
- [ ] Backup database
- [ ] Catat tabel apa saja yang akan dihapus
- [ ] Pastikan data tidak penting atau sudah di-backup
- [ ] Informasikan ke tim jika ada

### Proses Cleanup:
- [ ] Jalankan script cleanup
- [ ] Verifikasi tabel sudah terhapus
- [ ] Cek tidak ada error

### Setelah Cleanup:
- [ ] Test create format baru
- [ ] Verifikasi tabel terbuat
- [ ] Test upload file
- [ ] Verifikasi data masuk ke tabel

---

## 🚨 Troubleshooting

### Problem: Error "cannot drop table because other objects depend on it"

**Solusi:**
```sql
-- Gunakan CASCADE
DROP TABLE dept_fin_produk CASCADE;
```

### Problem: Tabel tidak bisa dihapus

**Solusi:**
```sql
-- Cek dependency
SELECT 
    tc.table_name, 
    kcu.column_name, 
    ccu.table_name AS foreign_table_name
FROM information_schema.table_constraints AS tc 
JOIN information_schema.key_column_usage AS kcu
  ON tc.constraint_name = kcu.constraint_name
JOIN information_schema.constraint_column_usage AS ccu
  ON ccu.constraint_name = tc.constraint_name
WHERE tc.table_name = 'dept_fin_produk';

-- Hapus foreign key dulu, baru hapus tabel
```

### Problem: Setelah cleanup, create format error

**Cek:**
1. Apakah migration sudah dijalankan?
2. Apakah TableManagerService berfungsi?
3. Cek log: `storage/logs/laravel.log`

---

## 📝 Catatan Penting

### 1. **Migration `create_fix_department_tables.php`**
Migration ini membuat tabel berdasarkan format yang sudah ada. Jika Anda tidak ingin ini terjadi lagi:

**Opsi A: Hapus Migration (Jika belum di production)**
```bash
# Hapus file migration
rm database/migrations/2025_09_30_055017_create_fix_department_tables.php

# Rollback jika sudah dijalankan
php artisan migrate:rollback --step=1
```

**Opsi B: Ubah Migration (Jika sudah di production)**
Biarkan migration tetap ada, tapi jangan jalankan lagi. Tabel akan dibuat oleh user melalui form.

### 2. **Data Lama**
Jika ada data penting di tabel lama:
```sql
-- Export data dulu
COPY dept_fin_produk TO '/tmp/dept_fin_produk_backup.csv' CSV HEADER;

-- Setelah cleanup dan create format baru, import kembali
COPY dept_fin_produk_baru FROM '/tmp/dept_fin_produk_backup.csv' CSV HEADER;
```

### 3. **Upload Histories**
Jika ada upload histories yang merujuk ke tabel lama:
```sql
-- Cek upload histories
SELECT * FROM upload_histories WHERE excel_format_id IN (
    SELECT id FROM excel_formats WHERE target_table IN ('produk', 'karyawan', 'tracks')
);

-- Opsional: Hapus histories lama
DELETE FROM upload_histories WHERE excel_format_id IN (
    SELECT id FROM excel_formats WHERE target_table IN ('produk', 'karyawan', 'tracks')
);
```

---

## ✅ Kesimpulan

Setelah cleanup:
1. ✅ Database bersih dari tabel department lama
2. ✅ User bisa membuat tabel baru dari form
3. ✅ Sistem lebih jelas: user yang membuat tabel
4. ✅ Tidak ada konflik dengan tabel lama

---

**Status:** Ready to Execute
**Risk Level:** ⚠️ HIGH (Data Loss)
**Recommendation:** Backup dulu sebelum cleanup!

---

**Last Updated:** 30 September 2025
