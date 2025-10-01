# Panduan Pembuatan Tabel di Upload Center

## 🎯 Konsep

Saat user membuat format baru, sistem akan **otomatis membuat tabel baru di database** dengan nama yang dipilih user.

---

## 📊 Struktur Penamaan Tabel

### Format Nama Tabel:
```
dept_{kode_department}_{nama_tabel_yang_dipilih}
```

### Contoh:
| Department | Nama Tabel Dipilih | Tabel di Database |
|------------|-------------------|-------------------|
| Finance (FIN) | `produk` | `dept_fin_produk` |
| HR | `karyawan` | `dept_hr_karyawan` |
| IT | `pembelian` | `dept_it_pembelian` |
| Sales | `penjualan` | `dept_sales_penjualan` |

---

## 🚀 Cara Membuat Tabel Baru

### Langkah 1: Buka Menu Create Format
```
Menu → Formats → Tambah Format Baru
```

### Langkah 2: Isi Form

**1. Nama Format**
```
Contoh: Format Data Produk 2025
```

**2. Deskripsi (Opsional)**
```
Contoh: Format untuk upload data produk bulanan
```

**3. Kolom yang Diharapkan**
```
Contoh:
- Kode Produk
- Nama Produk
- Kategori
- Harga
- Stok
```

**4. Nama Tabel Baru** ⭐ **PENTING**
```
Masukkan nama tabel yang Anda inginkan
Contoh: produk, karyawan, penjualan, inventory

Aturan:
✅ Hanya huruf kecil (a-z)
✅ Angka (0-9)
✅ Underscore (_)
❌ Tidak boleh spasi
❌ Tidak boleh karakter khusus
```

### Langkah 3: Submit
- Klik **"Simpan Format"**
- Sistem akan:
  1. ✅ Validasi nama tabel
  2. ✅ Cek apakah tabel sudah ada
  3. ✅ Buat tabel baru di database
  4. ✅ Simpan format

### Langkah 4: Verifikasi
- Lihat di halaman **Formats**
- Tabel database akan ditampilkan dengan prefix department
- Contoh: `dept_finance_produk`

---

## 🎨 Contoh Penggunaan

### Contoh 1: Department Finance - Tabel Produk

**Input:**
- Nama Format: `Format Data Produk`
- Nama Tabel: `produk`
- Kolom: Kode Produk, Nama Produk, Kategori, Harga, Stok

**Output:**
- Tabel dibuat: `dept_fin_produk`
- Struktur:
  ```sql
  CREATE TABLE dept_fin_produk (
      id BIGSERIAL PRIMARY KEY,
      upload_history_id BIGINT,
      department_id BIGINT,
      kode_produk VARCHAR(500),
      nama_produk VARCHAR(500),
      kategori VARCHAR(500),
      harga VARCHAR(500),
      stok VARCHAR(500),
      created_at TIMESTAMP,
      updated_at TIMESTAMP
  );
  ```

### Contoh 2: Department HR - Tabel Karyawan

**Input:**
- Nama Format: `Format Data Karyawan`
- Nama Tabel: `karyawan`
- Kolom: NIK, Nama, Jabatan, Departemen, Gaji

**Output:**
- Tabel dibuat: `dept_hr_karyawan`
- Struktur:
  ```sql
  CREATE TABLE dept_hr_karyawan (
      id BIGSERIAL PRIMARY KEY,
      upload_history_id BIGINT,
      department_id BIGINT,
      nik VARCHAR(500),
      nama VARCHAR(500),
      jabatan VARCHAR(500),
      departemen VARCHAR(500),
      gaji VARCHAR(500),
      created_at TIMESTAMP,
      updated_at TIMESTAMP
  );
  ```

### Contoh 3: Department Sales - Tabel Penjualan

**Input:**
- Nama Format: `Format Laporan Penjualan`
- Nama Tabel: `penjualan`
- Kolom: Tanggal, No Invoice, Customer, Total, Status

**Output:**
- Tabel dibuat: `dept_sales_penjualan`
- Struktur:
  ```sql
  CREATE TABLE dept_sales_penjualan (
      id BIGSERIAL PRIMARY KEY,
      upload_history_id BIGINT,
      department_id BIGINT,
      tanggal VARCHAR(500),
      no_invoice VARCHAR(500),
      customer VARCHAR(500),
      total VARCHAR(500),
      status VARCHAR(500),
      created_at TIMESTAMP,
      updated_at TIMESTAMP
  );
  ```

---

## ✅ Validasi dan Keamanan

### 1. Validasi Nama Tabel
```
✅ Hanya huruf kecil, angka, dan underscore
✅ Tidak boleh duplikat dalam department yang sama
✅ Otomatis dinormalisasi (spasi → underscore, huruf besar → kecil)
```

### 2. Isolasi Department
```
✅ Setiap department punya tabel sendiri
✅ Department Finance tidak bisa akses tabel HR
✅ Prefix department mencegah konflik nama
```

### 3. Kolom Wajib
Setiap tabel otomatis memiliki kolom:
```sql
- id (Primary Key)
- upload_history_id (Foreign Key)
- department_id (Foreign Key)
- created_at (Timestamp)
- updated_at (Timestamp)
```

---

## 🔍 Cek Tabel yang Sudah Dibuat

### Via UI
```
Menu → Formats → Lihat daftar format
→ Setiap format menampilkan nama tabel database
```

### Via Database
```sql
-- Lihat semua tabel department
SELECT tablename 
FROM pg_tables 
WHERE schemaname = 'public' 
AND tablename LIKE 'dept_%'
ORDER BY tablename;

-- Lihat tabel untuk department tertentu
SELECT tablename 
FROM pg_tables 
WHERE schemaname = 'public' 
AND tablename LIKE 'dept_fin_%';

-- Lihat struktur tabel
\d dept_fin_produk
```

---

## ⚠️ Error Handling

### Error 1: Nama Tabel Sudah Ada
**Pesan:**
```
Tabel "produk" sudah ada di department Anda. 
Gunakan nama tabel yang berbeda atau gunakan format yang sudah ada.
```

**Solusi:**
- Gunakan nama tabel yang berbeda (contoh: `produk_2025`, `produk_baru`)
- Atau gunakan format yang sudah ada untuk upload

### Error 2: Nama Tabel Tidak Valid
**Pesan:**
```
Nama tabel hanya boleh mengandung huruf kecil, angka, dan underscore (_)
```

**Solusi:**
- Ubah nama tabel sesuai aturan
- Contoh yang benar: `produk`, `data_karyawan`, `penjualan_2025`
- Contoh yang salah: `Produk`, `data karyawan`, `penjualan@2025`

### Error 3: Department Tidak Terdaftar
**Pesan:**
```
Anda belum terdaftar di department manapun.
```

**Solusi:**
- Hubungi administrator untuk mendaftarkan Anda ke department

---

## 📝 Best Practices

### 1. Penamaan Tabel
```
✅ Gunakan nama yang deskriptif
✅ Gunakan huruf kecil
✅ Gunakan underscore untuk pemisah kata
✅ Singkat tapi jelas

Contoh bagus:
- produk
- karyawan
- penjualan
- inventory
- data_customer
- laporan_bulanan

Contoh kurang bagus:
- tbl1
- data
- temp
- test
```

### 2. Kolom
```
✅ Sesuaikan dengan header Excel Anda
✅ Gunakan nama yang jelas
✅ Konsisten dengan format Excel

Contoh:
Excel Header: "Kode Produk" → Kolom: "Kode Produk"
Excel Header: "Nama" → Kolom: "Nama"
```

### 3. Dokumentasi
```
✅ Isi deskripsi format dengan jelas
✅ Jelaskan untuk apa format ini digunakan
✅ Catat perubahan jika ada update

Contoh deskripsi:
"Format untuk upload data produk bulanan dari sistem inventory. 
Digunakan oleh tim Finance untuk rekonsiliasi stok."
```

---

## 🎓 FAQ

### Q: Apakah saya bisa membuat banyak tabel?
**A:** Ya! Anda bisa membuat sebanyak mungkin tabel sesuai kebutuhan department Anda.

### Q: Apakah nama tabel harus unik?
**A:** Ya, dalam department yang sama. Tapi department berbeda bisa punya nama tabel yang sama.

### Q: Bagaimana jika saya salah membuat tabel?
**A:** Hubungi administrator untuk menghapus format dan tabel. Kemudian buat ulang dengan benar.

### Q: Apakah saya bisa mengubah struktur tabel setelah dibuat?
**A:** Saat ini belum ada fitur edit. Jika perlu ubah, buat format baru dengan nama tabel berbeda.

### Q: Apakah department lain bisa lihat tabel saya?
**A:** Tidak! Setiap department memiliki tabel sendiri yang terisolasi.

### Q: Bagaimana cara menghapus tabel?
**A:** Hubungi administrator. Penghapusan tabel harus dilakukan dengan hati-hati karena akan menghapus semua data.

---

## 🔧 Troubleshooting

### Problem: Tabel tidak terbuat
**Cek:**
1. Apakah ada error message?
2. Cek log: `storage/logs/laravel.log`
3. Cek permission database
4. Cek koneksi database

**Solusi:**
```bash
# Cek log
tail -f storage/logs/laravel.log

# Cek database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

### Problem: Tidak bisa upload ke tabel
**Cek:**
1. Apakah tabel benar-benar ada di database?
2. Apakah kolom sesuai dengan format?
3. Cek error di history upload

**Solusi:**
```sql
-- Cek apakah tabel ada
SELECT * FROM pg_tables WHERE tablename = 'dept_fin_produk';

-- Cek struktur tabel
\d dept_fin_produk

-- Cek data di tabel
SELECT COUNT(*) FROM dept_fin_produk;
```

---

## 📞 Support

Jika ada pertanyaan atau masalah:
1. Cek dokumentasi ini
2. Cek log di `storage/logs/laravel.log`
3. Hubungi administrator sistem

---

**Last Updated:** 30 September 2025
**Version:** 2.0
