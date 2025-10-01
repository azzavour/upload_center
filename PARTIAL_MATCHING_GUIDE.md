# Panduan Partial Matching - Upload Center

## 🎯 Konsep Baru

Sistem sekarang menggunakan **Partial Matching** untuk mapping kolom Excel.

### Sebelum (Exact Matching):
```
Excel: Tanggal, Jenis Produk, Jumlah Order, Harga, Total
Mapping: jenis_produk, tanggal, harga, total
Result: ❌ Tidak match (karena ada kolom tambahan "Jumlah Order")
Action: Buat mapping baru
```

### Sesudah (Partial Matching):
```
Excel: Tanggal, Jenis Produk, Jumlah Order, Harga, Total
Mapping: jenis_produk, tanggal, harga, total
Result: ✅ Match! (semua kolom mapping ada di Excel)
Action: Gunakan mapping yang ada
Warning: Kolom "Jumlah Order" akan diabaikan (ditampilkan kuning)
```

---

## 🔄 Cara Kerja

### 1. **Deteksi Mapping**
```php
// Cek apakah semua kolom mapping ada di Excel
$mappingColumnsExistInExcel = empty(array_diff($normalizedMappingColumns, $normalizedExcelColumns));

if ($mappingColumnsExistInExcel) {
    // Gunakan mapping ini
    return $mapping;
}
```

### 2. **Deteksi Kolom Diabaikan**
```php
// Kolom yang ada di Excel tapi tidak ada di mapping
$ignoredColumns = array_diff($normalizedExcelColumns, $normalizedMappingColumns);
```

### 3. **Proses Upload**
- Kolom yang ada di mapping → Diproses ✅
- Kolom yang tidak ada di mapping → Diabaikan ⚠️
- Data tetap masuk ke database dengan kolom yang dimapping

---

## 📊 Contoh Skenario

### Skenario 1: Excel Punya Kolom Tambahan

**Mapping yang Ada:**
```
jenis_produk → produk
tanggal → tanggal
harga → harga
total → total
```

**File Excel:**
```
Tanggal, Jenis Produk, Jumlah Order, Harga, Total
```

**Hasil:**
- ✅ Mapping ditemukan (partial match)
- ⚠️ Kolom diabaikan: `Jumlah Order`
- ✅ Data diproses: `tanggal`, `jenis_produk`, `harga`, `total`

### Skenario 2: Excel Kurang Kolom

**Mapping yang Ada:**
```
jenis_produk → produk
tanggal → tanggal
harga → harga
total → total
jumlah_order → jumlah
```

**File Excel:**
```
Tanggal, Jenis Produk, Harga, Total
```

**Hasil:**
- ❌ Mapping tidak ditemukan (kolom `jumlah_order` tidak ada di Excel)
- 🔄 Redirect ke create mapping baru

### Skenario 3: Excel Sama Persis

**Mapping yang Ada:**
```
jenis_produk → produk
tanggal → tanggal
harga → harga
total → total
```

**File Excel:**
```
Tanggal, Jenis Produk, Harga, Total
```

**Hasil:**
- ✅ Mapping ditemukan (exact match)
- ✅ Tidak ada kolom diabaikan
- ✅ Data diproses semua

---

## 🎨 UI Warning (Yang Perlu Ditambahkan)

### Di Halaman Upload Preview:

```html
<!-- Kolom yang Akan Diproses -->
<div class="alert alert-success">
    <strong>Kolom yang Akan Diproses:</strong>
    <span class="badge bg-success">Tanggal</span>
    <span class="badge bg-success">Jenis Produk</span>
    <span class="badge bg-success">Harga</span>
    <span class="badge bg-success">Total</span>
</div>

<!-- Kolom yang Diabaikan -->
<div class="alert alert-warning">
    <strong>⚠️ Kolom yang Akan Diabaikan:</strong>
    <span class="badge bg-warning">Jumlah Order</span>
    <small class="d-block mt-2">
        Kolom ini tidak ada di mapping dan tidak akan disimpan ke database.
    </small>
</div>
```

---

## 🔧 Implementasi

### File yang Sudah Diubah:

1. ✅ `app/Services/MappingService.php`
   - Method `findMappingByExcelColumns()` - Partial matching logic
   - Method `getIgnoredColumns()` - Deteksi kolom diabaikan
   - Method `normalizeColumn()` - Helper

### File yang Perlu Diubah:

2. ⏳ `app/Http/Controllers/UploadController.php`
   - Tambah logic untuk detect ignored columns
   - Pass ke view

3. ⏳ `resources/views/upload/index.blade.php`
   - Tampilkan warning kolom diabaikan (kuning)
   - Tampilkan kolom yang diproses (hijau)

---

## 📝 Code yang Perlu Ditambahkan

### Di UploadController (method yang handle preview):

```php
// Setelah menemukan mapping
if ($mapping) {
    $ignoredColumns = $this->mappingService->getIgnoredColumns(
        $excelColumns, 
        $mapping->column_mapping
    );
    
    return view('upload.preview', [
        'mapping' => $mapping,
        'excelColumns' => $excelColumns,
        'ignoredColumns' => $ignoredColumns,
        'processedColumns' => array_keys($mapping->column_mapping)
    ]);
}
```

### Di View (upload/index.blade.php):

```blade
@if(!empty($ignoredColumns))
<div class="alert alert-warning">
    <h6 class="alert-heading">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Kolom yang Akan Diabaikan
    </h6>
    <p class="mb-2">Kolom berikut ada di file Excel Anda tapi tidak ada di mapping, sehingga tidak akan disimpan:</p>
    <div>
        @foreach($ignoredColumns as $col)
        <span class="badge bg-warning text-dark me-1 mb-1">{{ $col }}</span>
        @endforeach
    </div>
    <small class="text-muted mt-2 d-block">
        <i class="fas fa-info-circle me-1"></i>
        Jika Anda ingin menyimpan kolom ini, buat mapping baru atau edit mapping yang ada.
    </small>
</div>
@endif
```

---

## ✅ Keuntungan

1. **Lebih Fleksibel** - User tidak perlu buat mapping baru jika hanya ada kolom tambahan
2. **Lebih Jelas** - User tahu kolom mana yang diproses dan mana yang diabaikan
3. **Lebih Efisien** - Tidak ada duplikasi mapping yang tidak perlu
4. **Backward Compatible** - Mapping lama tetap berfungsi

---

## 🧪 Testing

### Test Case 1: File dengan Kolom Tambahan
```
1. Upload file dengan kolom: Tanggal, Jenis Produk, Jumlah Order, Harga, Total
2. Mapping ada: jenis_produk, tanggal, harga, total
3. Expected: 
   - ✅ Mapping ditemukan
   - ⚠️ Warning: "Jumlah Order" diabaikan
   - ✅ Data masuk: tanggal, jenis_produk, harga, total
```

### Test Case 2: File Sama Persis
```
1. Upload file dengan kolom: Tanggal, Jenis Produk, Harga, Total
2. Mapping ada: jenis_produk, tanggal, harga, total
3. Expected:
   - ✅ Mapping ditemukan
   - ✅ Tidak ada warning
   - ✅ Data masuk semua
```

### Test Case 3: File Kurang Kolom
```
1. Upload file dengan kolom: Tanggal, Harga, Total
2. Mapping ada: jenis_produk, tanggal, harga, total
3. Expected:
   - ❌ Mapping tidak ditemukan
   - 🔄 Redirect ke create mapping
```

---

## 📞 Status

**Backend:** ✅ Complete
**Frontend:** ⏳ Pending (perlu tambah UI warning)

---

**Last Updated:** 1 Oktober 2025
