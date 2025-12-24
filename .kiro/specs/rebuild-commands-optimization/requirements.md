# Requirements Document

## Introduction

Dokumen ini mendefinisikan kebutuhan untuk optimasi Artisan Commands rebuild rekapitulasi pada sistem SISFOZIS. Saat ini, proses rebuild rekapitulasi (ZIS, HakAmil, Setor, Pendis, Unit, Alokasi) memiliki masalah performa yang signifikan ketika dijalankan pada skala production dengan 5,880 unit ZIS dan 326,000+ transaksi. Optimasi ini bertujuan untuk mengurangi waktu eksekusi, penggunaan memory, dan beban database tanpa mengganggu operasional server.

## Glossary

-   **Rebuild Command**: Artisan command yang membangun ulang tabel rekapitulasi dari data transaksi mentah
-   **RekapZis**: Tabel rekapitulasi penerimaan ZIS (Zakat Fitrah, Zakat Mal, Infak/Sedekah)
-   **RekapPendis**: Tabel rekapitulasi pendistribusian dana ZIS
-   **RekapSetor**: Tabel rekapitulasi setoran dana ke unit induk
-   **RekapHakAmil**: Tabel rekapitulasi hak amil dari dana ZIS
-   **RekapUnit**: Tabel rekapitulasi agregat per unit
-   **RekapAlokasi**: Tabel rekapitulasi alokasi dana
-   **Chunking**: Teknik memproses data dalam batch kecil untuk mengurangi penggunaan memory
-   **Queue Job**: Background process yang dijalankan secara asynchronous
-   **Composite Index**: Database index yang mencakup multiple columns

## Requirements

### Requirement 1

**User Story:** As a system administrator, I want rebuild commands to process data in chunks, so that the server memory is not exhausted when processing large datasets.

#### Acceptance Criteria

1. WHEN a rebuild command is executed with --unit=all option THEN the System SHALL process units in configurable chunk sizes (default 50 units per chunk)
2. WHEN processing each chunk THEN the System SHALL release memory after completing each chunk before processing the next
3. WHEN a user specifies --chunk-size option THEN the System SHALL use the specified chunk size for processing
4. IF memory usage exceeds 80% of PHP memory limit THEN the System SHALL log a warning and pause processing temporarily

### Requirement 2

**User Story:** As a system administrator, I want rebuild commands to run as background queue jobs, so that the server remains responsive during long-running rebuild operations.

#### Acceptance Criteria

1. WHEN a user executes rebuild command with --queue option THEN the System SHALL dispatch the rebuild process as queue jobs instead of running synchronously
2. WHEN queue jobs are dispatched THEN the System SHALL create separate jobs for each unit chunk to enable parallel processing
3. WHEN a queue job fails THEN the System SHALL retry the job up to 3 times before marking it as failed
4. WHEN queue processing is active THEN the System SHALL provide progress tracking via artisan command output or log files

### Requirement 3

**User Story:** As a system administrator, I want rebuild commands to use optimized database queries, so that the rebuild process completes faster with less database load.

#### Acceptance Criteria

1. WHEN aggregating transaction data THEN the System SHALL use batch queries instead of individual queries per date per unit
2. WHEN inserting or updating rekap records THEN the System SHALL use bulk upsert operations instead of individual updateOrCreate calls
3. WHEN querying transaction tables THEN the System SHALL utilize composite indexes on (unit_id, trx_date) columns
4. WHEN processing daily rekapitulasi THEN the System SHALL aggregate data at database level using GROUP BY instead of PHP loops

### Requirement 4

**User Story:** As a system administrator, I want to see progress and estimated completion time during rebuild operations, so that I can plan maintenance windows accordingly.

#### Acceptance Criteria

1. WHEN a rebuild command starts THEN the System SHALL display total units and estimated completion time based on historical processing speed
2. WHILE rebuild is in progress THEN the System SHALL update progress bar with percentage complete and units processed
3. WHEN a rebuild command completes THEN the System SHALL display summary including total time, units processed, and any errors encountered
4. WHEN errors occur during processing THEN the System SHALL log detailed error information without stopping the entire process

### Requirement 5

**User Story:** As a system administrator, I want rebuild commands to have consistent interface and behavior, so that I can use them predictably across different rekap types.

#### Acceptance Criteria

1. WHEN any rebuild command is executed THEN the System SHALL accept standardized options: --unit, --start, --end, --periode, --chunk-size, --queue
2. WHEN validating date inputs THEN the System SHALL verify format Y-m-d and reject invalid dates with clear error messages
3. WHEN validating periode option THEN the System SHALL accept only valid values: harian, bulanan, tahunan, all
4. WHEN no date range is specified THEN the System SHALL default to last 30 days instead of last month to ensure consistent behavior

### Requirement 6

**User Story:** As a developer, I want rebuild services to be refactored with shared base functionality, so that code duplication is minimized and maintenance is easier.

#### Acceptance Criteria

1. WHEN implementing rebuild services THEN the System SHALL use a base abstract class or trait for common functionality
2. WHEN processing periodic rekapitulasi THEN the System SHALL avoid redundant recalculation of monthly/yearly data during daily processing
3. WHEN updating rekapitulasi THEN the System SHALL use database transactions to ensure data consistency
4. WHEN an error occurs mid-process THEN the System SHALL rollback only the affected unit's data, not the entire batch
