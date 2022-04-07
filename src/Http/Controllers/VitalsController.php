<?php

namespace MarcoRieser\StatamicVitals\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Statamic\Facades\Addon;
use Statamic\Marketplace\Marketplace;
use Statamic\Statamic;
use Statamic\Updater\UpdatesOverview;

class VitalsController
{
    const CACHE_FOR_MINUTES = 60;

    protected array $vitals = [];

    public function __invoke()
    {
        if (!$this->checkAccess()) {
            return new JsonResponse('Wrong access key.', '401');
        }

        if (!$this->shouldServeFromCache()) {
            $this->collectSystemVitals();
            $this->collectStatamicVitals();
            $this->collectAddonsVitals();
            $this->collectAvailableUpdates();
            $this->cacheVitals();
        }

        return new JsonResponse($this->vitals);
    }

    protected function checkAccess(): bool
    {
        return config('statamic.vitals.access_key', '') === request('access_key');
    }

    protected function shouldServeFromCache(): bool
    {
        if (request('clear_cache')) {
            return false;
        }

        if ($vitals = cache('statamic.vitals')) {
            $this->vitals = $vitals;
            return true;
        }

        return false;
    }

    protected function collectSystemVitals(): void
    {
        $this->vitals['system'] = [
            'name' => config('app.name'),
            'domain' => config('app.url'),
            'environment' => config('app.env'),
            'laravel' => App::version(),
            'php' => PHP_VERSION,
        ];
    }

    protected function collectStatamicVitals(): void
    {
        $this->vitals['statamic'] = [
            'version' => Statamic::version(),
            'latest_version' => app(Marketplace::class)->statamic()->changelog()->latest()->version,
            'pro' => Statamic::pro(),
            'antlers_version' => config('statamic.antlers.version'),
            'update_available' => app(UpdatesOverview::class)->hasStatamicUpdate(true)
        ];
    }

    protected function collectAddonsVitals(): void
    {
        /** @var \Statamic\Extend\Addon $addon */
        foreach (Addon::all() as $addon) {
            $this->vitals['addons'][] = [
                'name' => $addon->name(),
                'package' => $addon->package(),
                'version' => $addon->version(),
                'latest_version' => $addon->latestVersion(),
                'update_available' => !$addon->isLatestVersion()
            ];
        }
    }

    protected function collectAvailableUpdates(): void
    {
        $this->vitals['updates_available'] = app(UpdatesOverview::class)->count(true) ?: false;
    }

    protected function cacheVitals(): void
    {
        $expiry = Carbon::now()->addMinutes(self::CACHE_FOR_MINUTES);
        cache(['statamic.vitals' => $this->vitals], $expiry);
    }
}
