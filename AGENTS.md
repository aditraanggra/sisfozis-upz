# SISFOZIS Development Guidelines

This document contains essential information for agentic coding agents working on the SISFOZIS (Sistem Informasi Zakat, Infak, dan Sedekah) Laravel application.

## Development Commands

### PHP/Laravel Commands
```bash
# Install dependencies
composer install

# Run development server (includes queue, pail, vite)
composer dev

# Run application server only
php artisan serve

# Database operations
php artisan migrate
php artisan db:seed
php artisan tinker

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate application key
php artisan key:generate

# Queue operations
php artisan queue:work
php artisan queue:listen --tries=1
```

### Frontend Commands
```bash
# Install Node.js dependencies
npm install

# Development server with hot reload
npm run dev

# Build production assets
npm run build
```

### Testing Commands
```bash
# Run all tests
./vendor/bin/pest
# or
php artisan test

# Run specific test suite
./vendor/bin/pest tests/Feature
./vendor/bin/pest tests/Unit
./vendor/bin/pest tests/Property

# Run single test file
./vendor/bin/pest tests/Feature/Services/AllocationConfigServiceTest.php

# Run tests with coverage
./vendor/bin/pest --coverage

# Filter tests by name
./vendor/bin/pest --filter="test function name"
```

### Code Quality Commands
```bash
# Format PHP code using Laravel Pint
./vendor/bin/pint

# Format specific file/directory
./vendor/bin/pint app/Models/User.php
./vendor/bin/pint app/Http/Controllers/

# Check without fixing (dry run)
./vendor/bin/pint --dry-run
```

## Code Style Guidelines

### PHP Code Style
- **Indentation**: 4 spaces (no tabs)
- **Line Endings**: LF (Unix style)
- **Encoding**: UTF-8
- **File Naming**: PascalCase for classes (User.php, UserService.php)
- **Method Naming**: camelCase (getUserById(), calculateTotal())
- **Variable Naming**: camelCase ($userName, $totalAmount)

### Laravel-Specific Conventions

#### Models
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExampleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'field1',
        'field2',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function relatedModel()
    {
        return $this->belongsTo(RelatedModel::class);
    }
}
```

#### Controllers
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Implementation
    }

    public function store(Request $request)
    {
        // Implementation
    }
}
```

#### Services
```php
<?php

namespace App\Services;

class ExampleService
{
    public function calculateSomething($data)
    {
        // Business logic here
        return $result;
    }
}
```

### Frontend Guidelines

#### Blade Templates
- Use Tailwind CSS classes for styling
- Follow Filament component patterns
- Include proper CSRF tokens in forms
- Use Laravel's Blade directives consistently

#### JavaScript
- Use ES6+ modules
- Follow Vite build patterns
- Keep JS in resources/js directory
- Use Alpine.js for interactive components when needed

#### Tailwind CSS
- Use utility classes over custom CSS
- Follow responsive design patterns (sm:, md:, lg:, xl:)
- Use dark mode variants (dark:bg-gray-800)
- Leverage Filament's design system

## Testing Guidelines

### Pest PHP Structure
```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Model', function () {
    beforeEach(function () {
        // Setup before each test
    });

    it('can create a user', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        expect($user->email)->toBe('test@example.com');
    });

    it('validates email format', function () {
        // Test implementation
    });
});
```

### Test Organization
- **Unit Tests**: tests/Unit - Test individual classes/methods
- **Feature Tests**: tests/Feature - Test application workflows
- **Property Tests**: tests/Property - Test with invariants

### Database Testing
- Use RefreshDatabase trait for clean state
- Use DatabaseTransactions for performance
- Use factories for test data
- Test both positive and negative cases

## Security Guidelines

### Input Validation
- Always validate user input
- Use Laravel's validation rules
- Sanitize file uploads
- Implement rate limiting for APIs

### Authentication & Authorization
- Use Laravel Sanctum for API authentication
- Implement proper RBAC with Filament Shield
- Check authorization in controllers
- Use policies for model access control

### Data Protection
- Hash passwords (Laravel default)
- Encrypt sensitive data
- Use HTTPS in production
- Implement proper error handling without exposing internals

## Performance Guidelines

### Database Optimization
- Use eager loading to prevent N+1 queries
- Add database indexes for frequently queried columns
- Use query caching where appropriate
- Implement pagination for large datasets

### Caching
- Use Laravel's cache system for expensive operations
- Implement cache invalidation strategies
- Consider Redis for distributed caching
- Cache database query results

### Frontend Optimization
- Minimize asset sizes
- Use Vite's build optimizations
- Implement lazy loading for images
- Use CDN for static assets

## Development Workflow

### Before Committing
1. Run tests: `./vendor/bin/pest`
2. Format code: `./vendor/bin/pint`
3. Build assets: `npm run build` (if frontend changes)
4. Check functionality manually

### Branch Strategy
- Use feature branches for new development
- Follow git flow conventions
- Write descriptive commit messages
- Create pull requests for code review

### Environment Setup
- Copy `.env.example` to `.env`
- Generate application key: `php artisan key:generate`
- Configure database settings
- Install dependencies: `composer install && npm install`

## Filament Admin Panel Guidelines

### Custom Panels
- Extend AdminPanelProvider for customizations
- Use Filament's color system for theming
- Implement proper resource relationships
- Add custom actions when needed

### Resources
- Follow Filament resource conventions
- Implement proper form validation
- Use table filters and actions
- Implement soft deletes where appropriate

## Special Considerations

### Decimal Calculations
- Use `bcdiv()`, `bcmul()`, `bcadd()`, `bcsub()` for monetary calculations
- Always specify precision (typically 2 decimal places for currency)
- Avoid floating-point arithmetic for financial data

### Multilingual Support
- Store translatable strings in `lang/` directory
- Use Laravel's localization features
- Implement proper date/time formatting
- Consider timezone handling

### File Structure
```
app/
├── Console/          # Artisan commands
├── Filament/         # Admin panel components
├── Http/             # Controllers, middleware
├── Jobs/             # Queue jobs
├── Models/           # Eloquent models
├── Observers/        # Model observers
├── Policies/         # Authorization policies
├── Providers/        # Service providers
├── Services/         # Business logic services
└── View/             # View composers

tests/
├── Feature/          # Application feature tests
├── Unit/             # Unit tests
└── Property/         # Property-based tests
```

Remember to always test thoroughly and follow Laravel's best practices when working with this codebase.