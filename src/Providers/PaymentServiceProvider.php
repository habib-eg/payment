<?php


namespace Habib\Payment\Providers;

use Habib\Payment\Facades\Cart;
use Habib\Payment\Helpers\CartDataBase;
use Habib\Payment\Helpers\CartSession;
use Habib\Payment\Helpers\CartSystem;
use Habib\Payment\Helpers\PaymentCheckout;
use Habib\Payment\Helpers\PaymentCheckoutInterface;
use Habib\Payment\Routes\RouterPayment;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Srmklive\PayPal\Facades\PayPal;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/payment.php', 'payment');

        $this->app->alias(PayPal::class, 'PayPal');

        $this->app->singleton(PaymentCheckoutInterface::class, function () {
            return new PaymentCheckout();
        });


        $this->app->alias(Cart::class, 'Cart');
        Router::mixin(new RouterPayment());
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('cart', function ($app) {
            return new CartSystem();
        });
        $packagePath = dirname(__DIR__);

        if ($this->app->runningInConsole()) {
            // config
            $this->publishes([$packagePath . '/config/payment.php' => config_path('payment.php'),], 'config');
            //publish migrations
            $this->publishes([$packagePath . '/database/migrations/' => database_path('migrations')], 'migrations');
            $this->loadMigrationsFrom($packagePath . '/database/migrations');

        }


        Route::mixin(new RouterPayment());
    }
}
