# Implementation Plan

-   [x] 1. Create database migration for composite indexes

    -   [x] 1.1 Create migration file for transaction table indexes (zfs, zms, ifs, distributions)

        -   Add composite index on (unit_id, trx_date) for each transaction table
        -   _Requirements: 3.3_

    -   [x] 1.2 Create migration file for rekap table unique indexes

        -   Add unique composite index on (unit_id, period, period_date) for rekap tables
        -   _Requirements: 3.2, 3.3_

-   [x] 2. Implement BaseRekapService abstract class

    -   [x] 2.1 Create BaseRekapService with core methods

        -   Implement setChunkSize(), bulkUpsert(), processInChunks()
        -   Define abstract methods rebuild() and getAggregatedData()
        -   _Requirements: 6.1, 1.1, 1.2_

    -   [ ]\* 2.2 Write property test for chunking preserves total unit count
        -   **Property 1: Chunking preserves total unit count**
        -   **Validates: Requirements 1.1**
    -   [ ]\* 2.3 Write property test for error isolation
        -   **Property 7: Error isolation preserves other units**
        -   **Validates: Requirements 4.4, 6.4**

-   [x] 3. Refactor RekapZisService to extend BaseRekapService

    -   [x] 3.1 Update RekapZisService to extend BaseRekapService

        -   Implement rebuild() method with batch processing
        -   Implement getAggregatedData() with GROUP BY queries
        -   Remove redundant periodic recalculation from daily processing
        -   _Requirements: 3.1, 3.4, 6.2_

    -   [ ]\* 3.2 Write property test for batch query count
        -   **Property 3: Batch query count is constant per unit**
        -   **Validates: Requirements 3.1**
    -   [ ]\* 3.3 Write property test for bulk upsert
        -   **Property 4: Bulk upsert receives all records**
        -   **Validates: Requirements 3.2**
    -   [ ]\* 3.4 Write property test for daily processing independence
        -   **Property 8: Daily processing independence**
        -   **Validates: Requirements 6.2**

-   [x] 4. Checkpoint - Ensure all tests pass

    -   Ensure all tests pass, ask the user if questions arise.

-   [x] 5. Refactor remaining Rekap Services

    -   [x] 5.1 Update RekapPendisService to extend BaseRekapService

        -   Implement rebuild() and getAggregatedData() methods
        -   Remove redundant periodic recalculation from daily processing
        -   _Requirements: 3.1, 6.1, 6.2_

    -   [x] 5.2 Update RekapSetorService to extend BaseRekapService

        -   Implement rebuild() and getAggregatedData() methods
        -   Remove redundant periodic recalculation from daily processing
        -   _Requirements: 3.1, 6.1, 6.2_

    -   [x] 5.3 Update RekapHakAmilService to extend BaseRekapService

        -   Implement rebuild() and getAggregatedData() methods
        -   Remove redundant periodic recalculation from daily processing
        -   _Requirements: 3.1, 6.1, 6.2_

    -   [x] 5.4 Update RekapUnitService to use optimized approach

        -   Leverage existing chunking, add bulk upsert
        -   _Requirements: 3.2, 6.1_

    -   [x] 5.5 Update RekapAlokasiService to use optimized approach

        -   Add chunking and bulk upsert support
        -   _Requirements: 3.2, 6.1_

-   [x] 6. Create RebuildRekapJob for queue processing

    -   [x] 6.1 Create RebuildRekapJob class

        -   Implement ShouldQueue interface
        -   Set tries=3, timeout=3600
        -   Handle unit chunk processing
        -   _Requirements: 2.1, 2.3_

    -   [ ]\* 6.2 Write property test for queue job count
        -   **Property 2: Queue job count matches chunk calculation**
        -   **Validates: Requirements 2.2**
    -   [ ]\* 6.3 Write unit tests for job configuration
        -   Verify $tries and $timeout values
        -   _Requirements: 2.3_

-   [x] 7. Implement BaseRebuildCommand abstract class

    -   [x] 7.1 Create BaseRebuildCommand with standardized interface

        -   Define common signature options (--unit, --start, --end, --periode, --chunk-size, --queue)
        -   Implement validateInputs(), dispatchToQueue(), runSync()
        -   Implement progress display and summary output
        -   _Requirements: 5.1, 5.2, 5.3, 5.4, 4.1, 4.3_

    -   [ ]\* 7.2 Write property test for input validation
        -   **Property 5: Input validation rejects invalid formats**
        -   **Validates: Requirements 5.2, 5.3**
    -   [ ]\* 7.3 Write property test for command interface consistency
        -   **Property 6: Command interface consistency**
        -   **Validates: Requirements 5.1**

-   [x] 8. Checkpoint - Ensure all tests pass

    -   Ensure all tests pass, ask the user if questions arise.

-   [x] 9. Refactor existing rebuild commands

    -   [x] 9.1 Update RebuildRekapitulasi to extend BaseRebuildCommand

        -   Set serviceClass to RekapZisService
        -   Remove duplicated logic, use inherited methods
        -   _Requirements: 5.1_

    -   [x] 9.2 Update RebuildRekapPendis to extend BaseRebuildCommand

        -   Set serviceClass to RekapPendisService
        -   _Requirements: 5.1_

    -   [x] 9.3 Update RebuildRekapSetor to extend BaseRebuildCommand

        -   Set serviceClass to RekapSetorService
        -   _Requirements: 5.1_

    -   [x] 9.4 Update RebuildRekapHakAmil to extend BaseRebuildCommand

        -   Set serviceClass to RekapHakAmilService
        -   _Requirements: 5.1_

    -   [x] 9.5 Update RebuildRekapUnit to extend BaseRebuildCommand

        -   Set serviceClass to RekapUnitService
        -   _Requirements: 5.1_

    -   [x] 9.6 Update RebuildRekapAlokasi to extend BaseRebuildCommand

        -   Standardize signature to match other commands (--unit, --start, --end, --periode, --chunk-size, --queue)
        -   _Requirements: 5.1_

-   [x] 10. Final integration and documentation

    -   [x] 10.1 Update queue configuration for rebuild queue

        -   Add 'rebuild' queue to queue worker configuration
        -   _Requirements: 2.1_

    -   [ ]\* 10.2 Write integration tests for full rebuild flow
        -   Test single unit rebuild end-to-end
        -   Test queue dispatch and execution
        -   _Requirements: 2.1, 3.1, 3.2_

-   [ ] 11. Final Checkpoint - Ensure all tests pass
    -   Ensure all tests pass, ask the user if questions arise.
