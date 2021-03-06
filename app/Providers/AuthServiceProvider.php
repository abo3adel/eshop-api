<?php

namespace App\Providers;

use App\Policies\ProductPolicy;
use App\Product;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use \Dusterio\LumenPassport\LumenPassport;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first();
            }
        });

        LumenPassport::routes($this->app);

        // LumenPassport::tokensExpireIn(Carbon::now()->addDay());
        // Passport::refreshTokensExpireIn(Carbon::now()->addDays(5));
        // Passport::personalAccessTokensExpireIn(
        //     Carbon::now()->addWeeks(2)
        // );

        Passport::tokensCan([
            'create-sub' => 'Create Sub Categories',
            'patch-role' => 'Change User Role'
        ]);

        Gate::policy(Product::class, ProductPolicy::class);
    }
}
