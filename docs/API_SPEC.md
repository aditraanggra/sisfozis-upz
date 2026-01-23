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

Mendaftarkan user baru.

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
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "1|abc123..."
}
```

---

### 1.2 Login

Autentikasi user dan mendapatkan token.

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
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "1|abc123..."
}
```

**Error Response (422):**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["Email atau Password Salah."]
    }
}
```

---

### 1.3 Logout

🔒 **Requires Authentication**

Logout dan revoke token.

```
POST /api/logout
```

**Response (200):**

```json
{
    "message": "Logged out successfully"
}
```

---

### 1.4 Get Current User

🔒 **Requires Authentication**

Mendapatkan data user yang sedang login.

```
GET /api/user
```

**Response (200):**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

---

## 2. Master Data (Public)

### 2.1 Kecamatan (Districts)

Mendapatkan daftar kecamatan.

```
GET /api/kecamatan
```

**Response (200):**

```json
{
    "success": true,
    "message": "List Data Kecamatan",
    "data": [
        {
            "id": 1,
            "name": "Kecamatan A"
        }
    ]
}
```

---

### 2.2 Desa (Villages)

Mendapatkan daftar desa.

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
    "data": [
        {
            "id": 1,
            "name": "Desa A",
            "district_id": 1
        }
    ]
}
```

---

### 2.3 Jenis Pembayaran Zakat Fitrah

Mendapatkan daftar jenis pembayaran ZF.

```
GET /api/zf-payment-types
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

### 3.1 List Unit ZIS

```
GET /api/unit-zis
```

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

### 3.2 Show Unit ZIS

```
GET /api/unit-zis/{id}
```

---

### 3.3 Create Unit ZIS

```
POST /api/unit-zis
```

**Request Body:**

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

---

### 3.4 Update Unit ZIS

```
PUT /api/unit-zis/{id}
```

---

### 3.5 Delete Unit ZIS

```
DELETE /api/unit-zis/{id}
```

---

## 4. Transaksi Pengumpulan ZIS

🔒 **Requires Authentication**

Semua endpoint transaksi memiliki struktur yang sama dengan field dasar:

**Base Transaction Fields:**

| Field    | Type    | Required | Description                    |
| -------- | ------- | -------- | ------------------------------ |
| unit_id  | integer | Yes      | ID unit ZIS                    |
| trx_date | date    | Yes      | Tanggal transaksi (YYYY-MM-DD) |
| desc     | string  | No       | Keterangan                     |

**Common Query Parameters:**

| Parameter  | Type   | Description          |
| ---------- | ------ | -------------------- |
| search     | string | Pencarian teks       |
| start_date | date   | Filter tanggal mulai |
| end_date   | date   | Filter tanggal akhir |

---

### 4.1 Zakat Fitrah (ZF)

#### List ZF

```
GET /api/zf
```

#### Create ZF

```
POST /api/zf
```

**Additional Request Body:**

| Field         | Type    | Required | Description       |
| ------------- | ------- | -------- | ----------------- |
| muzakki_name  | string  | Yes      | Nama muzakki      |
| zf_rice       | numeric | Yes      | Jumlah beras (kg) |
| zf_amount     | integer | Yes      | Jumlah uang (Rp)  |
| total_muzakki | integer | Yes      | Jumlah jiwa       |

**Response (201):**

```json
{
    "data": {
        "id": 1,
        "unit": {
            "id": 1,
            "unit_name": "UPZ Masjid Al-Ikhlas"
        },
        "trx_date": "2025-03-15",
        "muzakki_name": "Ahmad",
        "zf_rice": 2.5,
        "zf_amount": 0,
        "total_muzakki": 1
    }
}
```

#### Show ZF

```
GET /api/zf/{id}
```

#### Update ZF

```
PUT /api/zf/{id}
```

#### Delete ZF

```
DELETE /api/zf/{id}
```

---

### 4.2 Zakat Maal (ZM)

#### List ZM

```
GET /api/zm
```

#### Create ZM

```
POST /api/zm
```

**Additional Request Body:**

| Field         | Type    | Required | Description         |
| ------------- | ------- | -------- | ------------------- |
| category_maal | string  | Yes      | Kategori zakat maal |
| muzakki_name  | string  | Yes      | Nama muzakki        |
| amount        | integer | Yes      | Jumlah (Rp)         |

**Response:**

```json
{
    "data": {
        "id": 1,
        "unit": {
            "id": 1,
            "unit_name": "UPZ Masjid Al-Ikhlas"
        },
        "trx_date": "2025-03-15",
        "category_maal": "Perdagangan",
        "muzakki_name": "Budi",
        "amount": 1000000
    }
}
```

#### Show/Update/Delete ZM

```
GET /api/zm/{id}
PUT /api/zm/{id}
DELETE /api/zm/{id}
```

---

### 4.3 Infak/Sedekah (IFS)

#### List IFS

```
GET /api/ifs
```

#### Create IFS

```
POST /api/ifs
```

**Additional Request Body:**

| Field       | Type    | Required | Description |
| ----------- | ------- | -------- | ----------- |
| munfiq_name | string  | Yes      | Nama munfiq |
| amount      | integer | Yes      | Jumlah (Rp) |

**Response:**

```json
{
    "data": {
        "id": 1,
        "unit": {
            "id": 1,
            "unit_name": "UPZ Masjid Al-Ikhlas"
        },
        "trx_date": "2025-03-15",
        "munfiq_name": "Citra",
        "amount": 500000
    }
}
```

#### Show/Update/Delete IFS

```
GET /api/ifs/{id}
PUT /api/ifs/{id}
DELETE /api/ifs/{id}
```

---

### 4.4 Fidyah

#### List Fidyah

```
GET /api/fidyah
```

#### Create Fidyah

```
POST /api/fidyah
```

**Additional Request Body:**

| Field     | Type    | Required | Description   |
| --------- | ------- | -------- | ------------- |
| name      | string  | Yes      | Nama pembayar |
| total_day | integer | Yes      | Jumlah hari   |
| amount    | integer | Yes      | Jumlah (Rp)   |

**Response:**

```json
{
    "data": {
        "id": 1,
        "unit": {
            "id": 1,
            "unit_name": "UPZ Masjid Al-Ikhlas"
        },
        "trx_date": "2025-03-15",
        "name": "Dedi",
        "total_day": 30,
        "amount": 450000
    }
}
```

#### Show/Update/Delete Fidyah

```
GET /api/fidyah/{id}
PUT /api/fidyah/{id}
DELETE /api/fidyah/{id}
```

---

### 4.5 Kotak Amal (Donation Box)

#### List Kotak Amal

```
GET /api/kotak_amal
```

#### Create Kotak Amal

```
POST /api/kotak_amal
```

**Additional Request Body:**

| Field  | Type    | Required | Description |
| ------ | ------- | -------- | ----------- |
| amount | integer | Yes      | Jumlah (Rp) |

**Response:**

```json
{
    "data": {
        "id": 1,
        "unit": {
            "id": 1,
            "unit_name": "UPZ Masjid Al-Ikhlas"
        },
        "trx_date": "2025-03-15",
        "amount": 250000
    }
}
```

#### Show/Update/Delete Kotak Amal

```
GET /api/kotak_amal/{id}
PUT /api/kotak_amal/{id}
DELETE /api/kotak_amal/{id}
```

---

## 5. Distribusi (Pendis)

🔒 **Requires Authentication**

### 5.1 List Distribusi

```
GET /api/pendis
```

### 5.2 Create Distribusi

```
POST /api/pendis
```

**Request Body:**

| Field         | Type    | Required | Description             |
| ------------- | ------- | -------- | ----------------------- |
| unit_id       | integer | Yes      | ID unit ZIS             |
| trx_date      | date    | Yes      | Tanggal transaksi       |
| desc          | string  | No       | Keterangan              |
| mustahik_name | string  | Yes      | Nama mustahik           |
| nik           | string  | Yes      | NIK (16 digit)          |
| fund_type     | string  | Yes      | Jenis dana (ZF/ZM/IFS)  |
| asnaf         | string  | Yes      | Kategori asnaf          |
| program       | string  | Yes      | Nama program            |
| total_rice    | numeric | Yes      | Jumlah beras (kg)       |
| total_amount  | integer | Yes      | Jumlah uang (Rp)        |
| beneficiary   | integer | Yes      | Jumlah penerima manfaat |

**Response (201):**

```json
{
    "data": {
        "id": 1,
        "unit": {
            "id": 1,
            "unit_name": "UPZ Masjid Al-Ikhlas"
        },
        "trx_date": "2025-03-15",
        "mustahik_name": "Eko",
        "nik": "3201234567890123",
        "fund_type": "ZF",
        "asnaf": "Fakir",
        "program": "Kemanusiaan",
        "total_rice": 5.0,
        "total_amount": 0,
        "beneficiary": 4
    }
}
```

### 5.3 Show/Update/Delete Distribusi

```
GET /api/pendis/{id}
PUT /api/pendis/{id}
DELETE /api/pendis/{id}
```

---

## 6. Setor ZIS

🔒 **Requires Authentication**

### 6.1 List Setor

```
GET /api/setor
```

### 6.2 Create Setor

```
POST /api/setor
```

**Request Body:**

| Field              | Type    | Required | Description        |
| ------------------ | ------- | -------- | ------------------ |
| unit_id            | integer | Yes      | ID unit ZIS        |
| trx_date           | date    | Yes      | Tanggal transaksi  |
| desc               | string  | No       | Keterangan         |
| zf_amount_deposit  | integer | Yes      | Setoran ZF uang    |
| zf_rice_deposit    | numeric | Yes      | Setoran ZF beras   |
| zm_amount_deposit  | integer | Yes      | Setoran ZM         |
| ifs_amount_deposit | integer | Yes      | Setoran IFS        |
| total_deposit      | integer | Yes      | Total setoran      |
| status             | string  | Yes      | Status setoran     |
| validation         | string  | Yes      | Status validasi    |
| upload             | string  | Yes      | URL bukti transfer |

**Response (201):**

```json
{
    "data": {
        "id": 1,
        "unit": {
            "id": 1,
            "unit_name": "UPZ Masjid Al-Ikhlas"
        },
        "trx_date": "2025-03-15",
        "zf_amount_deposit": 1000000,
        "zf_rice_deposit": 0,
        "zm_amount_deposit": 500000,
        "ifs_amount_deposit": 250000,
        "total_deposit": 1750000,
        "status": "pending",
        "validation": "pending",
        "upload": "https://example.com/bukti.jpg"
    }
}
```

### 6.3 Show/Update/Delete Setor

```
GET /api/setor/{id}
PUT /api/setor/{id}
DELETE /api/setor/{id}
```

---

## 7. Rekapitulasi

🔒 **Requires Authentication**

### 7.1 Rekap ZIS

#### List Rekap ZIS

```
GET /api/rekap/zis
```

**Query Parameters:**

| Parameter  | Type    | Description                                      |
| ---------- | ------- | ------------------------------------------------ |
| unit_id    | integer | Filter by unit                                   |
| period     | string  | Filter by period: `harian`, `bulanan`, `tahunan` |
| from_date  | date    | Tanggal mulai                                    |
| to_date    | date    | Tanggal akhir                                    |
| sort_by    | string  | Field untuk sorting (default: `period_date`)     |
| sort_order | string  | `asc` atau `desc` (default: `desc`)              |
| per_page   | integer | Items per page (default: 15)                     |

**Response (200):**

```json
{
    "data": [
        {
            "id": 1,
            "unit_id": 1,
            "unit": {
                "id": 1,
                "unit_name": "UPZ Masjid Al-Ikhlas"
            },
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

#### Show Rekap ZIS

```
GET /api/rekap/zis/{id}
```

#### Summary Rekap ZIS

```
GET /api/rekap/zis-summary
```

**Response:**

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

#### Monthly Stats Rekap ZIS

```
GET /api/rekap/zis-monthly
```

**Query Parameters:**

| Parameter | Type    | Description    |
| --------- | ------- | -------------- |
| year      | integer | Tahun          |
| unit_id   | integer | Filter by unit |

**Response:**

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

---

### 7.2 Rekap Alokasi

#### List Rekap Alokasi

```
GET /api/rekap/alokasi
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "unit_id": 1,
      "unit": {
        "id": 1,
        "unit_name": "UPZ Masjid Al-Ikhlas"
      },
      "periode": "bulanan",
      "periode_date": "2025-03-01",
      "total_setor": {
        "zf_amount": 5000000,
        "zf_rice": 100.5,
        "zm": 2000000,
        "ifs": 1000000
      },
      "total_kelola": {
        "zf_amount": 4000000,
        "zf_rice": 80.0,
        "zm": 1600000,
        "ifs": 800000
      },
      "hak_amil": {
        "zf_amount": 400000,
        "zf_rice": 8.0,
        "zm": 160000,
        "ifs": 80000
      },
      "alokasi_pendis": {
        "zf_amount": 3600000,
        "zf_rice": 72.0,
        "zm": 1440000,
        "ifs": 720000
      },
      "hak_op": {
        "zf_amount": 200000,
        "zf_rice": 4.0
      }
    }
  ],
  "meta": {...}
}
```

#### Show Rekap Alokasi

```
GET /api/rekap/alokasi/{id}
```

#### Summary Rekap Alokasi

```
GET /api/rekap/alokasi-summary
```

#### Monthly Stats Rekap Alokasi

```
GET /api/rekap/alokasi-monthly
```

---

### 7.3 Rekap Pendis (Distribusi)

#### List Rekap Pendis

```
GET /api/rekap/pendis
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "unit_id": 1,
      "unit": {
        "id": 1,
        "unit_name": "UPZ Masjid Al-Ikhlas"
      },
      "periode": "bulanan",
      "periode_date": "2025-03-01",
      "total_pendis": {
        "zf_amount": 3600000,
        "zf_rice": 72.0,
        "zm": 1440000,
        "ifs": 720000
      },
      "asnaf": {
        "fakir": {
          "amount": 1500000,
          "rice": 30.0
        },
        "miskin": {
          "amount": 1200000,
          "rice": 24.0
        },
        "fisabilillah": {
          "amount": 900000,
          "rice": 18.0
        }
      },
      "program": {
        "kemanusiaan": {
          "amount": 2000000,
          "rice": 40.0
        },
        "dakwah": {
          "amount": 1600000,
          "rice": 32.0
        }
      },
      "t_pm": 100
    }
  ],
  "meta": {...}
}
```

#### Show Rekap Pendis

```
GET /api/rekap/pendis/{id}
```

#### Summary Rekap Pendis

```
GET /api/rekap/pendis-summary
```

#### Monthly Stats Rekap Pendis

```
GET /api/rekap/pendis-monthly
```

#### Distribution Stats

```
GET /api/rekap/pendis-distribution
```

**Response:**

```json
{
    "asnaf": {
        "fakir": {
            "amount": 15000000,
            "rice": 300.0
        },
        "miskin": {
            "amount": 12000000,
            "rice": 240.0
        },
        "fisabilillah": {
            "amount": 9000000,
            "rice": 180.0
        }
    },
    "program": {
        "kemanusiaan": {
            "amount": 20000000,
            "rice": 400.0
        },
        "dakwah": {
            "amount": 16000000,
            "rice": 320.0
        }
    }
}
```

---

### 7.4 Rekap Hak Amil

#### List Rekap Hak Amil

```
GET /api/rekap/hak-amil
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "unit_id": 1,
      "unit": {
        "id": 1,
        "unit_name": "UPZ Masjid Al-Ikhlas"
      },
      "periode": "bulanan",
      "periode_date": "2025-03-01",
      "total_pendis_ha": {
        "zf_amount": 400000,
        "zf_rice": 8.0,
        "zm": 160000,
        "ifs": 80000
      },
      "t_pm": 5
    }
  ],
  "meta": {...}
}
```

#### Show Rekap Hak Amil

```
GET /api/rekap/hak-amil/{id}
```

---

### 7.5 Rekap Setor

#### List Rekap Setor

```
GET /api/rekap/setor
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

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "unit_id": 1,
      "unit": {
        "id": 1,
        "unit_name": "UPZ Masjid Al-Ikhlas"
      },
      "periode": "bulanan",
      "periode_date": "2025-03-01",
      "t_setor_zf_amount": 5000000,
      "t_setor_zf_rice": 100.5,
      "t_setor_zm": 2000000,
      "t_setor_ifs": 1000000
    }
  ],
  "meta": {...}
}
```

#### Show Rekap Setor

```
GET /api/rekap/setor/{id}
```

---

## 8. Admin Only Endpoints

🔒 **Requires Authentication + Admin Role**

### 8.1 Manage ZF Payment Types

#### Create Payment Type

```
POST /api/zf-payment-types
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

#### Update Payment Type

```
PUT /api/zf-payment-types/{id}
```

#### Delete Payment Type

```
DELETE /api/zf-payment-types/{id}
```

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
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
    "message": "Unauthorized access"
}
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
    "errors": {
        "field_name": ["Error message"]
    }
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

1. Semua endpoint transaksi (ZF, ZM, IFS, Fidyah, Kotak Amal, Pendis, Setor) memiliki akses kontrol berdasarkan kepemilikan unit.
2. Admin dapat mengakses semua data, sedangkan user biasa hanya dapat mengakses data unit miliknya.
3. Rekap data di-generate otomatis melalui observer ketika transaksi dibuat/diupdate/dihapus.
4. Gunakan filter `period` atau `periode` untuk memfilter data rekap berdasarkan periode waktu.
