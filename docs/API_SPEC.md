# SISFOZIS API Specification

Dokumentasi lengkap REST API untuk sistem SISFOZIS (Sistem Informasi Zakat, Infak, dan Sedekah).

## Base URL

```
/api/v1
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
POST /api/v1/register
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
POST /api/v1/login
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
POST /api/v1/logout
```

**Response (200):**

```json
{ "message": "Logged out successfully" }
```

### 1.4 Get Current User

🔒 **Requires Authentication**

```
GET /api/v1/user
```

**Response (200):**

```json
{ "user": { "id": 1, "name": "John Doe", "email": "john@example.com" } }
```

---

## 2. Master Data (Public)

### 2.1 Kecamatan (Districts)

```
GET /api/v1/kecamatan
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
GET /api/v1/desa
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
GET /api/v1/zf-payment-types
GET /api/v1/zf-payment-types/{id}
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
GET    /api/v1/unit-zis          # List
POST   /api/v1/unit-zis          # Create
GET    /api/v1/unit-zis/{id}     # Show
PUT    /api/v1/unit-zis/{id}     # Update
DELETE /api/v1/unit-zis/{id}     # Delete
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

**Example Request - Filter by Year:**
```
GET /api/v1/unit-zis?year=2025
```

**Example Request - Filter by Village:**
```
GET /api/v1/unit-zis?village_id=1
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
GET    /api/v1/zf          # List
POST   /api/v1/zf          # Create
GET    /api/v1/zf/{id}     # Show
PUT    /api/v1/zf/{id}     # Update
DELETE /api/v1/zf/{id}     # Delete
```

**Example Request - Get all ZF for year 2025:**
```
GET /api/v1/zf?year=2025&per_page=15&page=1
```

**Example Request - Filter by month:**
```
GET /api/v1/zf?month=1&year=2025
```

**Example Request - Search by name:**
```
GET /api/v1/zf?search=Ahmad&per_page=10
```

**Example Request - Get by date range:**
```
GET /api/v1/zf?start_date=2025-01-01&end_date=2025-01-31
```

**Example Request - Create new ZF transaction:**
```json
POST /api/v1/zf
Authorization: Bearer {token}
Content-Type: application/json

{
    "unit_id": 1,
    "trx_date": "2025-01-15",
    "muzakki_name": "Ahmad Rahman",
    "zf_rice": 2.5,
    "zf_amount": 37500,
    "total_muzakki": 1,
    "desc": "Zakat fitrah tahun 2025"
}
```

---

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

**Query Parameters:**

| Parameter   | Type   | Description                    |
| ----------- | ------ | ---------------------------- |
| search      | string | Pencarian teks               |
| year        | int    | Filter berdasarkan tahun       |
| month       | int    | Filter berdasarkan bulan (1-12)|
| start_date  | date   | Filter tanggal mulai           |
| end_date    | date   | Filter tanggal akhir           |
| unit_id     | int    | Filter by unit                 |
| sort_by     | string | Sort by field (default: trx_date) |
| sort_direction | string | Sort direction: `asc` atau `desc` |
| per_page    | int    | Items per page (default: 15)   |
| page        | int    | Page number (default: 1)       |

### 4.2 Zakat Maal (ZM)

```
GET    /api/v1/zm          # List
POST   /api/v1/zm          # Create
GET    /api/v1/zm/{id}     # Show
PUT    /api/v1/zm/{id}     # Update
DELETE /api/v1/zm/{id}     # Delete
```

**Example Request - Get all ZM transactions for 2025:**
```
GET /api/v1/zm?year=2025&per_page=15
```

**Example Request - Filter by category:**
```
GET /api/v1/zm?category_maal=Pendidikan
```

**Example Request - Search by name:**
```
GET /api/v1/zm?search=Budi&per_page=10
```

**Example Request - Create new ZM transaction:**
```json
POST /api/v1/zm
Authorization: Bearer {token}
Content-Type: application/json

{
    "unit_id": 1,
    "trx_date": "2025-01-15",
    "category_maal": "Pendidikan",
    "muzakki_name": "Budi Santoso",
    "no_telp": "08123456789",
    "amount": 500000,
    "desc": "Donasi untuk SMP Al-Ikhlas"
}
```

---

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

**Query Parameters:**

| Parameter   | Type   | Description                    |
| ----------- | ------ | ---------------------------- |
| search      | string | Pencarian teks               |
| year        | int    | Filter berdasarkan tahun       |
| month       | int    | Filter berdasarkan bulan (1-12)|
| start_date  | date   | Filter tanggal mulai           |
| end_date    | date   | Filter tanggal akhir           |
| unit_id     | int    | Filter by unit                 |
| category_maal | string | Filter by category            |
| sort_by     | string | Sort by field (default: trx_date) |
| sort_direction | string | Sort direction: `asc` atau `desc` |
| per_page    | int    | Items per page (default: 15)   |
| page        | int    | Page number (default: 1)       |

### 4.3 Infak/Sedekah (IFS)

```
GET    /api/v1/ifs          # List
POST   /api/v1/ifs          # Create
GET    /api/v1/ifs/{id}     # Show
PUT    /api/v1/ifs/{id}     # Update
DELETE /api/v1/ifs/{id}     # Delete
```

**Example Request - Get all IFS transactions for 2025:**
```
GET /api/v1/ifs?year=2025&per_page=15
```

**Example Request - Filter by amount range:**
```
GET /api/v1/ifs?min_amount=100000&max_amount=1000000
```

**Example Request - Search by name:**
```
GET /api/v1/ifs?search=Citra&per_page=10
```

**Example Request - Create new IFS transaction:**
```json
POST /api/v1/ifs
Authorization: Bearer {token}
Content-Type: application/json

{
    "unit_id": 1,
    "trx_date": "2025-01-15",
    "munfiq_name": "Citra Dewi",
    "amount": 100000,
    "total_munfiq": 1,
    "desc": "Infak pembangunan masjid"
}
```

**Example Request - Group donation (total_munfiq > 1):**
```json
POST /api/v1/ifs
Authorization: Bearer {token}
Content-Type: application/json

{
    "unit_id": 1,
    "trx_date": "2025-01-15",
    "munfiq_name": "Yayasan X",
    "amount": 5000000,
    "total_munfiq": 5,
    "desc": "Kumpulan donasi kelompok"
}
```

---

**Request Body:**

| Field          | Type    | Required | Description                    |
| -------------- | ------- | -------- | ------------------------------ |
| unit_id        | integer | Yes      | ID unit ZIS                   |
| trx_date       | date    | Yes      | Tanggal transaksi              |
| munfiq_name   | string  | Yes      | Nama munfiq                  |
| amount         | integer | Yes      | Jumlah (Rp)                  |
| total_munfiq  | integer | Yes      | Total jumlah munfiq (min: 1)    |
| desc           | string  | No       | Keterangan                     |

**Query Parameters:**

| Parameter   | Type   | Description                    |
| ----------- | ------ | ---------------------------- |
| search      | string | Pencarian teks               |
| year        | int    | Filter berdasarkan tahun       |
| month       | int    | Filter berdasarkan bulan (1-12)|
| start_date  | date   | Filter tanggal mulai           |
| end_date    | date   | Filter tanggal akhir           |
| unit_id     | int    | Filter by unit                 |
| min_amount  | int    | Filter minimum amount         |
| max_amount  | int    | Filter maximum amount         |
| sort_by     | string | Sort by field (default: trx_date) |
| sort_direction | string | Sort direction: `asc` atau `desc` |
| per_page    | int    | Items per page (default: 15)   |
| page        | int    | Page number (default: 1)       |

### 4.4 Fidyah

```
GET    /api/v1/fidyah          # List
POST   /api/v1/fidyah          # Create
GET    /api/v1/fidyah/{id}     # Show
PUT    /api/v1/fidyah/{id}     # Update
DELETE /api/v1/fidyah/{id}     # Delete
```

**Example Request - Get all Fidyah transactions for 2025:**
```
GET /api/v1/fidyah?year=2025&per_page=15
```

**Example Request - Search by name:**
```
GET /api/v1/fidyah?search=Ahmad&per_page=10
```

**Example Request - Create new Fidyah transaction:**
```json
POST /api/v1/fidyah
Authorization: Bearer {token}
Content-Type: application/json

{
    "unit_id": 1,
    "trx_date": "2025-01-15",
    "name": "Ahmad Fariq",
    "total_day": 7,
    "amount": 350000,
    "desc": "Fidyah puasa Ramadhan"
}
```

---

**Request Body:**

| Field     | Type    | Required | Description       |
| --------- | ------- | -------- | ----------------- |
| unit_id   | integer | Yes      | ID unit ZIS       |
| trx_date  | date    | Yes      | Tanggal transaksi |
| name      | string  | Yes      | Nama pembayar     |
| total_day | integer | Yes      | Jumlah hari       |
| amount    | integer | Yes      | Jumlah (Rp)       |
| desc      | string  | No       | Keterangan        |

**Query Parameters:**

| Parameter   | Type   | Description                    |
| ----------- | ------ | ---------------------------- |
| search      | string | Pencarian teks               |
| year        | int    | Filter berdasarkan tahun       |
| month       | int    | Filter berdasarkan bulan (1-12)|
| start_date  | date   | Filter tanggal mulai           |
| end_date    | date   | Filter tanggal akhir           |
| unit_id     | int    | Filter by unit                 |
| sort_by     | string | Sort by field (default: trx_date) |
| sort_direction | string | Sort direction: `asc` atau `desc` |
| per_page    | int    | Items per page (default: 15)   |
| page        | int    | Page number (default: 1)       |

### 4.5 Kotak Amal (Donation Box)

```
GET    /api/v1/kotak_amal          # List
POST   /api/v1/kotak_amal          # Create
GET    /api/v1/kotak_amal/{id}     # Show
PUT    /api/v1/kotak_amal/{id}     # Update
DELETE /api/v1/kotak_amal/{id}     # Delete
```

**Request Body:**

| Field    | Type    | Required | Description       |
| -------- | ------- | -------- | ----------------- |
| unit_id  | integer | Yes      | ID unit ZIS       |
| trx_date | date    | Yes      | Tanggal transaksi |
| amount   | integer | Yes      | Jumlah (Rp)       |
| desc     | string  | No       | Keterangan        |

**Query Parameters:**

| Parameter  | Type   | Description                    |
| ---------- | ------ | ---------------------------- |
| search     | string | Pencarian teks               |
| start_date | date   | Filter tanggal mulai           |
| end_date   | date   | Filter tanggal akhir           |
| year       | int    | Filter berdasarkan tahun       |
| per_page   | int    | Items per page (default: 15)   |
| page       | int    | Page number (default: 1)       |

**Example Request - Get all Kotak Amal for 2025:**
```
GET /api/v1/kotak_amal?year=2025&per_page=15
```

**Example Request - Search by description:**
```
GET /api/v1/kotak_amal?search=Pembangunan&per_page=10
```

**Example Request - Create new Kotak Amal transaction:**
```json
POST /api/v1/kotak_amal
Authorization: Bearer {token}
Content-Type: application/json

{
    "unit_id": 1,
    "trx_date": "2025-01-15",
    "amount": 500000,
    "desc": "Donasi pembangunan kubah masjid"
}
```

---

---

## 5. Distribusi (Pendis)

🔒 **Requires Authentication**

```
GET    /api/v1/pendis          # List
POST   /api/v1/pendis          # Create
GET    /api/v1/pendis/{id}     # Show
PUT    /api/v1/pendis/{id}     # Update
DELETE /api/v1/pendis/{id}     # Delete
```

**Query Parameters:**

| Parameter    | Type   | Description                    |
| ------------ | ------ | ---------------------------- |
| year         | int    | Filter berdasarkan tahun       |
| month        | int    | Filter berdasarkan bulan (1-12)|
| start_date   | date   | Filter tanggal mulai           |
| end_date     | date   | Filter tanggal akhir           |
| unit_id      | int    | Filter by unit                 |
| fund_type    | string | Filter by fund type (ZF/ZM/IFS) |
| asnaf        | string | Filter by asnaf category       |
| program      | string | Filter by program              |
| sort_by      | string | Sort by field (default: trx_date) |
| sort_direction | string | Sort direction: `asc` atau `desc` |
| per_page     | int    | Items per page (default: 15)   |
| page         | int    | Page number (default: 1)       |

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
| total_amount     | integer | Yes      | Jumlah uang (Rp)        |
| beneficiary      | integer | Yes      | Jumlah penerima manfaat |
| desc             | string  | No       | Keterangan              |

**Example Request - Get Pendis for year 2025:**
```
GET /api/v1/pendis?year=2025&fund_type=ZF&per_page=15
```

**Example Request - Filter by asnaf:**
```
GET /api/v1/pendis?asnaf=Fakir&fund_type=IFS
```

**Example Request - Create new Pendis transaction:**
```json
POST /api/v1/pendis
Authorization: Bearer {token}
Content-Type: application/json

{
    "unit_id": 1,
    "trx_date": "2025-01-15",
    "mustahik_name": "H. Abdullah",
    "nik": "3201123456780001",
    "fund_type": "ZF",
    "asnaf": "Fakir",
    "program": "Kesejahteraan",
    "total_rice": 5,
    "total_amount": 75000,
    "beneficiary": 2,
    "desc": "Bantuan zakat fitrah"
}
```

---

## 6. Setor ZIS

🔒 **Requires Authentication**

```
GET    /api/v1/setor          # List
POST   /api/v1/setor          # Create
GET    /api/v1/setor/{id}     # Show
PUT    /api/v1/setor/{id}     # Update
DELETE /api/v1/setor/{id}     # Delete
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
GET /api/v1/allocation-configs
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
GET /api/v1/allocation-configs/{id}
```

### 7.3 Create Allocation Config

🔒 **Admin Only**

```
POST /api/v1/allocation-configs
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
PUT /api/v1/allocation-configs/{id}
```

### 7.5 Delete Allocation Config

🔒 **Admin Only**

```
DELETE /api/v1/allocation-configs/{id}
```

**Response (204):** No Content

### 7.6 Get Active Config

Mendapatkan konfigurasi aktif untuk jenis ZIS dan tahun tertentu.

```
GET /api/v1/allocation-configs-active
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

### 4.6 Comprehensive Statistics

```
GET /api/v1/statistics/overview     # Overview statistics for all ZIS types
GET /api/v1/statistics/trends        # Trends analysis over time
GET /api/v1/statistics/units         # Unit-wise comparison
GET /api/v1/statistics/distribution # Distribution by category
GET /api/v1/statistics/peak-dates   # Peak donation days analysis
```

#### 4.6.1 Overview Statistics

```
GET /api/v1/statistics/overview
```

**Query Parameters:**

| Parameter | Type   | Description                      |
| --------- | ------ | -------------------------------- |
| year      | int    | Filter by year (default: current) |
| zis_type  | string | Filter by ZIS type (zf/zm/ifs)   |

**Response (200):**

```json
{
    "zf": {
        "total_amount": 50000000,
        "total_rice": 1000.5,
        "total_muzakki": 500,
        "transactions": 245,
        "avg_per_muzakki": 102040.8,
        "avg_per_transaction": 204081.6
    },
    "zm": {
        "total_amount": 20000000,
        "total_muzakki": 100,
        "transactions": 72,
        "avg_per_muzakki": 200000,
        "avg_per_transaction": 277777.8
    },
    "ifs": {
        "total_amount": 10000000,
        "total_munfiq": 200,
        "transactions": 14761,
        "avg_per_munfiq": 50000,
        "avg_per_transaction": 678.0
    },
    "summary": {
        "grand_total": 80000000,
        "total_transactions": 15078,
        "total_participants": 800
    }
}
```

#### 4.6.2 Trends Analysis

```
GET /api/v1/statistics/trends
```

**Query Parameters:**

| Parameter | Type   | Description                          |
| --------- | ------ | ------------------------------------ |
| start_date| date  | Start date for analysis               |
| end_date  | date  | End date for analysis                 |
| zis_type  | string | Filter by ZIS type                   |
| period    | string | Period type: `daily`, `monthly`, `yearly` (default: monthly) |
| group_by  | string | Group by: `day`, `month`, `year`     |

**Response (200):**

```json
[
    {
        "period": "2025-01",
        "zf_amount": 5000000,
        "zf_rice": 100.5,
        "zf_muzakki": 50,
        "zm_amount": 2000000,
        "zm_muzakki": 10,
        "ifs_amount": 1000000,
        "ifs_munfiq": 20,
        "total_amount": 8000000,
        "total_participants": 80
    },
    {
        "period": "2025-02",
        "zf_amount": 6000000,
        "zf_rice": 120.5,
        "zf_muzakki": 60,
        "zm_amount": 2500000,
        "zm_muzakki": 12,
        "ifs_amount": 1500000,
        "ifs_munfiq": 25,
        "total_amount": 10000000,
        "total_participants": 97
    }
]
```

#### 4.6.3 Unit-wise Comparison

```
GET /api/v1/statistics/units
```

**Query Parameters:**

| Parameter | Type   | Description                      |
| --------- | ------ | -------------------------------- |
| year      | int    | Filter by year                    |
| zis_type  | string | Filter by ZIS type               |

**Response (200):**

```json
[
    {
        "unit_id": 1,
        "unit_name": "UPZ Masjid Al-Ikhlas",
        "zf_amount": 5000000,
        "zf_muzakki": 50,
        "zm_amount": 2000000,
        "zm_muzakki": 10,
        "ifs_amount": 1000000,
        "ifs_munfiq": 20,
        "total_amount": 8000000,
        "total_participants": 80
    },
    {
        "unit_id": 2,
        "unit_name": "UPZ Pusat",
        "zf_amount": 3000000,
        "zf_muzakki": 30,
        "zm_amount": 1500000,
        "zm_muzakki": 8,
        "ifs_amount": 500000,
        "ifs_munfiq": 10,
        "total_amount": 5000000,
        "total_participants": 48
    }
]
```

#### 4.6.4 Distribution by Category

```
GET /api/v1/statistics/distribution
```

**Query Parameters:**

| Parameter | Type   | Description                      |
| --------- | ------ | -------------------------------- |
| zis_type  | string | Filter by ZIS type               |
| period    | string | Period type: `daily`, `monthly`, `yearly` |
| start_date| date  | Start date                       |
| end_date  | date  | End date                         |

**Response (200):**

```json
{
    "zf": {
        "by_kategori": [
            {"kategori": "Rukun Ibadah", "jumlah": 50, "persentase": 20},
            {"kategori": "Rukun Islam", "jumlah": 100, "persentase": 40},
            {"kategori": "Rukun Iman", "jumlah": 100, "persentase": 40}
        ],
        "by_masjid": [
            {"masjid": "Masjid Al-Ikhlas", "jumlah": 5000000, "persentase": 50},
            {"masjid": "Masjid Baiturrahman", "jumlah": 5000000, "persentase": 50}
        ]
    },
    "zm": {
        "by_kategori": [
            {"kategori": "Pendidikan", "jumlah": 5000000, "persentase": 25},
            {"kategori": "Kesehatan", "jumlah": 5000000, "persentase": 25},
            {"kategori": "Dakwah", "jumlah": 10000000, "persentase": 50}
        ]
    }
}
```

#### 4.6.5 Peak Donation Days

```
GET /api/v1/statistics/peak-dates
```

**Query Parameters:**

| Parameter | Type   | Description                      |
| --------- | ------ | -------------------------------- |
| zis_type  | string | Filter by ZIS type               |
| start_date| date  | Start date                       |
| end_date  | date  | End date                         |

**Response (200):**

```json
{
    "top_days": [
        {"date": "2025-01-01", "jumlah": 15000000, "persentase": 25.5},
        {"date": "2025-02-14", "jumlah": 10000000, "persentase": 17.0},
        {"date": "2025-01-01", "jumlah": 8000000, "persentase": 13.6}
    ],
    "top_days_of_week": [
        {"day": "Ahad", "jumlah": 35000000, "persentase": 59.3},
        {"day": "Jumat", "jumlah": 15000000, "persentase": 25.4},
        {"day": "Sabtu", "jumlah": 8000000, "persentase": 13.6}
    ]
}
```

### 4.7 IFS Statistics

```
GET /api/v1/ifs/statistics
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
GET /api/v1/rekap/zis              # List dengan pagination
GET /api/v1/rekap/zis/{id}         # Detail
GET /api/v1/rekap/zis-summary      # Summary totals
GET /api/v1/rekap/zis-monthly      # Monthly statistics
```

**Query Parameters (List):**

| Parameter  | Type    | Description                                      |
| ---------- | ------- | ------------------------------------------------ |
| unit_id    | integer | Filter by unit                                   |
| period     | string  | Filter by period: `harian`, `bulanan`, `tahunan` |
| year       | integer | Filter by year (e.g., 2025)                      |
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
GET /api/v1/zm/statistics
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
GET /api/v1/rekap/alokasi              # List
GET /api/v1/rekap/alokasi/{id}         # Detail
GET /api/v1/rekap/alokasi-summary      # Summary
GET /api/v1/rekap/alokasi-monthly      # Monthly stats
```

**Query Parameters (List):**

| Parameter  | Type    | Description                                      |
| ---------- | ------- | ------------------------------------------------ |
| unit_id    | integer | Filter by unit                                   |
| periode    | string  | Filter by periode: `harian`, `bulanan`, `tahunan`|
| year       | integer | Filter by year (e.g., 2025)                      |
| from_date  | date    | Tanggal mulai                                    |
| to_date    | date    | Tanggal akhir                                    |
| sort_by    | string  | Field untuk sorting (default: `periode_date`)    |
| sort_order | string  | `asc` atau `desc` (default: `desc`)              |
| per_page   | integer | Items per page (default: 15)                     |

### 8.3 Rekap Pendis (Distribusi)

```
GET /api/v1/rekap/pendis               # List
GET /api/v1/rekap/pendis/{id}          # Detail
GET /api/v1/rekap/pendis-summary       # Summary
GET /api/v1/rekap/pendis-monthly       # Monthly stats
GET /api/v1/rekap/pendis-distribution  # Distribution by asnaf/program
```

**Query Parameters (List):**

| Parameter  | Type    | Description                                      |
| ---------- | ------- | ------------------------------------------------ |
| unit_id    | integer | Filter by unit                                   |
| periode    | string  | Filter by periode: `harian`, `bulanan`, `tahunan`|
| year       | integer | Filter by year (e.g., 2025)                      |
| from_date  | date    | Tanggal mulai                                    |
| to_date    | date    | Tanggal akhir                                    |
| sort_by    | string  | Field untuk sorting (default: `periode_date`)    |
| sort_order | string  | `asc` atau `desc` (default: `desc`)              |
| per_page   | integer | Items per page (default: 15)                     |

### 8.4 Rekap Hak Amil

```
GET /api/v1/rekap/hak-amil               # List
GET /api/v1/rekap/hak-amil/{id}          # Detail
GET /api/v1/rekap/hak-amil-summary       # Summary
GET /api/v1/rekap/hak-amil-monthly       # Monthly stats
GET /api/v1/rekap/hak-amil-distribution  # Distribution stats
```

**Query Parameters (List):**

| Parameter  | Type    | Description                                      |
| ---------- | ------- | ------------------------------------------------ |
| unit_id    | integer | Filter by unit                                   |
| periode    | string  | Filter by periode: `harian`, `bulanan`, `tahunan`|
| year       | integer | Filter by year (e.g., 2025)                      |
| from_date  | date    | Tanggal mulai                                    |
| to_date    | date    | Tanggal akhir                                    |
| sort_by    | string  | Field untuk sorting (default: `periode_date`)    |
| sort_order | string  | `asc` atau `desc` (default: `desc`)              |
| per_page   | integer | Items per page (default: 15)                     |

### 8.5 Rekap Setor

```
GET /api/v1/rekap/setor          # List
GET /api/v1/rekap/setor/{id}     # Detail
```

**Query Parameters:**

| Parameter      | Type    | Description               |
| -------------- | ------- | ------------------------- |
| unit_id        | integer | Filter by unit            |
| periode        | string  | Filter by periode         |
| year           | integer | Filter by year (e.g., 2025)|
| from_date      | date    | Tanggal mulai             |
| to_date        | date    | Tanggal akhir             |
| with_unit      | string  | `true` untuk include unit |
| sort_by        | string  | Field untuk sorting       |
| sort_direction | string  | `asc` atau `desc`         |
| per_page       | integer | Items per page            |

### 8.6 Rekap ZIS Report (Consolidated)

Endpoint untuk mendapatkan laporan ZIS terkonsolidasi dalam satu response, cocok untuk generate PDF.

```
GET /api/v1/rekap/zis-report
```

**Query Parameters:**

| Parameter   | Type   | Required | Description                              |
| ----------- | ------ | -------- | ---------------------------------------- |
| unit_id     | int    | Yes      | ID unit ZIS                              |
| periode     | string | No       | Periode: `harian`, `bulanan`, `tahunan`  |
| year        | int    | No       | Filter by year (e.g., 2025)              |
| from_date   | date   | No       | Tanggal mulai (Y-m-d)                    |
| to_date     | date   | No       | Tanggal akhir (Y-m-d), harus >= from_date |

**Example Request - Get consolidated report for unit:**
```
GET /api/v1/rekap/zis-report?unit_id=1
```

**Example Request - Filter by year:**
```
GET /api/v1/rekap/zis-report?unit_id=1&year=2025
```

**Example Request - Filter by date range:**
```
GET /api/v1/rekap/zis-report?unit_id=1&from_date=2025-01-01&to_date=2025-12-31
```

**Example Request - Filter by period:**
```
GET /api/v1/rekap/zis-report?unit_id=1&periode=bulanan&from_date=2025-01-01&to_date=2025-03-31
```

**Response (200):**

```json
{
    "data": {
        "total_zf_amount": 50000000,
        "total_zf_rice": 1000.5,
        "total_zf_muzakki": 500,
        "total_zm_amount": 20000000,
        "total_zm_muzakki": 100,
        "total_ifs_amount": 10000000,
        "total_ifs_munfiq": 200,
        "total_pendis_zf_amount": 15000000,
        "total_pendis_zf_rice": 300.5,
        "total_pendis_zm": 5000000,
        "total_pendis_ifs": 3000000,
        "total_pendis_amount": 23000000,
        "total_pendis_rice": 300.5,
        "total_pm": 150,
        "total_hak_amil": 2875000,
        "total_setor_zf_amount": 12000000,
        "total_setor_zf_rice": 250.0,
        "total_setor_zm": 3500000,
        "total_setor_ifs": 2500000,
        "bukti_setor": "/storage/setor/bukti_001.jpg",
        "ketua": "Ahmad Hidayat",
        "sekretaris": "Budi Santoso",
        "bendahara": "Citra Dewi"
    }
}
```

**Response Fields:**

| Field                    | Type    | Description                           |
| ------------------------ | ------- | ------------------------------------- |
| total_zf_amount          | int     | Total zakat fitrah uang (Rp)          |
| total_zf_rice            | float   | Total zakat fitrah beras (kg)         |
| total_zf_muzakki         | int     | Total muzakki zakat fitrah            |
| total_zm_amount          | int     | Total zakat maal uang (Rp)            |
| total_zm_muzakki         | int     | Total muzakki zakat maal              |
| total_ifs_amount         | int     | Total infak/sedekah (Rp)              |
| total_ifs_munfiq         | int     | Total munfiq infak/sedekah            |
| total_pendis_zf_amount   | int     | Total distribusi ZF uang (Rp)         |
| total_pendis_zf_rice     | float   | Total distribusi ZF beras (kg)        |
| total_pendis_zm          | int     | Total distribusi ZM (Rp)              |
| total_pendis_ifs         | int     | Total distribusi IFS (Rp)             |
| total_pendis_amount      | int     | Total distribusi uang (Rp)            |
| total_pendis_rice        | float   | Total distribusi beras (kg)           |
| total_pm                 | int     | Total penerima manfaat                |
| total_hak_amil           | int     | Total hak amil yang diserap (Rp)      |
| total_setor_zf_amount    | int     | Total setoran ZF uang (Rp)            |
| total_setor_zf_rice      | float   | Total setoran ZF beras (kg)           |
| total_setor_zm           | int     | Total setoran ZM (Rp)                 |
| total_setor_ifs          | int     | Total setoran IFS (Rp)                |
| bukti_setor              | string  | URL bukti setor terakhir (nullable)   |
| ketua                    | string  | Nama ketua unit                       |
| sekretaris               | string  | Nama sekretaris/wakil unit            |
| bendahara                | string  | Nama bendahara unit                   |

### 8.7 Alokasi Report (Updated Allocations)

Endpoint untuk mendapatkan data alokasi terupdate per jenis ZIS. Menghitung selisih antara alokasi yang direncanakan (`rekap_alokasi`) dan realisasi (`rekap_setor`, `rekap_pendis`, `rekap_hak_amil`) untuk periode **tahunan**.

```
GET /api/v1/rekap/alokasi-report
```

**Query Parameters:**

| Parameter | Type    | Required | Description                             |
| --------- | ------- | -------- | --------------------------------------- |
| unit_id   | int     | Yes      | ID unit ZIS                             |
| year      | int     | No       | Tahun fiskal (default: tahun sekarang)  |

**Example Request - Get allocation report:**
```
GET /api/v1/rekap/alokasi-report?unit_id=1
```

**Example Request - Filter by year:**
```
GET /api/v1/rekap/alokasi-report?unit_id=1&year=2026
```

**Response (200):**

```json
{
    "data": {
        "alokasi_kelola_zf_uang": 7000000,
        "alokasi_kelola_zf_beras": 175.35,
        "alokasi_kelola_zm": 5600000,
        "alokasi_kelola_ifs": 2400000,
        "alokasi_setor_zf_uang": 2000000,
        "alokasi_setor_zf_beras": 30.5,
        "alokasi_setor_zm": 1000000,
        "alokasi_setor_ifs": 500000,
        "alokasi_pendis_zf_uang": 1500000,
        "alokasi_pendis_zf_beras": 50.0,
        "alokasi_pendis_zm": 800000,
        "alokasi_pendis_ifs": 400000,
        "alokasi_ha_zf_uang": 375000,
        "alokasi_ha_zf_beras": 5.0,
        "alokasi_ha_zm": 200000,
        "alokasi_ha_ifs": 100000,
        "alokasi_op_uang": 500000,
        "alokasi_op_beras": 10.0
    }
}
```

**Response Fields:**

| Field                    | Type  | Description                                                      |
| ------------------------ | ----- | ---------------------------------------------------------------- |
| alokasi_kelola_zf_uang   | int   | Alokasi kelola ZF uang (langsung dari rekap_alokasi)             |
| alokasi_kelola_zf_beras  | float | Alokasi kelola ZF beras (langsung dari rekap_alokasi)            |
| alokasi_kelola_zm        | int   | Alokasi kelola ZM (langsung dari rekap_alokasi)                  |
| alokasi_kelola_ifs       | int   | Alokasi kelola IFS (langsung dari rekap_alokasi)                 |
| alokasi_setor_zf_uang    | int   | Sisa alokasi setor ZF uang = rekap_alokasi − rekap_setor        |
| alokasi_setor_zf_beras   | float | Sisa alokasi setor ZF beras = rekap_alokasi − rekap_setor       |
| alokasi_setor_zm         | int   | Sisa alokasi setor ZM = rekap_alokasi − rekap_setor             |
| alokasi_setor_ifs        | int   | Sisa alokasi setor IFS = rekap_alokasi − rekap_setor            |
| alokasi_pendis_zf_uang   | int   | Sisa alokasi pendis ZF uang = rekap_alokasi − rekap_pendis      |
| alokasi_pendis_zf_beras  | float | Sisa alokasi pendis ZF beras = rekap_alokasi − rekap_pendis     |
| alokasi_pendis_zm        | int   | Sisa alokasi pendis ZM = rekap_alokasi − rekap_pendis           |
| alokasi_pendis_ifs       | int   | Sisa alokasi pendis IFS = rekap_alokasi − rekap_pendis          |
| alokasi_ha_zf_uang       | int   | Sisa alokasi hak amil ZF uang = rekap_alokasi − rekap_hak_amil  |
| alokasi_ha_zf_beras      | float | Sisa alokasi hak amil ZF beras = rekap_alokasi − rekap_hak_amil |
| alokasi_ha_zm            | int   | Sisa alokasi hak amil ZM = rekap_alokasi − rekap_hak_amil       |
| alokasi_ha_ifs           | int   | Sisa alokasi hak amil IFS = rekap_alokasi − rekap_hak_amil      |
| alokasi_op_uang          | int   | Alokasi operasional uang (langsung dari rekap_alokasi)           |
| alokasi_op_beras         | float | Alokasi operasional beras (langsung dari rekap_alokasi)          |

---

## 9. Admin Only Endpoints

🔒 **Requires Authentication + Admin Role**

### 9.1 Manage ZF Payment Types

```
POST   /api/v1/zf-payment-types              # Create
PUT    /api/v1/zf-payment-types/{id}         # Update
DELETE /api/v1/zf-payment-types/{id}         # Delete
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
