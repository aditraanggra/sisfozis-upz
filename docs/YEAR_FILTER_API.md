# Year-Based Data Filtering API Documentation

## Overview

The SISFOZIS backend API now supports dynamic year-based filtering for all transaction endpoints. This feature allows frontend applications to retrieve data filtered by specific years or all years at once.

## Filtering Mechanism

The API accepts a `year` query parameter that supports two modes:

1. **Specific Year**: Filter data for a specific calendar year (e.g., `year=2025`)
2. **All Years**: Return data from all available years (e.g., `year=all` or omit the parameter)

## Supported Endpoints

All transaction endpoints now support the `year` parameter:

### 1. Zakat Fitrah (ZF)
```
GET /api/zf?year=2025
GET /api/zf?year=all
GET /api/zf?year=2024&search=muzakki%20name
GET /api/zf?year=2023&start_date=2023-01-01&end_date=2023-12-31
```

### 2. Zakat Maal (ZM)
```
GET /api/zm?year=2025
GET /api/zm?year=all
GET /api/zm?year=2024&sort_by=no_telp&sort_direction=asc
GET /api/zm?year=2023&start_date=2023-01-01&end_date=2023-12-31
```

### 3. Infak/Sedekah (IFS)
```
GET /api/ifs?year=2025
GET /api/ifs?year=all
GET /api/ifs?year=2024&min_munfiq=1&max_munfiq=5
GET /api/ifs?year=2023&sort_by=total_munfiq&sort_direction=desc
GET /api/ifs?year=2024&per_page=20&page=2
```

### 4. Fidyah
```
GET /api/fidyah?year=2025
GET /api/fidyah?year=all
GET /api/fidyah?year=2024&search=pembayar
```

### 5. Kotak Amal (Donation Box)
```
GET /api/kotak_amal?year=2025
GET /api/kotak_amal?year=all
GET /api/kotak_amal?year=2024&start_date=2024-01-01&end_date=2024-12-31
```

### 6. Pendis (Distribution)
```
GET /api/pendis?year=2025
GET /api/pendis?year=all
GET /api/pendis?year=2024&search=mustahik
```

### 7. Setor ZIS
```
GET /api/setor?year=2025
GET /api/setor?year=all
GET /api/setor?year=2024&start_date=2024-01-01&end_date=2024-12-31
```

## Query Parameters

### Common Parameters

| Parameter | Type   | Description                                      |
| --------- | ------ | ------------------------------------------------ |
| year      | string | Filter by year: specific year (e.g., 2025) or 'all' |
| search    | string | Search text in transaction descriptions/names     |
| start_date| date  | Filter from this date                            |
| end_date  | date  | Filter until this date                          |
| per_page  | int    | Items per page (default: 15)                    |
| page      | int    | Page number (default: 1)                        |

### ZM Specific Parameters

| Parameter       | Type   | Description                     |
| -------------- | ------ | ------------------------------- |
| no_telp         | string | Filter by exact phone number    |
| sort_by         | string | Sort field (e.g., 'no_telp')    |
| sort_direction  | string | Sort direction ('asc' or 'desc') |

### IFS Specific Parameters

| Parameter       | Type   | Description                    |
| -------------- | ------ | ------------------------------ |
| total_munfiq   | int    | Filter exact total munfiq      |
| min_munfiq     | int    | Filter minimum total munfiq    |
| max_munfiq     | int    | Filter maximum total munfiq    |
| sort_by         | string | Sort field (e.g., 'total_munfiq') |
| sort_direction  | string | Sort direction ('asc' or 'desc') |

## Usage Examples

### Example 1: Get 2025 Data Only
```bash
GET /api/zf?year=2025
```

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "unit_id": 1,
            "trx_date": "2025-01-15",
            "muzakki_name": "Ahmad",
            "zf_rice": 2.5,
            "zf_amount": 50000,
            "total_muzakki": 3,
            "desc": "Test transaction"
        }
    ],
    "meta": {
        "total": 100,
        "per_page": 15,
        "current_page": 1,
        "total_pages": 7
    }
}
```

### Example 2: Get All Years Data
```bash
GET /api/zf?year=all
```

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "unit_id": 1,
            "trx_date": "2025-01-15",
            "muzakki_name": "Ahmad",
            "zf_rice": 2.5,
            "zf_amount": 50000,
            "total_muzakki": 3,
            "desc": "2025 transaction"
        },
        {
            "id": 2,
            "unit_id": 1,
            "trx_date": "2024-06-20",
            "muzakki_name": "Budi",
            "zf_rice": 3.0,
            "zf_amount": 60000,
            "total_muzakki": 2,
            "desc": "2024 transaction"
        }
    ]
}
```

### Example 3: Combine Year Filter with Other Filters
```bash
GET /api/ifs?year=2024&min_munfiq=2&max_munfiq=5&sort_by=total_munfiq&sort_direction=desc&per_page=20
```

**Response:**
```json
{
    "data": [
        {
            "id": 5,
            "unit_id": 1,
            "trx_date": "2024-03-10",
            "munfiq_name": "Group Donor",
            "amount": 500000,
            "total_munfiq": 5,
            "desc": "Large group donation"
        },
        {
            "id": 3,
            "unit_id": 1,
            "trx_date": "2024-02-15",
            "munfiq_name": "Family Donor",
            "amount": 250000,
            "total_munfiq": 3,
            "desc": "Family contribution"
        }
    ],
    "meta": {
        "total": 45,
        "per_page": 20,
        "current_page": 1,
        "total_pages": 3
    }
}
```

### Example 4: Get Statistics for a Specific Year
```bash
GET /api/zm/statistics?year=2024
```

**Response:**
```json
{
    "total_transactions": 9724,
    "total_amount": 4862000000,
    "total_with_phone": 2456,
    "total_without_phone": 7268,
    "average_amount": 500000,
    "highest_amount": 5000000,
    "phone_coverage": 25.24
}
```

## Performance Considerations

### Database Indexing
The API uses Laravel's Eloquent `whereYear()` method which generates efficient SQL queries:
```sql
SELECT * FROM zf WHERE YEAR(trx_date) = 2025
```

To optimize performance:
1. Ensure the `trx_date` column has an index
2. Consider using range queries (`WHERE trx_date >= '2025-01-01' AND trx_date <= '2025-12-31'`) for better index utilization
3. Monitor query performance with database profiling tools

### Recommended Indexes
```sql
-- For all transaction tables
ALTER TABLE zf ADD INDEX idx_trx_date (trx_date);
ALTER TABLE zm ADD INDEX idx_trx_date (trx_date);
ALTER TABLE ifs ADD INDEX idx_trx_date (trx_date);
ALTER TABLE fidyah ADD INDEX idx_trx_date (trx_date);
ALTER TABLE kotak_amal ADD INDEX idx_trx_date (trx_date);
ALTER TABLE pendis ADD INDEX idx_trx_date (trx_date);
ALTER TABLE setor_zis ADD INDEX idx_trx_date (trx_date);
```

### Caching Strategy
For frequently accessed year-based queries, consider implementing caching:
```php
// Example with Laravel Cache
$cacheKey = "zf_data_{$year}_{$filters}";
$data = Cache::remember($cacheKey, now()->addHours(1), function () use ($year, $filters) {
    return Zf::whereYear('trx_date', $year)->get();
});
```

## Frontend Integration Guide

### JavaScript/React Example
```javascript
// Fetch data for 2025
async function fetchData2025() {
    const response = await fetch('/api/zf?year=2025');
    const data = await response.json();
    return data;
}

// Fetch data for all years
async function fetchDataAllYears() {
    const response = await fetch('/api/zf?year=all');
    const data = await response.json();
    return data;
}

// Combine with other filters
async function fetchDataWithFilters(year, searchTerm) {
    const url = `/api/zf?year=${year}&search=${encodeURIComponent(searchTerm)}`;
    const response = await fetch(url);
    const data = await response.json();
    return data;
}
```

### Vue.js Example
```javascript
methods: {
    async fetchData() {
        const response = await this.$axios.get('/api/zf', {
            params: {
                year: this.selectedYear,
                per_page: 20
            }
        });
        this.transactions = response.data.data;
        this.pagination = response.data.meta;
    }
}
```

### Angular Example
```typescript
this.http.get('/api/zf', {
    params: {
        year: this.selectedYear,
        search: this.searchTerm
    }
}).subscribe(response => {
    this.transactions = response.data;
});
```

## Error Handling

### Invalid Year Format
```bash
GET /api/zf?year=abc
```

**Response:**
```json
{
    "message": "Server error",
    "error": "ArgumentCountError: Too few arguments to function ZfController::index()"
}
```

### Best Practice: Validate Year Input
```javascript
function isValidYear(year) {
    return !isNaN(year) && year.toString().length === 4;
}

// Use only if valid
if (isValidYear(yearParam)) {
    fetchData(`/api/zf?year=${yearParam}`);
} else {
    fetchData('/api/zf?year=all');
}
```

## Migration to Production

### 1. Database Indexing
Run the recommended index queries before deploying to production:
```bash
php artisan tinker
// Execute the ALTER TABLE statements from above
```

### 2. API Documentation Update
Update frontend documentation to include the `year` parameter for all transaction endpoints.

### 3. Testing
Test all endpoints with various year values to ensure:
- Specific year filtering works correctly
- 'all' parameter returns all records
- Year parameter combinations with other filters work
- Pagination works with year filtering
- Statistics endpoints respect year filtering

### 4. Monitoring
Monitor API performance metrics:
- Query execution time with year filtering
- Database load with year-based queries
- Cache hit rates (if caching is implemented)

## Additional Features

### Automatic Year Detection
The system automatically detects the current year when `year=all` is used:
```bash
GET /api/zf?year=all
// Returns data from all years including the current year
```

### Date Range + Year Filter
Both filters can be used together. The date range will filter within the selected year:
```bash
GET /api/zf?year=2025&start_date=2025-03-01&end_date=2025-03-31
```

## Support

For issues or questions about year-based filtering:
1. Check this documentation first
2. Review the API specification in `/docs/API_SPEC.md`
3. Contact the backend development team
