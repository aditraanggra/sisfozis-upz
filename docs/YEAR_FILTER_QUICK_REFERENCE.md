# Year-Based Filtering Quick Reference

## What's New

All transaction endpoints now support filtering by year! Use the `year` query parameter to get data from specific years or all years.

## Quick Start

### Basic Usage
```bash
# Get data from 2025
GET /api/zf?year=2025

# Get data from all years
GET /api/zf?year=all

# Get all years (no year parameter)
GET /api/zf
```

## Supported Endpoints

| Endpoint | Year Support | Notes |
|----------|-------------|-------|
| `/api/zf` | ✅ Yes | Zakat Fitrah |
| `/api/zm` | ✅ Yes | Zakat Maal (also in stats) |
| `/api/ifs` | ✅ Yes | Infak/Sedekah (also in stats) |
| `/api/fidyah` | ✅ Yes | Fidyah |
| `/api/kotak_amal` | ✅ Yes | Kotak Amal |
| `/api/pendis` | ✅ Yes | Pendis |
| `/api/setor` | ✅ Yes | Setor ZIS |

## Filter Combinations

### Year + Search
```bash
GET /api/zf?year=2025&search=muzakki
GET /api/ifs?year=all&search=pembayar
```

### Year + Date Range
```bash
GET /api/zf?year=2025&start_date=2025-01-01&end_date=2025-12-31
GET /api/zm?year=2024&start_date=2024-06-01&end_date=2024-06-30
```

### Year + Pagination
```bash
GET /api/zf?year=2025&per_page=20&page=2
GET /api/ifs?year=2024&per_page=50
```

### Year + Sorting (ZM)
```bash
GET /api/zm?year=2025&sort_by=no_telp&sort_direction=asc
GET /api/zm?year=2024&sort_by=no_telp&sort_direction=desc
```

### Year + Munfiq Range (IFS)
```bash
GET /api/ifs?year=2025&min_munfiq=1&max_munfiq=5
GET /api/ifs?year=2024&total_munfiq=3
```

## Statistics Endpoints

```bash
# Get 2024 ZM statistics
GET /api/zm/statistics?year=2024

# Get 2025 IFS statistics
GET /api/ifs/statistics?year=2025
```

## Response Format

```json
{
    "data": [
        {
            "id": 1,
            "unit_id": 1,
            "trx_date": "2025-01-15",
            "muzakki_name": "Ahmad",
            "amount": 50000
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

## JavaScript Example

```javascript
// Fetch 2025 data
async function get2025Data() {
    const response = await fetch('/api/zf?year=2025');
    const data = await response.json();
    return data;
}

// Fetch all years
async function getAllData() {
    const response = await fetch('/api/zf?year=all');
    const data = await response.json();
    return data;
}

// Fetch with search
async function getFilteredData() {
    const response = await fetch('/api/zf?year=2025&search=muzakki');
    const data = await response.json();
    return data;
}
```

## Vue.js Example

```javascript
methods: {
    async fetchData(year) {
        const url = year ? `/api/zf?year=${year}` : '/api/zf';
        const response = await this.$axios.get(url);
        this.transactions = response.data.data;
    }
}
```

## Performance Tips

1. **Use specific years** for better performance
2. **Create database indexes** on `trx_date` columns
3. **Combine filters wisely** to reduce query size
4. **Use pagination** for large datasets

## Database Indexes

Create these indexes for optimal performance:

```sql
ALTER TABLE zf ADD INDEX idx_trx_date (trx_date);
ALTER TABLE zm ADD INDEX idx_trx_date (trx_date);
ALTER TABLE ifs ADD INDEX idx_trx_date (trx_date);
ALTER TABLE fidyah ADD INDEX idx_trx_date (trx_date);
ALTER TABLE kotak_amal ADD INDEX idx_trx_date (trx_date);
ALTER TABLE pendis ADD INDEX idx_trx_date (trx_date);
ALTER TABLE setor_zis ADD INDEX idx_trx_date (trx_date);
```

## Common Patterns

### Get Last 3 Years Data
```bash
GET /api/zf?year=2025&year=2024&year=2023
```

### Get Current Year Only
```bash
GET /api/zf?year=2025
```

### Get Previous Year Data
```bash
GET /api/zf?year=2024
```

### Get Multiple Years Data
```bash
GET /api/zf?year=2024&year=2025&year=2026
```

## Troubleshooting

### Problem: Empty Results
- Check if year format is correct (4 digits)
- Verify the year exists in the database
- Check user permissions for the unit

### Problem: Slow Queries
- Create database indexes on `trx_date`
- Use pagination to reduce dataset size
- Consider caching frequently accessed years

### Problem: Wrong Year Data
- Verify the API endpoint is correct
- Check that year parameter is being passed
- Ensure the date format is correct (YYYY-MM-DD)

## Error Handling

```javascript
async function fetchDataWithErrorHandling() {
    try {
        const response = await fetch('/api/zf?year=2025');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
        return null;
    }
}
```

## Support

- Detailed documentation: `/docs/YEAR_FILTER_API.md`
- API specification: `/docs/API_SPEC.md`
- Implementation details: `/docs/YEAR_FILTER_IMPLEMENTATION.md`

## Version History

- **2025-02-14**: Initial implementation
  - Added year filtering to all transaction endpoints
  - Added year filtering to statistics endpoints
  - Updated all access control methods
  - Created comprehensive documentation

## Next Steps

1. Create database indexes
2. Test with frontend application
3. Update API documentation for frontend team
4. Monitor performance in production
5. Implement caching for frequently accessed years

---

**Ready to use!** Start filtering by year today! 🚀
