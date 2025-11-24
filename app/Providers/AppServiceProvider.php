<?php

namespace App\Providers;

use App\Database\Connectors\SqlServerConnector;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind a custom SQL Server connector that removes unsupported PDO attributes
        $this->app->bind('db.connector.sqlsrv', fn () => new SqlServerConnector);

        // dY`� TAMBAHKAN INI
        if ($this->app->environment('local')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
        // dY`+ SAMPAI SINI
    }

    public function boot()
    {
        //
    }
}
