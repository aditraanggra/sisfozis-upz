# SISFOZIS API Specification

Dokumentasi lengkap REST API untuk sistem SISFOZIS (Sistem Informasi Zakat, Infak, dan Sedekah).

## Base URL

```
/api
```

## Authentication

API menggunakan **Laravel Sanctum** untuk autentikasi berbasis token.

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## 1. Authentication

### 1.1 Register

```
POST /api/register
```

**Request Body:**

| Field                 | Type   | Required | Description               |
| --------------------- | ------ | -------- | ------------------------- |
| name                  | string | Yes      | Nama lengkap (max 255)    |
| email                 | string | Yes      | Email unik                |
| password              | string | Yes      | Password (min 8 karakter) |
| password_confirmation | string | Yes      | Konfirmasi password       |

**Response (201):**

```json
{
    "message": "Registration successful",
    "user": { "id": 1, "name": "John Doe", "email": "john@example.com" },
    "token": "1|abc123..."
}
```

### 1.2 Login

```
POST /api/login
```

**Request Body:**

| Field    | Type   | Required | Description     |
| -------- | ------ | -------- | --------------- |
| email    | string | Yes      | Email terdaftar |
| password | string | Yes      | Password        |

**Response (200):**

```json
{
    "message": "Login successful",
    "user": { "id": 1, "name": "John Doe", "email": "john@example.com" },
    "token": "1|abc123..."
}
```

### 1.3 Logout

🔒 **Requires Authentication**

```
POST /api/logout
```

**Response (200):**

```json
{ "message": "Logged out successfully" }
```

### 1.4 Get Current User

🔒 **Requires Authentication**

```
GET /api/user
```

**Response (200):**

```json
{ "user": { "id": 1, "name": "John Doe", "email": "john@example.com" } }
```

---

## 2. Master Data (Public)

### 2.1 Kecamatan (Districts)

```
GET /api/kecamatan
```

**Response (200):**

```json
{
    "success": true,
    "message": "List Data Kecamatan",
    "data": [{ "id": 1, "name": "Kecamatan A" }]
}
```

### 2.2 Desa (Villages)

```
GET /api/desa
```

**Query Parameters:**

| Parameter   | Type    | Description                  |
| ----------- | ------- | ---------------------------- |
| district_id | integer | Filter berdasarkan kecamatan |

**Response (200):**

```json
{
    "success": true,
    "message": "List Data Desa",
    "data": [{ "id": 1, "name": "Desa A", "district_id": 1 }]
}
```

### 2.3 Jenis Pembayaran Zakat Fitrah

```
GET /api/zf-payment-types
GET /api/zf-payment-types/{id}
```

**Query Parameters:**

| Parameter | Type    | Description                                         |
| --------- | ------- | --------------------------------------------------- |
| all       | boolean | `true` untuk menampilkan semua (termasuk non-aktif) |
| type      | string  | Filter by type: `beras` atau `uang`                 |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Beras Premium",
            "type": "beras",
            "rice_amount": 2.5,
            "money_amount": null,
            "sk_reference": "SK/001/2025",
            "is_active": true,
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z"
        }
    ]
}
```

---

## 3. Unit ZIS (UPZ)

🔒 **Requires Authentication**

### 3.1 CRUD Unit ZIS

```
GET    /api/unit-zis          # List
POST   /api/unit-zis          # Create
GET    /api/unit-zis/{id}     # Show
PUT    /api/unit-zis/{id}     # Update
DELETE /api/unit-zis/{id}     # Delete
```

**Request Body (Create/Update):**

| Field          | Type    | Required | Description               |
| -------------- | ------- | -------- | ------------------------- |
| user_id        | integer | Yes      | ID user pemilik           |
| category_id    | integer | Yes      | ID kategori unit          |
| village_id     | integer | Yes      | ID desa                   |
| district_id    | integer | Yes      | ID kecamatan              |
| no_sk          | string  | Yes      | Nomor SK                  |
| unit_name      | string  | Yes      | Nama unit                 |
| no_register    | string  | Yes      | Nomor registrasi (unique) |
| address        | string  | Yes      | Alamat                    |
| unit_leader    | string  | Yes      | Nama ketua                |
| unit_assistant | string  | Yes      | Nama wakil                |
| unit_finance   | string  | Yes      | Nama bendahara            |
| operator_phone | string  | Yes      | No. HP operator           |
| rice_price     | integer | Yes      | Harga beras per kg        |
| is_verified    | boolean | No       | Status verifikasi         |

**Response (200):**

```json
{
    "success": true,
    "message": "List Data UPZ",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "category_id": 1,
            "village_id": 1,
            "district_id": 1,
            "no_sk": "SK/001/2025",
            "unit_name": "UPZ Masjid Al-Ikhlas",
            "no_register": "REG001",
            "address": "Jl. Masjid No. 1",
            "unit_leader": "Ahmad",
            "unit_assistant": "Budi",
            "unit_finance": "Citra",
            "operator_phone": "08123456789",
            "rice_price": 15000,
            "is_verified": true,
            "village_name": "Desa A",
            "district_name": "Kecamatan A"
        }
    ]
}
```

---

## 4. Transaksi Pengumpulan ZIS

🔒 **Requires Authentication**

**Common Query Parameters:**

| Parameter  | Type   | Description                    |
| ---------- | ------ | ---------------------------- |
| search     | string | Pencarian teks               |
| start_date | date   | Filter tanggal mulai           |
| end_date   | date   | Filter tanggal akhir           |
| no_telp    | string | Filter exact phone number      |
| sort_by     | string  | Sort by field (e.g., no_telp) |
| sort_direction | string  | Sort direction: `asc` atau `desc` |
| per_page    | integer | Items per page (default: 15) |
| page        | integer | Page number (default: 1)      |
| total_munfiq | integer | Filter exact total munfiq     |
| min_munfiq | integer | Filter minimum total munfiq    |
| max_munfiq | integer | Filter maksimum total munfiq   |

### 4.1 Zakat Fitrah (ZF)

```
GET    /api/zf          # List
POST   /api/zf          # Create
GET    /api/zf/{id}     # Show
PUT    /api/zf/{id}     # Update
DELETE /api/zf/{id}     # Delete
```

**Request Body:**

| Field         | Type    | Required | Description       |
| ------------- | ------- | -------- | ----------------- |
| unit_id       | integer | Yes      | ID unit ZIS       |
| trx_date      | date    | Yes      | Tanggal transaksi |
| muzakki_name  | string  | Yes      | Nama muzakki      |
| zf_rice       | numeric | Yes      | Jumlah beras (kg) |
| zf_amount     | integer | Yes      | Jumlah uang (Rp)  |
| total_muzakki | integer | Yes      | Jumlah jiwa       |
| desc          | string  | No       | Keterangan        |

### 4.2 Zakat Maal (ZM)

```
GET    /api/zm          # List
POST   /api/zm          # Create
GET    /api/zm/{id}     # Show
PUT    /api/zm/{id}     # Update
DELETE /api/zm/{id}     # Delete
```

**Request Body:**

| Field         | Type    | Required | Description                    |
| ------------- | ------- | -------- | ------------------------------ |
| unit_id       | integer | Yes      | ID unit ZIS                   |
| trx_date      | date    | Yes      | Tanggal transaksi              |
| category_maal | string  | Yes      | Kategori zakat maal            |
| muzakki_name  | string  | Yes      | Nama muzakki                 |
| no_telp       | string  | No       | No. telepon muzakki          |
| amount        | integer | Yes      | Jumlah (Rp)                  |
| desc          | string  | No       | Keterangan                     |

### 4.3 Infak/Sedekah (IFS)

```
GET    /api/ifs          # List
POST   /api/ifs          # Create
GET    /api/ifs/{id}     # Show
PUT    /api/ifs/{id}     # Update
DELETE /api/ifs/{id}     # Delete
```

**Request Body:**

| Field          | Type    | Required | Description                    |
| -------------- | ------- | -------- | ------------------------------ |
| unit_id        | integer | Yes      | ID unit ZIS                   |
| trx_date       | date    | Yes      | Tanggal transaksi              |
| munfiq_name   | string  | Yes      | Nama munfiq                  |
| amount         | integer | Yes      | Jumlah (Rp)                  |
| total_munfiq  | integer | Yes      | Total jumlah munfiq (min: 1)    |
| desc           | string  | No       | Keterangan                     |

### 4.4 Fidyah

```
GET    /api/fidyah          # List
POST   /api/fidyah          # Create
GET    /api/fidyah/{id}     # Show
PUT    /api/fidyah/{id}     # Update
DELETE /api/fidyah/{id}     # Delete
```

**Request Body:**

| Field     | Type    | Required | Description       |
| --------- | ------- | -------- | ----------------- |
| unit_id   | integer | Yes      | ID unit ZIS       |
| trx_date  | date    | Yes      | Tanggal transaksi |
| name      | string  | Yes      | Nama pembayar     |
| total_day | integer | Yes      | Jumlah hari       |
| amount    | integer | Yes      | Jumlah (Rp)       |
| desc      | string  | No       | Keterangan        |

### 4.5 Kotak Amal (Donation Box)

```
GET    /api/kotak_amal          # List
POST   /api/kotak_amal          # Create
GET    /api/kotak_amal/{id}     # Show
PUT    /api/kotak_amal/{id}     # Update
DELETE /api/kotak_amal/{id}     # Delete
```

**Request Body:**

| Field    | Type    | Required | Description       |
| -------- | ------- | -------- | ----------------- |
| unit_id  | integer | Yes      | ID unit ZIS       |
| trx_date | date    | Yes      | Tanggal transaksi |
| amount   | integer | Yes      | Jumlah (Rp)       |
| desc     | string  | No       | Keterangan        |

---

## 5. Distribusi (Pendis)

🔒 **Requires Authentication**

```
GET    /api/pendis          # List
POST   /api/pendis          # Create
GET    /api/pendis/{id}     # Show
PUT    /api/pendis/{id}     # Update
DELETE /api/pendis/{id}     # Delete
```

**Request Body:**

| Field            | Type    | Required | Description             |
| ---------------- | ------- | -------- | ----------------------- |
| unit_id          | integer | Yes      | ID unit ZIS             |
| trx_date         | date    | Yes      | Tanggal transaksi       |
| mustahik_name    | string  | Yes      | Nama mustahik           |
| nik              | string  | Yes      | NIK (16 digit)          |
| fund_type        | string  | Yes      | Jenis dana (ZF/ZM/IFS)  |
| asnaf            | string  | Yes      | Kategori asnaf          |
| program          | string  | Yes      | Nama program            |
| total_rice       | numeric | Yes      | Jumlah beras (kg)       |
| total_amount     | integer | Yes      |
| Jumlah uang (Rp) |
| beneficiary      | integer | Yes      | Jumlah penerima manfaat |
| desc             | string  | No       | Keterangan              |

---

## 6. Setor ZIS

🔒 **Requires Authentication**

```
GET    /api/setor          # List
POST   /api/setor          # Create
GET    /api/setor/{id}     # Show
PUT    /api/setor/{id}     # Update
DELETE /api/setor/{id}     # Delete
```

**Request Body:**

| Field              | Type    | Required | Description        |
| ------------------ | ------- | -------- | ------------------ |
| unit_id            | integer | Yes      | ID unit ZIS        |
| trx_date           | date    | Yes      | Tanggal transaksi  |
| zf_amount_deposit  | integer | Yes      | Setoran ZF uang    |
| zf_rice_deposit    | numeric | Yes      | Setoran ZF beras   |
| zm_amount_deposit  | integer | Yes      | Setoran ZM         |
| ifs_amount_deposit | integer | Yes      | Setoran IFS        |
| total_deposit      | integer | Yes      | Total setoran      |
| status             | string  | Yes      | Status setoran     |
| validation         | string  | Yes      | Status validasi    |
| upload             | string  | Yes      | URL bukti transfer |
| desc               | string  | No       | Keterangan         |

---

## 7. Allocation Config (Konfigurasi Alokasi ZIS)

🔒 **Requires Authentication**

Konfigurasi persentase alokasi dana ZIS (setor, kelola, amil).

### 7.1 List Allocation Configs

```
GET /api/allocation-configs
```

**Query Parameters:**

| Parameter      | Type    | Description                       |
| -------------- | ------- | --------------------------------- |
| zis_type       | string  | Filter by type: `zf`, `zm`, `ifs` |
| effective_year | integer | Filter by tahun efektif           |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "zis_type": "zf",
            "zis_type_label": "Zakat Fitrah",
            "effective_year": 2025,
            "setor_percentage": 30.0,
            "kelola_percentage": 70.0,
            "amil_percentage": 12.5,
            "description": "Konfigurasi ZF 2025",
            "created_at": "2025-01-01T00:00:00.000000Z",
            "updated_at": "2025-01-01T00:00:00.000000Z"
        }
    ]
}
```

### 7.2 Show Allocation Config

```
GET /api/allocation-configs/{id}
```

### 7.3 Create Allocation Config

🔒 **Admin Only**

```
POST /api/allocation-configs
```

**Request Body:**

| Field             | Type    | Required | Description                       |
| ----------------- | ------- | -------- | --------------------------------- |
| zis_type          | string  | Yes      | Jenis ZIS: `zf`, `zm`, atau `ifs` |
| effective_year    | integer | Yes      | Tahun efektif (2020-2100)         |
| setor_percentage  | numeric | Yes      | Persentase setor (0-100)          |
| kelola_percentage | numeric | Yes      | Persentase kelola (0-100)         |
| amil_percentage   | numeric | Yes      | Persentase amil (0-100)           |
| description       | string  | No       | Keterangan (max 500)              |

**Validation Rules:**

- `setor_percentage + kelola_percentage` harus = 100%
- Kombinasi `zis_type` + `effective_year` harus unik

**Response (201):**

```json
{
    "data": {
        "id": 1,
        "zis_type": "zf",
        "zis_type_label": "Zakat Fitrah",
        "effective_year": 2025,
        "setor_percentage": 30.0,
        "kelola_percentage": 70.0,
        "amil_percentage": 12.5,
        "description": "Konfigurasi ZF 2025"
    }
}
```

### 7.4 Update Allocation Config

🔒 **Admin Only**

```
PUT /api/allocation-configs/{id}
```

### 7.5 Delete Allocation Config

🔒 **Admin Only**

```
DELETE /api/allocation-configs/{id}
```

**Response (204):** No Content

### 7.6 Get Active Config

Mendapatkan konfigurasi aktif untuk jenis ZIS dan tahun tertentu.

```
GET /api/allocation-configs-active
```

**Query Parameters:**

| Parameter | Type    | Required | Description                     |
| --------- | ------- | -------- | ------------------------------- |
| zis_type  | string  | Yes      | Jenis ZIS: `zf`, `zm`, `ifs`    |
| year      | integer | No       | Tahun (default: tahun sekarang) |

**Response (200):**

```json
{
    "data": {
        "id": 1,
        "zis_type": "zf",
        "zis_type_label": "Zakat Fitrah",
        "effective_year": 2025,
        "setor_percentage": 30.0,
        "kelola_percentage": 70.0,
        "amil_percentage": 12.5
    }
}
```

**Response jika tidak ada konfigurasi (menggunakan default):**

```json
{
    "message": "No configuration found, using defaults",
    "data": {
        "zis_type": "zf",
        "effective_year": 2025,
        "setor_percentage": 30.0,
        "kelola_percentage": 70.0,
        "amil_percentage": 12.5,
        "is_default": true
    }
}
```

**Default Values:**

- `setor_percentage`: 30%
- `kelola_percentage`: 70%
- `amil_percentage`: 12.5% (ZF/ZM), 20% (IFS)

### 4.4 IFS Statistics

```
GET /api/ifs/statistics
```

**Query Parameters:**

| Parameter  | Type    | Description                    |
| ---------- | ------- | ---------------------------- |
| unit_id   | integer | Filter by specific unit ID     |
| start_date | date    | Filter tanggal mulai           |
| end_date   | date    | Filter tanggal akhir           |

**Response (200):**

```json
{
    "total_transactions": 14761,
    "total_amount": 7694450000,
    "total_munfiq": 14771,
    "average_amount": 521234,
    "average_munfiq": 1,
    "highest_munfiq": 5,
    "individual_donors": 14759,
    "group_donors": 2
}
```

---

## 8. Rekapitulasi

🔒 **Requires Authentication**

### 8.1 Rekap ZIS

```
GET /api/rekap/zis              # List dengan pagination
GET /api/rekap/zis/{id}         # Detail
GET /api/rekap/zis-summary      # Summary totals
GET /api/rekap/zis-monthly      # Monthly statistics
```

**Query Parameters (List):**

| Parameter  | Type    | Description                                      |
| ---------- | ------- | ------------------------------------------------ |
| unit_id    | integer | Filter by unit                                   |
| period     | string  | Filter by period: `harian`, `bulanan`, `tahunan` |
| from_date  | date    | Tanggal mulai                                    |
| to_date    | date    | Tanggal akhir                                    |
| sort_by    | string  | Field untuk sorting (default: `period_date`)     |
| sort_order | string  | `asc` atau `desc` (default: `desc`)              |
| per_page   | integer | Items per page (default: 15)                     |

**Response (List):**

```json
{
    "data": [
        {
            "id": 1,
            "unit_id": 1,
            "unit": { "id": 1, "unit_name": "UPZ Masjid Al-Ikhlas" },
            "period": "bulanan",
            "period_date": "2025-03-01",
            "total_zf_rice": 100.5,
            "total_zf_amount": 5000000,
            "total_zf_muzakki": 50,
            "total_zm_amount": 2000000,
            "total_zm_muzakki": 10,
            "total_ifs_amount": 1000000,
            "total_ifs_munfiq": 20
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

**Response (Summary):**

```json
{
    "total_zf_amount": 50000000,
    "total_zf_rice": 1000.5,
    "total_zf_muzakki": 500,
    "total_zm_amount": 20000000,
    "total_zm_muzakki": 100,
    "total_ifs_amount": 10000000,
    "total_ifs_munfiq": 200
}
```

**Response (Monthly):**

Query params: `year`, `unit_id`

```json
[
    {
        "month": "2025-01",
        "zf_amount": 5000000,
        "zf_rice": 100.5,
        "zm_amount": 2000000,
        "ifs_amount": 1000000
    }
]
```

### 4.5 ZM Statistics

```
GET /api/zm/statistics
```

**Query Parameters:**

| Parameter  | Type    | Description                    |
| ---------- | ------- | ---------------------------- |
| unit_id   | integer | Filter by specific unit ID     |
| start_date | date    | Filter tanggal mulai           |
| end_date   | date    | Filter tanggal akhir           |

**Response (200):**

```json
{
    "total_transactions": 9724,
    "total_amount": 4862000000,
    "average_amount": 500000,
    "total_with_phone": 2456,
    "total_without_phone": 7268,
    "average_amount": 500000,
    "highest_amount": 5000000,
    "phone_coverage": 25.24%
}
```

---

### 8.2 Rekap Alokasi

```
GET /api/rekap/alokasi              # List
GET /api/rekap/alokasi/{id}         # Detail
GET /api/rekap/alokasi-summary      # Summary
GET /api/rekap/alokasi-monthly      # Monthly stats
```

### 8.3 Rekap Pendis (Distribusi)

```
GET /api/rekap/pendis               # List
GET /api/rekap/pendis/{id}          # Detail
GET /api/rekap/pendis-summary       # Summary
GET /api/rekap/pendis-monthly       # Monthly stats
GET /api/rekap/pendis-distribution  # Distribution by asnaf/program
```

### 8.4 Rekap Hak Amil

```
GET /api/rekap/hak-amil               # List
GET /api/rekap/hak-amil/{id}          # Detail
GET /api/rekap/hak-amil-summary       # Summary
GET /api/rekap/hak-amil-monthly       # Monthly stats
GET /api/rekap/hak-amil-distribution  # Distribution stats
```

### 8.5 Rekap Setor

```
GET /api/rekap/setor          # List
GET /api/rekap/setor/{id}     # Detail
```

**Query Parameters:**

| Parameter      | Type    | Description               |
| -------------- | ------- | ------------------------- |
| unit_id        | integer | Filter by unit            |
| periode        | string  | Filter by periode         |
| from_date      | date    | Tanggal mulai             |
| to_date        | date    | Tanggal akhir             |
| with_unit      | string  | `true` untuk include unit |
| sort_by        | string  | Field untuk sorting       |
| sort_direction | string  | `asc` atau `desc`         |
| per_page       | integer | Items per page            |

---

## 9. Admin Only Endpoints

🔒 **Requires Authentication + Admin Role**

### 9.1 Manage ZF Payment Types

```
POST   /api/zf-payment-types              # Create
PUT    /api/zf-payment-types/{id}         # Update
DELETE /api/zf-payment-types/{id}         # Delete
```

**Request Body:**

| Field        | Type    | Required    | Description           |
| ------------ | ------- | ----------- | --------------------- |
| name         | string  | Yes         | Nama jenis pembayaran |
| type         | string  | Yes         | `beras` atau `uang`   |
| rice_amount  | numeric | Conditional | Wajib jika type=beras |
| money_amount | integer | Conditional | Wajib jika type=uang  |
| sk_reference | string  | No          | Referensi SK          |
| is_active    | boolean | No          | Status aktif          |

---

## Error Responses

### 400 Bad Request

```json
{
    "message": "Error creating transaction",
    "error": "Error message details"
}
```

### 401 Unauthorized

```json
{ "message": "Unauthenticated." }
```

### 403 Forbidden

```json
{ "message": "Unauthorized" }
```

### 404 Not Found

```json
{
    "message": "Error retrieving transaction",
    "error": "No query results for model"
}
```

### 422 Validation Error

```json
{
    "message": "The given data was invalid.",
    "errors": { "field_name": ["Error message"] }
}
```

---

## Data Types Reference

### Period Types

| Value   | Description |
| ------- | ----------- |
| harian  | Daily       |
| bulanan | Monthly     |
| tahunan | Yearly      |

### ZIS Types

| Value | Description   |
| ----- | ------------- |
| zf    | Zakat Fitrah  |
| zm    | Zakat Maal    |
| ifs   | Infak/Sedekah |

### Fund Types (Distribusi)

| Value | Description   |
| ----- | ------------- |
| ZF    | Zakat Fitrah  |
| ZM    | Zakat Maal    |
| IFS   | Infak/Sedekah |

### Asnaf Categories

| Value        | Description                     |
| ------------ | ------------------------------- |
| Fakir        | Orang yang tidak memiliki harta |
| Miskin       | Orang yang kekurangan           |
| Amil         | Pengelola zakat                 |
| Muallaf      | Orang yang baru masuk Islam     |
| Riqab        | Budak/hamba sahaya              |
| Gharimin     | Orang yang berhutang            |
| Fisabilillah | Pejuang di jalan Allah          |
| Ibnu Sabil   | Musafir yang kehabisan bekal    |

### Program Categories

| Value       | Description         |
| ----------- | ------------------- |
| Kemanusiaan | Program kemanusiaan |
| Dakwah      | Program dakwah      |
| Pendidikan  | Program pendidikan  |
| Kesehatan   | Program kesehatan   |
| Ekonomi     | Program ekonomi     |

---

## Notes

1. Semua endpoint transaksi memiliki akses kontrol berdasarkan kepemilikan unit.
2. Admin dapat mengakses semua data, user biasa hanya data unit miliknya.
3. Rekap data di-generate otomatis via observer saat transaksi dibuat/diupdate/dihapus.
4. Allocation config menentukan persentase pembagian dana ZIS (setor/kelola/amil).
5. `total_munfiq` field pada IFS merepresentasikan jumlah orang dalam grup donasi.
   - Minimum value: 1 (individual donor)
   - Existing records akan otomatis diisi dengan nilai 1
   - Digunakan untuk analisis pola donasi individu vs kelompok
