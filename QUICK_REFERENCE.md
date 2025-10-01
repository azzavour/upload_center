# Upload Center - Quick Reference Card

## 🚀 Quick Start

### Jalankan Migration
```bash
php artisan migrate
```

### Clear Cache
```bash
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

---

## 📝 5 Masalah yang Diperbaiki

| # | Masalah | Status | Solusi |
|---|---------|--------|--------|
| 1 | Data tidak masuk database | ✅ FIXED | Tabel otomatis dibuat |
| 2 | Tidak ada tampilan history | ✅ FIXED | History lengkap dengan info uploader |
| 3 | Tidak ada pilihan upload mode | ✅ FIXED | Replace/Append tersedia |
| 4 | Tidak ada tracking file | ✅ FIXED | Tabel file_uploads dibuat |
| 5 | File tidak dibutuhkan | ✅ FIXED | List file sudah dibuat |

---

## 📊 Tabel Database Baru

### `file_uploads` (Baru)
Tracking semua file yang diupload
```
- upload_history_id
- department_id
- uploaded_by
- original_filename
- target_table
- rows_inserted
- upload_mode
```

### `upload_histories` (Updated)
Tambah kolom:
```
- department_id
- upload_mode
```

---

## 🎯 Cara Menggunakan

### 1. Buat Format
```
Menu → Formats → Create
→ Tabel otomatis dibuat: dept_{dept_code}_{table_name}
```

### 2. Upload File
```
Menu → Upload
→ Pilih Format
→ Pilih File
→ Pilih Mode:
   ⭕ Append (tambah data)
   ⭕ Replace (ganti data)
→ Upload
```

### 3. Lihat History
```
Menu → History
→ Lihat semua file department Anda
→ Klik 👁️ untuk detail
```

---

## 🔧 Upload Mode

### Append (Default)
```
✅ Data baru ditambahkan
✅ Data lama tetap ada
✅ Cocok untuk update berkala
```

### Replace
```
⚠️ Data lama dihapus
✅ Data baru diinsert
✅ Cocok untuk refresh bulanan
```

---

## 📁 File yang Dibuat/Diubah

### Baru (6 file)
```
✅ database/migrations/2025_09_30_092152_add_department_id_to_upload_histories_table.php
✅ database/migrations/2025_09_30_092203_create_file_uploads_table.php
✅ app/Models/FileUpload.php
✅ CHANGES_SUMMARY.md
✅ IMPLEMENTATION_GUIDE.md
✅ SUMMARY_BAHASA_INDONESIA.md
```

### Diubah (6 file)
```
✏️ app/Services/UploadService.php
✏️ app/Models/UploadHistory.php
✏️ app/Http/Controllers/UploadController.php
✏️ resources/views/upload/index.blade.php
✏️ resources/views/history/index.blade.php
✏️ resources/views/history/show.blade.php
```

---

## 🗑️ File yang Tidak Dibutuhkan

```
❌ backup_before_migration.sql → Pindahkan ke folder backup
❌ _ide_helper*.php → Tambahkan ke .gitignore
❌ .phpstorm.meta.php → Tambahkan ke .gitignore
```

---

## 🧪 Testing Checklist

```
[ ] Migration berhasil
[ ] Buat format → Tabel terbuat
[ ] Upload Append → Data bertambah
[ ] Upload Replace → Data lama terhapus
[ ] History menampilkan info lengkap
[ ] User A & B dari dept sama bisa lihat file masing-masing
[ ] User dari dept berbeda tidak bisa lihat data lain
```

---

## 🔍 Query Berguna

### Cek Tabel Department
```sql
SELECT tablename FROM pg_tables 
WHERE schemaname = 'public' 
AND tablename LIKE 'dept_%';
```

### Cek File Uploads
```sql
SELECT * FROM file_uploads 
ORDER BY uploaded_at DESC 
LIMIT 10;
```

### Cek Upload Mode
```sql
SELECT original_filename, upload_mode, uploaded_at 
FROM upload_histories 
ORDER BY uploaded_at DESC;
```

### Cek Data di Tabel Department
```sql
SELECT COUNT(*) FROM dept_finance_tracks;
```

---

## 🚨 Troubleshooting

### Tabel tidak terbuat
```
→ Cek log: storage/logs/laravel.log
→ Cek permission database
→ Hapus format dan buat ulang
```

### Data tidak masuk
```
→ Cek mapping kolom
→ Cek error details di history
→ Cek log
```

### Replace tidak menghapus
```
→ Cek mode yang dipilih
→ Cek log untuk konfirmasi delete
→ Cek upload_mode di database
```

---

## 📞 Dokumentasi Lengkap

```
📄 SUMMARY_BAHASA_INDONESIA.md → Ringkasan lengkap
📄 IMPLEMENTATION_GUIDE.md → Panduan implementasi
📄 CHANGES_SUMMARY.md → Detail teknis
📄 FILES_CHANGED.md → Daftar file yang diubah
📄 QUICK_REFERENCE.md → Quick reference (file ini)
```

---

## ✅ Status

```
✅ Backend: Complete
✅ Frontend: Complete
✅ Database: Complete
✅ Documentation: Complete
✅ Testing: Ready
```

**Ready to Use!** 🎉

---

**Last Updated:** 30 September 2025
