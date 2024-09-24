<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {


            // Rotas para o login AppClient
            Route::middleware('api')
                ->prefix('app_client')
                ->group(base_path('routes/api/appClient/api.php'));


            // Rotas para o login AppClient
            Route::middleware('portal.admin')
                ->prefix('health-checker/')
                ->group(base_path('routes/api/healthChecker/api.php'));

            // Rotas para o login Portal
            Route::prefix('portal/auth')
                ->group(base_path('routes/api/portal/auth.php'));

            // Rotas para o Portal
            Route::prefix('portal')
                ->group(base_path('routes/api/portal/api.php'));

            // Rotas para o gerenciamento máximo do Portal
            Route::middleware(['portal', 'portal.master'])
                ->prefix('portal/management/master/')
                ->group(base_path('routes/api/portal/management/master.php'));

            // Rotas para o gerenciamento administrativo do Portal
            Route::middleware(['portal', 'portal.admin'])
                ->prefix('portal/management/admin/')
                ->group(base_path('routes/api/portal/management/admin.php'));


            // Rotas para o AgeRv
            Route::middleware('portal')
                ->prefix('portal/agerv/b2b/')
                ->group(base_path('routes/api/portal/agerv/b2b/api.php'));


            // Rotas para o AgeRv Retenção
            Route::middleware('portal')
                ->prefix('portal/agerv/retention/')
                ->group(base_path('routes/api/portal/agerv/retention/api.php'));

            // Rotas para o Age Report
            Route::middleware('portal')
                ->prefix('portal/agereport/')
                ->group(base_path('routes/api/portal/ageReport/api.php'));

            // Rotas para os serviços diversos
            Route::middleware('portal')
                ->prefix('portal/services/')
                ->group(base_path('routes/api/portal/services/api.php'));

            // Rotas para a integração de boletos Digitro x Voalle
            Route::middleware('portal')
                ->prefix('integrator/voalle/')
                ->group(base_path('routes/api/integrator/voalle/billet.php'));

            // Rotas para a integração de boletos Portal x Voalle x Aniel
            Route::middleware('portal')
                ->prefix('integrator/aniel/')
                ->group(base_path('routes/api/integrator/aniel/schedule.php'));


            // Rotas para a integração de boletos Digitro x Voalle
            Route::middleware('portal')
                ->prefix('portal/ageCommunicate/')
                ->group(base_path('routes/api/portal/ageCommunicate/api.php'));


            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
