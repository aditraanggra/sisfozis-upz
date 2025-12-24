# Tech Stack

## Backend

-   **PHP 8.2+** with **Laravel 11**
-   **Filament 3.2** - Admin panel framework
-   **Laravel Sanctum** - API authentication
-   **Laravel Octane** - High-performance server (FrankenPHP)
-   **Spatie Permission** via **Filament Shield** - Role-based access control

## Database

-   **PostgreSQL** (primary)
-   Queue/Cache/Session: Database driver

## Frontend

-   **Tailwind CSS 3.4**
-   **Vite 6** - Build tool
-   **Livewire** (via Filament)

## Key Packages

-   `filament/filament` - Admin panel
-   `bezhansalleh/filament-shield` - Permission management
-   `eightynine/filament-excel-import` - Excel imports
-   `barryvdh/laravel-dompdf` - PDF generation
-   `cloudinary-labs/cloudinary-laravel` - Image storage

## Testing

-   **Pest PHP 3** - Testing framework

## Common Commands

```bash
# Development (runs server, queue, logs, vite concurrently)
composer dev

# Or individually:
php artisan serve
php artisan queue:listen --tries=1
npm run dev

# Build assets
npm run build

# Run tests
./vendor/bin/pest

# Code formatting
./vendor/bin/pint

# Clear caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Rebuild recapitulation data
php artisan rekap:rebuild-zis
php artisan rekap:rebuild-pendis
php artisan rekap:rebuild-setor
php artisan rekap:rebuild-alokasi
php artisan rekap:rebuild-hak-amil
```

## API

-   Base path: `/api/v1`
-   Auth: Bearer token (Sanctum)
-   Resources follow REST conventions
