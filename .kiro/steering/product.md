# SISFOZIS - Sistem Informasi Zakat, Infak, dan Sedekah

## Overview

SISFOZIS is a Zakat (Islamic alms) management system for UPZ (Unit Pengumpul Zakat / Zakat Collection Units). It handles collection, distribution, and reporting of Islamic charitable funds.

## Core Domain

-   **ZIS**: Zakat, Infak, Sedekah (Islamic charitable giving types)
-   **Zakat Fitrah (ZF)**: Annual obligatory charity during Ramadan (rice or money)
-   **Zakat Maal (ZM)**: Wealth-based zakat
-   **Infak/Sedekah (IFS)**: Voluntary charitable donations
-   **Distribution (Pendis)**: Fund distribution to beneficiaries (mustahik)
-   **Setor**: Deposit/transfer of collected funds

## Key Entities

-   **UnitZis**: Zakat collection units (UPZ) at village/district level
-   **District/Village**: Geographic hierarchy (Kecamatan/Desa)
-   **Muzakki**: Zakat payers
-   **Mustahik**: Zakat recipients (8 asnaf categories)
-   **Rekap**: Recapitulation/summary reports (daily, monthly, yearly)

## User Roles

-   `super_admin`: Full system access
-   `admin`: Administrative access
-   `tim_sisfo`: IT/System team
-   `monitoring`: Read-only monitoring
-   `upz_kecamatan`: District-level UPZ operator
-   `upz_desa`: Village-level UPZ operator

## Business Rules

-   Data access is scoped by user role and geographic assignment
-   Transactions trigger automatic recapitulation updates via observers
-   Reports aggregate at daily, monthly, and yearly periods
