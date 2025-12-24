# Project Structure

## Laravel Standard + Filament

```
app/
├── Console/Commands/     # Artisan commands (rebuild rekap scripts)
├── Filament/
│   ├── Clusters/         # Grouped resources (e.g., Dskl)
│   ├── Exports/          # Filament export classes
│   ├── Imports/          # Filament import classes
│   ├── Pages/            # Custom Filament pages (Dashboard)
│   ├── Resources/        # CRUD resources (main admin UI)
│   │   └── {Name}Resource/
│   │       └── Pages/    # List, Create, Edit pages
│   └── Widgets/          # Dashboard widgets
├── Http/
│   ├── Controllers/Api/  # REST API controllers
│   ├── Requests/         # Form request validation
│   └── Resources/        # API resource transformers
├── Jobs/                 # Queue jobs (UpdateRekap*)
├── Models/               # Eloquent models
│   └── Scopes/           # Global query scopes
├── Observers/            # Model event observers
├── Policies/             # Authorization policies
├── Providers/
│   └── Filament/         # Panel configuration
└── Services/             # Business logic (Rekap*Service)

routes/
├── api.php               # API routes (v1 prefix)
├── web.php               # Web routes
└── console.php           # Console commands

resources/views/
└── filament/             # Filament view overrides
```

## Key Patterns

### Models

-   Use global scopes for role-based data filtering (`ZisScope`, `user_access`)
-   Relationships: `unit()` → `UnitZis`, geographic via `district_id`/`village_id`

### Observers

-   Transaction models (Zf, Zm, Ifs, Distribution) have observers
-   Observers dispatch jobs to update recapitulation tables

### Services

-   `Rekap*Service` classes handle aggregation logic
-   Called by jobs and artisan commands

### Filament Resources

-   Each resource has companion folder with Pages
-   Filters respect user role visibility
-   Use `User::currentIs*()` static helpers for role checks

### API Resources

-   Located in `app/Http/Resources/`
-   Follow `{Model}Resource` naming
-   Controllers in `app/Http/Controllers/Api/`
