<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 👇 TAMBAHKAN INI
        if ($this->app->environment('local')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
        // 👆 SAMPAI SINI
    }

    public function boot()
    {
        //
    }
}