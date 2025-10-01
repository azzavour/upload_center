-- Script untuk menghapus tabel-tabel department yang sudah ada
-- HATI-HATI: Ini akan menghapus semua data di tabel-tabel tersebut!
-- Jalankan hanya jika Anda yakin ingin menghapus semua tabel department

-- Backup dulu jika perlu!

-- Drop tabel-tabel department
DROP TABLE IF EXISTS dept_fin_produk CASCADE;
DROP TABLE IF EXISTS dept_hr_produk CASCADE;
DROP TABLE IF EXISTS dept_it_pembelian CASCADE;
DROP TABLE IF EXISTS dept_it_produk CASCADE;
DROP TABLE IF EXISTS dept_it_tracks CASCADE;
DROP TABLE IF EXISTS dept_ops_produk CASCADE;
DROP TABLE IF EXISTS dept_sales_penjualan CASCADE;
DROP TABLE IF EXISTS dept_sales_penjualan_coffee CASCADE;

-- Jika ada tabel lain dengan prefix dept_, tambahkan di sini
-- DROP TABLE IF EXISTS dept_xxx_xxx CASCADE;

-- Setelah ini, user bisa membuat tabel baru dari form Create Format
