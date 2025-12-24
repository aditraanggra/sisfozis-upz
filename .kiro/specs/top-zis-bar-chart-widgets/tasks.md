# Implementation Plan

-   [x] 1. Create Top10KecamatanChart widget

    -   [x] 1.1 Create widget class extending ChartWidget with InteractsWithPageFilters trait

        -   Create `app/Filament/Widgets/Top10KecamatanChart.php`
        -   Implement `canView()` method to restrict to super_admin and tim_sisfo roles
        -   Implement `getData()` method to return bar chart data
        -   Implement `getType()` method returning 'bar'
        -   Add query methods for calculating total ZIS per district
        -   Apply date filters from page filters (year, startDate, endDate)
        -   Limit results to top 10 with non-zero total_zis
        -   _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

    -   [ ]\* 1.2 Write property test for Top10KecamatanChart
        -   **Property 1: Top 10 ranking is correctly sorted descending**
        -   **Property 5: Role-based visibility for Kecamatan widget**
        -   **Validates: Requirements 1.1, 1.5**

-   [x] 2. Create Top10DesaChart widget

    -   [x] 2.1 Create widget class with ZisScope integration

        -   Create `app/Filament/Widgets/Top10DesaChart.php`
        -   Implement `getData()` method with ZisScope applied to ZIS queries
        -   Implement `getType()` method returning 'bar'
        -   Add query methods for calculating total ZIS per village
        -   Apply date filters from page filters
        -   Apply ZisScope for role-based geographic filtering
        -   Limit results to top 10 with non-zero total_zis
        -   _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

    -   [ ]\* 2.2 Write property test for Top10DesaChart
        -   **Property 2: Zero ZIS exclusion**
        -   **Property 6: ZisScope application for Desa widget**
        -   **Validates: Requirements 2.3, 2.5**

-   [x] 3. Create Top10DkmChart widget

    -   [x] 3.1 Create widget class with ZisScope and DKM category filter

        -   Create `app/Filament/Widgets/Top10DkmChart.php`
        -   Implement `getData()` method with ZisScope and category filter
        -   Implement `getType()` method returning 'bar'
        -   Add query methods for calculating total ZIS per UPZ DKM
        -   Filter UnitZis by DKM category_id
        -   Apply date filters from page filters
        -   Apply ZisScope for role-based geographic filtering
        -   Limit results to top 10 with non-zero total_zis
        -   _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

    -   [ ]\* 3.2 Write property test for Top10DkmChart
        -   **Property 7: ZisScope application for DKM widget**
        -   **Property 8: DKM category filter**
        -   **Validates: Requirements 3.5, 3.6**

-   [x] 4. Integrate widgets into Dashboard

    -   [x] 4.1 Update Dashboard.php to include new widgets

        -   Add Top10KecamatanChart to getWidgets() array
        -   Add Top10DesaChart to getWidgets() array
        -   Add Top10DkmChart to getWidgets() array
        -   Set appropriate sort order for widget display
        -   _Requirements: 1.1, 2.1, 3.1_

-   [x] 5. Checkpoint - Ensure all tests pass

    -   Ensure all tests pass, ask the user if questions arise.

-   [ ]\* 6. Write integration property tests

    -   [ ]\* 6.1 Write property test for date filter consistency
        -   **Property 3: Date filter application consistency**
        -   **Validates: Requirements 1.2, 2.2, 3.2, 5.2**
    -   [ ]\* 6.2 Write property test for total ZIS calculation
        -   **Property 4: Total ZIS calculation accuracy**
        -   **Validates: Requirements 5.1**

-   [ ] 7. Final Checkpoint - Ensure all tests pass
    -   Ensure all tests pass, ask the user if questions arise.
