<?php

namespace App\Providers;

use App\Models\Distribution;
use App\Models\Zf;
use App\Models\Zm;
use App\Models\Ifs;
use App\Models\RekapZis;
use App\Models\SetorZis;
use App\Observers\HakAmilObserver;
use App\Observers\ZfObserver;
use App\Observers\ZmObserver;
use App\Observers\IfsObserver;
use App\Observers\PendisObserver;
use App\Observers\RekapZisObserver;
use App\Observers\SetorObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
    }
}
