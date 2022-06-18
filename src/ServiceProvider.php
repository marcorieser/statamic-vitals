<?php

namespace MarcoRieser\StatamicVitals;

use MarcoRieser\StatamicVitals\Console\Commands\GenerateKey;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        GenerateKey::class
    ];

    public function bootAddon(): void
    {
        $this
            ->autoPublishConfig()
            ->loadRoutes();
    }

    protected function loadRoutes(): self
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        return $this;
    }

    protected function autoPublishConfig(): self
    {
        $this->publishes([
            __DIR__ . '/../config/statamic/vitals.php' => config_path('statamic/vitals.php'),
        ], 'statamic-vitals-config');

        Statamic::afterInstalled(static function ($command) {
            $command->call('vendor:publish', [
                '--tag' => 'statamic-vitals-config'
            ]);
        });

        return $this;
    }
}
