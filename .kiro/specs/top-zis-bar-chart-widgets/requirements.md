# Requirements Document

## Introduction

Fitur ini menambahkan tiga widget bar chart pada Dashboard Pengumpulan untuk menampilkan visualisasi 10 besar kecamatan, desa, dan UPZ DKM dengan penerimaan ZIS (Zakat, Infak, Sedekah) terbanyak. Widget ini akan membantu pengguna dengan cepat mengidentifikasi area dan unit dengan performa pengumpulan ZIS tertinggi melalui representasi visual yang intuitif.

## Glossary

-   **ZIS**: Zakat, Infak, Sedekah - tiga jenis donasi dalam Islam
-   **Dashboard_Pengumpulan**: Halaman dashboard utama untuk monitoring pengumpulan ZIS
-   **Bar_Chart_Widget**: Komponen visual berbentuk grafik batang horizontal untuk menampilkan data perbandingan
-   **Kecamatan**: Unit administratif tingkat distrik
-   **Desa**: Unit administratif tingkat kelurahan/desa
-   **UPZ_DKM**: Unit Pengumpul Zakat kategori Dewan Kemakmuran Masjid
-   **UnitZis**: Model yang merepresentasikan unit pengumpul zakat
-   **Total_ZIS**: Jumlah agregat dari Zakat Fitrah (uang), Zakat Maal, dan Infak/Sedekah
-   **Page_Filter**: Filter yang tersedia di halaman dashboard (tahun, tanggal mulai, tanggal akhir)
-   **ZisScope**: Global scope Laravel yang memfilter data berdasarkan role dan assignment geografis pengguna

## Requirements

### Requirement 1

**User Story:** As a super_admin or tim_sisfo user, I want to see a bar chart showing the top 10 districts (kecamatan) with highest ZIS collection, so that I can quickly identify high-performing districts.

#### Acceptance Criteria

1. WHEN the Dashboard_Pengumpulan page loads THEN the Bar_Chart_Widget SHALL display a horizontal bar chart with the top 10 Kecamatan ranked by Total_ZIS in descending order
2. WHEN Page_Filter values change THEN the Bar_Chart_Widget SHALL recalculate and display updated data reflecting the filtered period
3. WHEN a Kecamatan has zero Total_ZIS THEN the Bar_Chart_Widget SHALL exclude that Kecamatan from the top 10 ranking
4. WHEN fewer than 10 Kecamatan have ZIS data THEN the Bar_Chart_Widget SHALL display only the available Kecamatan with non-zero Total_ZIS
5. WHEN the user is not super_admin or tim_sisfo THEN the Bar_Chart_Widget SHALL not be visible

### Requirement 2

**User Story:** As a dashboard user, I want to see a bar chart showing the top 10 villages (desa) with highest ZIS collection, so that I can quickly identify high-performing villages within my access scope.

#### Acceptance Criteria

1. WHEN the Dashboard_Pengumpulan page loads THEN the Bar_Chart_Widget SHALL display a horizontal bar chart with the top 10 Desa ranked by Total_ZIS in descending order
2. WHEN Page_Filter values change THEN the Bar_Chart_Widget SHALL recalculate and display updated data reflecting the filtered period
3. WHEN a Desa has zero Total_ZIS THEN the Bar_Chart_Widget SHALL exclude that Desa from the top 10 ranking
4. WHEN fewer than 10 Desa have ZIS data THEN the Bar_Chart_Widget SHALL display only the available Desa with non-zero Total_ZIS
5. WHEN querying ZIS data THEN the Bar_Chart_Widget SHALL apply ZisScope to filter data based on user role and geographic assignment

### Requirement 3

**User Story:** As a dashboard user, I want to see a bar chart showing the top 10 UPZ DKM with highest ZIS collection, so that I can quickly identify high-performing mosque-based collection units within my access scope.

#### Acceptance Criteria

1. WHEN the Dashboard_Pengumpulan page loads THEN the Bar_Chart_Widget SHALL display a horizontal bar chart with the top 10 UPZ_DKM ranked by Total_ZIS in descending order
2. WHEN Page_Filter values change THEN the Bar_Chart_Widget SHALL recalculate and display updated data reflecting the filtered period
3. WHEN a UPZ_DKM has zero Total_ZIS THEN the Bar_Chart_Widget SHALL exclude that UPZ_DKM from the top 10 ranking
4. WHEN fewer than 10 UPZ_DKM have ZIS data THEN the Bar_Chart_Widget SHALL display only the available UPZ_DKM with non-zero Total_ZIS
5. WHEN querying ZIS data THEN the Bar_Chart_Widget SHALL apply ZisScope to filter data based on user role and geographic assignment
6. WHEN filtering UnitZis THEN the Bar_Chart_Widget SHALL only include units with category_id corresponding to DKM category

### Requirement 4

**User Story:** As a dashboard user, I want the bar charts to display monetary values in a readable format, so that I can easily understand the ZIS amounts.

#### Acceptance Criteria

1. WHEN displaying Total_ZIS values THEN the Bar_Chart_Widget SHALL format amounts in Indonesian Rupiah (IDR) with thousand separators
2. WHEN hovering over a bar THEN the Bar_Chart_Widget SHALL display a tooltip showing the exact Total_ZIS amount for that location or unit
3. WHEN rendering the chart THEN the Bar_Chart_Widget SHALL use distinct colors for visual clarity

### Requirement 5

**User Story:** As a developer, I want the widget data calculation to be consistent with existing ZIS table widgets, so that the displayed values match other dashboard components.

#### Acceptance Criteria

1. WHEN calculating Total_ZIS THEN the Bar_Chart_Widget SHALL aggregate Zakat Fitrah (zf_amount), Zakat Maal (amount from Zm), and Infak/Sedekah (amount from Ifs)
2. WHEN applying date filters THEN the Bar_Chart_Widget SHALL use the same filter logic as ZisPerKecamatanTable and ZisPerDesaTable widgets
