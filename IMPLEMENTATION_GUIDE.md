# Upload Center - Panduan Implementasi dan Penggunaan

## 📋 Daftar Isi
1. [Ringkasan Perubahan](#ringkasan-perubahan)
2. [Instalasi dan Setup](#instalasi-dan-setup)
3. [Fitur Baru](#fitur-baru)
4. [Cara Penggunaan](#cara-penggunaan)
5. [Troubleshooting](#troubleshooting)
6. [File yang Tidak Dibutuhkan](#file-yang-tidak-dibutuhkan)

---

## 🎯 Ringkasan Perubahan

### Masalah yang Diperbaiki:

#### ✅ 1. Data Tidak Masuk ke Database
**Sebelum:** Saat user membuat format baru, tabel tidak dibuat di database.
**Sekarang:** Tabel otomatis dibuat dengan prefix department (contoh: `dept_finance_tracks`)

#### ✅ 2. Tampilan History Upload
**Sebelum:** Tidak ada informasi detail tentang file yang diupload.
**Sekarang:** 
- Dapat melihat siapa yang upload
- Dapat melihat mode upload (Replace/Append)
- Dapat melihat semua file dari department yang sama

#### ✅ 3. Pilihan Mode Upload
**Sebelum:** Data selalu ditambahkan (append).
**Sekarang:** User dapat memilih:
- **Append:** Tambahkan data baru ke data yang ada
- **Replace:** Hapus data lama, insert data baru

#### ✅ 4. Tracking File Upload
**Sebelum:** Tidak ada informasi detail tentang file yang diupload.
**Sekarang:** Tabel `file_uploads` mencatat:
- Siapa yang upload
- File apa yang diupload
- Masuk ke tabel mana
- Berapa baris yang berhasil
- Mode upload yang digunakan

---

## 🚀 Instalasi dan Setup

### 1. Jalankan Migration

```bash
php artisan migrate
```

Migration yang akan dijalankan:
- `2025_09_30_092152_add_department_id_to_upload_histories_table.php`
- `2025_09_30_092203_create_file_uploads_table.php`

### 2. Cek Database

Pastikan tabel-tabel berikut sudah terbuat:
- `file_uploads` - Tabel baru untuk tracking file
- `upload_histories` - Sudah ada kolom `department_id` dan `upload_mode`

### 3. Clear Cache (Opsional)

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🎨 Fitur Baru

### 1. Upload Mode Selection

Saat upload file, user dapat memilih mode:

**Append Mode (Default):**
- Data baru ditambahkan ke data yang sudah ada
- Data lama tidak dihapus
- Cocok untuk update berkala

**Replace Mode:**
- Data lama dari department yang sama dihapus
- Data baru diinsert
- Cocok untuk refresh data bulanan

### 2. File Upload Tracking

Setiap upload tercatat di tabel `file_uploads` dengan informasi:
```
- ID Upload History
- Department ID
- User yang upload
- Nama file asli
- Nama file tersimpan
- Target table (dept_xxx_xxx)
- Format name
- Jumlah rows yang berhasil
- Mode upload
- Waktu upload
```

### 3. Enhanced History View

**History Index:**
- Kolom "Uploaded By" - Menampilkan nama user
- Kolom "Mode" - Badge untuk Replace/Append
- Filter berdasarkan department

**History Show:**
- Informasi lengkap file
- Informasi uploader dan department
- Badge mode upload
- Statistik upload

---

## 📖 Cara Penggunaan

### A. Membuat Format Baru

1. Login sebagai user dengan department
2. Pergi ke menu **Formats** → **Create Format**
3. Isi form:
   - Format Name: Nama format (contoh: "Track Data Finance")
   - Description: Deskripsi format
   - Expected Columns: Kolom yang diharapkan
   - Target Table: Nama tabel (contoh: "tracks")
4. Klik **Submit**
5. **Sistem otomatis membuat tabel:** `dept_{department_code}_{target_table}`
   - Contoh: `dept_finance_tracks`

### B. Upload File

1. Pergi ke menu **Upload**
2. Pilih **Format Excel** dari dropdown
3. Pilih **File Excel/CSV** (max 10MB)
4. Pilih **Mode Upload:**
   - ⭕ **Append** - Tambahkan ke data yang ada
   - ⭕ **Replace** - Ganti semua data lama
5. Klik **Cek Format** untuk preview
6. Jika format valid, klik **Upload & Process**

### C. Melihat History

1. Pergi ke menu **History**
2. Lihat semua file yang diupload oleh department Anda
3. Informasi yang ditampilkan:
   - Filename
   - Format yang digunakan
   - Siapa yang upload
   - Mode upload (Replace/Append)
   - Status (Completed/Failed)
   - Jumlah rows berhasil/gagal
   - Tanggal upload
4. Klik **👁️ (Eye icon)** untuk melihat detail

### D. Melihat Detail Upload

1. Dari History, klik icon mata (👁️)
2. Informasi yang ditampilkan:
   - **File Info:** Nama file, uploader, department, mode
   - **Format & Mapping:** Format dan mapping yang digunakan
   - **Statistics:** Total rows, success, failed, success rate
   - **Error Details:** Jika ada error, ditampilkan per baris
   - **Column Mapping:** Mapping kolom yang digunakan

---

## 🔍 Contoh Skenario Penggunaan

### Skenario 1: Upload Pertama Kali

**User A dari Finance:**
1. Buat format "Track Data Finance" dengan target table "tracks"
2. Sistem membuat tabel `dept_finance_tracks`
3. Upload file `tracks_january.xlsx` dengan mode **Append**
4. 100 rows berhasil diinsert
5. User A dapat melihat data di history

### Skenario 2: Upload Tambahan

**User B dari Finance:**
1. Upload file `tracks_february.xlsx` dengan mode **Append**
2. 50 rows baru ditambahkan
3. Total data di `dept_finance_tracks` = 150 rows
4. User B dapat melihat file User A dan file sendiri di history

### Skenario 3: Replace Data

**User A dari Finance:**
1. Upload file `tracks_updated.xlsx` dengan mode **Replace**
2. Sistem menghapus 150 rows lama
3. Insert 200 rows baru
4. Total data di `dept_finance_tracks` = 200 rows
5. History menunjukkan mode "Replace"

### Skenario 4: Department Isolation

**User C dari HR:**
1. Buat format "Employee Data" dengan target table "employees"
2. Sistem membuat tabel `dept_hr_employees`
3. Upload file dengan data HR
4. User C **TIDAK BISA** melihat data Finance
5. User Finance **TIDAK BISA** melihat data HR

---

## 🛠️ Troubleshooting

### Problem 1: Tabel Tidak Terbuat

**Gejala:** Error "Table not found" saat upload

**Solusi:**
1. Cek apakah format sudah dibuat dengan benar
2. Cek log di `storage/logs/laravel.log`
3. Cek database apakah tabel `dept_xxx_xxx` ada
4. Jika tidak ada, hapus format dan buat ulang

**Manual Fix:**
```sql
-- Cek tabel yang ada
SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename LIKE 'dept_%';

-- Jika tabel tidak ada, sistem akan membuat otomatis saat upload pertama
```

### Problem 2: Data Tidak Masuk

**Gejala:** Upload sukses tapi data tidak ada di database

**Solusi:**
1. Cek kolom di file Excel sesuai dengan expected columns
2. Cek mapping configuration
3. Cek error details di history show page
4. Cek log untuk detail error

### Problem 3: Replace Mode Tidak Menghapus Data

**Gejala:** Data lama masih ada setelah replace

**Solusi:**
1. Cek apakah mode "Replace" benar-benar dipilih
2. Cek log untuk konfirmasi delete operation
3. Cek `upload_mode` di tabel `upload_histories`

**Manual Check:**
```sql
-- Cek mode upload
SELECT id, original_filename, upload_mode, uploaded_at 
FROM upload_histories 
ORDER BY uploaded_at DESC 
LIMIT 10;

-- Cek data di tabel department
SELECT COUNT(*) FROM dept_finance_tracks;
```

### Problem 4: User Tidak Bisa Lihat History

**Gejala:** History page kosong

**Solusi:**
1. Pastikan user memiliki department
2. Cek apakah ada upload dari department tersebut
3. Admin dapat melihat semua history

**Check User Department:**
```sql
SELECT u.name, u.email, d.name as department 
FROM users u 
LEFT JOIN departments d ON u.department_id = d.id 
WHERE u.id = [user_id];
```

---

## 📁 File yang Tidak Dibutuhkan

Berdasarkan analisis, file-file berikut dapat dihapus atau dipindahkan:

### 1. File Backup
```
backup_before_migration.sql
```
**Rekomendasi:** Pindahkan ke folder backup di luar project

### 2. IDE Helper Files
```
_ide_helper.php
_ide_helper_models.php
.phpstorm.meta.php
```
**Rekomendasi:** 
- Keep untuk development
- Tambahkan ke `.gitignore` untuk production
- Tidak perlu di-deploy ke server

### 3. Cara Menghapus

**Option 1: Pindahkan ke folder backup**
```bash
mkdir ../backup
mv backup_before_migration.sql ../backup/
```

**Option 2: Tambahkan ke .gitignore**
```bash
echo "_ide_helper*.php" >> .gitignore
echo ".phpstorm.meta.php" >> .gitignore
echo "backup_before_migration.sql" >> .gitignore
```

---

## 📊 Database Schema

### Tabel: `file_uploads` (Baru)
```sql
CREATE TABLE file_uploads (
    id BIGSERIAL PRIMARY KEY,
    upload_history_id BIGINT REFERENCES upload_histories(id) ON DELETE CASCADE,
    department_id BIGINT REFERENCES departments(id) ON DELETE CASCADE,
    uploaded_by BIGINT REFERENCES users(id) ON DELETE CASCADE,
    original_filename VARCHAR(255),
    stored_filename VARCHAR(255),
    target_table VARCHAR(255),
    format_name VARCHAR(255),
    rows_inserted INTEGER DEFAULT 0,
    upload_mode VARCHAR(10) DEFAULT 'append',
    uploaded_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Tabel: `upload_histories` (Updated)
```sql
ALTER TABLE upload_histories 
ADD COLUMN department_id BIGINT REFERENCES departments(id),
ADD COLUMN upload_mode VARCHAR(10) DEFAULT 'append';
```

### Tabel Department (Contoh: `dept_finance_tracks`)
```sql
CREATE TABLE dept_finance_tracks (
    id BIGSERIAL PRIMARY KEY,
    upload_history_id BIGINT REFERENCES upload_histories(id) ON DELETE CASCADE,
    department_id BIGINT REFERENCES departments(id) ON DELETE CASCADE,
    -- Dynamic columns based on format
    track_id VARCHAR(500),
    track_name VARCHAR(500),
    artist_name VARCHAR(500),
    -- ... other columns
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 🔐 Security Notes

### 1. Department Isolation
- User hanya bisa akses data department sendiri
- Admin bisa akses semua data
- Validasi department_id di setiap query

### 2. File Upload Security
- Max file size: 10MB
- Allowed extensions: .xlsx, .xls, .csv
- File disimpan dengan timestamp prefix
- Validasi file type di client dan server

### 3. SQL Injection Prevention
- Menggunakan Laravel Query Builder
- Parameter binding untuk semua query
- Validasi input di controller

---

## 📞 Support dan Kontak

Jika ada pertanyaan atau issue:

1. **Cek Log:** `storage/logs/laravel.log`
2. **Cek Database:** Gunakan query di troubleshooting section
3. **Cek Permission:** Folder `storage/app/uploads` harus writable

---

## ✅ Testing Checklist

Sebelum deploy ke production, pastikan:

- [ ] Migration berhasil dijalankan
- [ ] Buat format baru → Tabel terbuat di database
- [ ] Upload file dengan mode Append → Data bertambah
- [ ] Upload file dengan mode Replace → Data lama terhapus
- [ ] Cek tabel `file_uploads` → Record tercatat
- [ ] Cek history page → Semua upload terlihat
- [ ] User A upload → User B dari department sama bisa lihat
- [ ] User dari department berbeda tidak bisa lihat data lain
- [ ] Admin bisa lihat semua data
- [ ] Error handling berfungsi dengan baik

---

## 📝 Changelog

### Version 2.0 (30 September 2025)

**Added:**
- Upload mode selection (Append/Replace)
- File upload tracking table
- Enhanced history views
- Department isolation for data
- Automatic table creation

**Fixed:**
- Data tidak masuk ke database
- Tabel tidak terbuat saat format dibuat
- Tidak ada informasi uploader di history

**Improved:**
- History index dengan kolom tambahan
- History show dengan informasi lengkap
- Upload form dengan mode selection
- Error handling dan logging

---

**Status:** ✅ Production Ready
**Last Updated:** 30 September 2025
