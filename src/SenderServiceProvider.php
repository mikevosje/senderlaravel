<?php

namespace App\Packages\Sender;

use Illuminate\Support\ServiceProvider;
use App\Packages\Sender\Commands\SenderCommand;

class SenderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/sender-laravel.php' => config_path('sender-laravel.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/sender-laravel'),
            ], 'views');

            $migrationFileName  = 'create_sender_laravel_table.php';
            $migrationFileName2 = 'create_media_table.php';
            if (! $this->migrationFileExists($migrationFileName) || ! $this->migrationFileExists($migrationFileName2)) {
                $this->publishes([
                    __DIR__ . "/../database/migrations/{$migrationFileName}.stub"  => database_path('migrations/' . date('Y_m_d_His',
                            time()) . '_' . $migrationFileName),
                    __DIR__ . "/../database/migrations/{$migrationFileName2}.stub" => database_path('migrations/' . date('Y_m_d_His',
                            time()) . '_' . $migrationFileName2),
                ], 'migrations');
            }

            $this->commands([
                SenderCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sender-laravel');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sender-laravel.php', 'sender-laravel');
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
