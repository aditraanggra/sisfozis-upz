# Rekap Rebuild Commands

Dokumentasi untuk artisan commands yang digunakan untuk membangun ulang data rekapitulasi.

## Daftar Commands

| Command                  | Deskripsi                                                          |
| ------------------------ | ------------------------------------------------------------------ |
| `rekap:rebuild-zis`      | Rebuild rekapitulasi ZIS (Zakat Fitrah, Zakat Maal, Infak/Sedekah) |
| `rekap:rebuild-setor`    | Rebuild rekapitulasi Setoran                                       |
| `rekap:rebuild-pendis`   | Rebuild rekapitulasi Pendistribusian                               |
| `rekap:rebuild-alokasi`  | Rebuild rekapitulasi Alokasi                                       |
| `rekap:rebuild-hak-amil` | Rebuild rekapitulasi Hak Amil                                      |

## Options

Semua command memiliki options yang sama:

| Option         | Default      | Deskripsi                                                |
| -------------- | ------------ | -------------------------------------------------------- |
| `--unit`       | `all`        | ID unit spesifik atau `all` untuk semua unit             |
| `--start`      | 30 hari lalu | Tanggal mulai (format: `Y-m-d`)                          |
| `--end`        | Hari ini     | Tanggal akhir (format: `Y-m-d`)                          |
| `--periode`    | `all`        | Tipe periode: `harian`, `bulanan`, `tahunan`, atau `all` |
| `--chunk-size` | `50`         | Jumlah unit per batch                                    |
| `--queue`      | -            | Jalankan sebagai background job                          |

## Contoh Penggunaan

### Rebuild semua data (default 30 hari terakhir)

```bash
php artisan rekap:rebuild-zis
php artisan rekap:rebuild-setor
php artisan rekap:rebuild-pendis
php artisan rekap:rebuild-alokasi
php artisan rekap:rebuild-hak-amil
```

### Rebuild untuk unit tertentu

```bash
php artisan rekap:rebuild-zis --unit=1
```

### Rebuild dengan rentang tanggal

```bash
php artisan rekap:rebuild-zis --start=2025-01-01 --end=2025-01-31
```

### Rebuild periode tertentu saja

```bash
# Hanya harian
php artisan rekap:rebuild-zis --periode=harian

# Hanya bulanan
php artisan rekap:rebuild-zis --periode=bulanan

# Hanya tahunan
php artisan rekap:rebuild-zis --periode=tahunan
```

### Rebuild dengan queue (background job)

```bash
php artisan rekap:rebuild-zis --queue

# Monitor queue
php artisan queue:work --queue=rebuild
```

### Kombinasi options

```bash
php artisan rekap:rebuild-setor --unit=5 --start=2025-01-01 --end=2025-03-31 --periode=bulanan --chunk-size=100
```

## Urutan Rebuild yang Disarankan

Untuk rebuild lengkap, jalankan dalam urutan berikut:

```bash
# 1. Rebuild data transaksi ZIS terlebih dahulu
php artisan rekap:rebuild-zis --start=2025-01-01

# 2. Rebuild alokasi (bergantung pada rekap ZIS)
php artisan rekap:rebuild-alokasi --start=2025-01-01

# 3. Rebuild setoran
php artisan rekap:rebuild-setor --start=2025-01-01

# 4. Rebuild pendistribusian
php artisan rekap:rebuild-pendis --start=2025-01-01

# 5. Rebuild hak amil
php artisan rekap:rebuild-hak-amil --start=2025-01-01
```

## Arsitektur

### Service Classes

-   `RekapZisService` - Agregasi data Zakat Fitrah, Zakat Maal, Infak/Sedekah
-   `RekapSetorService` - Agregasi data setoran ke BAZNAS
-   `RekapPendisService` - Agregasi data pendistribusian ke mustahik
-   `RekapAlokasiService` - Perhitungan alokasi dana
-   `RekapHakAmilService` - Perhitungan hak amil

### Base Classes

-   `BaseRebuildCommand` - Abstract command dengan standardized interface
-   `BaseRekapService` - Abstract service dengan chunked processing dan bulk upsert

### Fitur

-   Chunked processing untuk menghindari memory overflow
-   Bulk upsert untuk performa optimal
-   Error isolation per unit (satu unit gagal tidak menghentikan proses)
-   Support background queue processing
-   Progress reporting dan ringkasan hasil
