<?php

namespace App\Providers;

use App\Models\AllocationConfig;
use App\Models\Distribution;
use App\Models\Zf;
use App\Models\Zm;
use App\Models\Ifs;
use App\Models\RekapPendis;
use App\Models\RekapSetor;
use App\Models\RekapZis;
use App\Models\SetorZis;
use App\Observers\AllocationConfigObserver;
use App\Observers\HakAmilObserver;
use App\Observers\ZfObserver;
use App\Observers\ZmObserver;
use App\Observers\IfsObserver;
use App\Observers\PendisObserver;
use App\Observers\RekapPendisToUnitObserver;
use App\Observers\RekapSetorToUnitObserver;
use App\Observers\RekapZisObserver;
use App\Observers\RekapZisToUnitObserver;
use App\Observers\SetorObserver;
use App\View\Composers\AllocationComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Zf::observe(ZfObserver::class);
        Zm::observe(ZmObserver::class);
        Ifs::observe(IfsObserver::class);
        RekapZis::observe(RekapZisObserver::class);
        Distribution::observe(PendisObserver::class);
        Distribution::observe(HakAmilObserver::class);
        SetorZis::observe(SetorObserver::class);

        RekapZis::observe(RekapZisToUnitObserver::class);
        RekapPendis::observe(RekapPendisToUnitObserver::class);
        RekapSetor::observe(RekapSetorToUnitObserver::class);

        // Register observer for allocation config cache invalidation
        AllocationConfig::observe(AllocationConfigObserver::class);

        // Register view composer for allocation data in PDF and report views
        // This provides dynamic allocation percentages to all PDF and report templates
        View::composer([
            'filament.resources.village-resource.pdf',
            'filament.resources.village-resource.op',
            'pdf.*',
            'reports.*',
        ], AllocationComposer::class);
    }
}
