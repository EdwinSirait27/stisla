<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        if (env('APP_ENV') !== 'local') {
            // URL::forceScheme('https');
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

// $employee = \App\Models\Employee::where('id', '0196bf55-ff53-722b-bc8b-1d907876ed15')->first();

// Cek config SPK untuk company employee ini
// \App\Models\Companydocumentconfigs::with('documenttypes')->where('company_id', '0199eb83-7351-729f-9def-c33ae7450447')->whereHas('documenttypes', fn($q) => $q->where('nickname', 'SPK'))->where('is_active', true)->first()