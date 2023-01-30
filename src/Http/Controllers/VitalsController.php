<?php

namespace MarcoRieser\Vitals\Http\Controllers;

use Carbon\Carbon;
use Facades\Statamic\Marketplace\Marketplace;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Statamic\Facades\Addon;
use Statamic\Statamic;

class VitalsController
{
    protected const CACHE_FOR_MINUTES = 60;

    protected array $vitals = [];
    protected int $updatesCount = 0;

    public function __invoke()
    {
        if (!$this->checkAccess()) {
            return new JsonResponse(['error'=>'Wrong access key.'], '401');
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
            'debug' => config('app.debug')
        ];
    }

    protected function collectStatamicVitals(): void
    {
        if ($updateAvailable = (bool)Marketplace::statamic()->changelog()->availableUpdatesCount()) {
            $this->updatesCount++;
        }

        $this->vitals['statamic'] = [
            'version' => Statamic::version(),
            'cp_url' => Statamic::cpRoute('index'),
            'latest_version' => Marketplace::statamic()->changelog()->latest()->version,
            'pro' => Statamic::pro(),
            'antlers_version' => config('statamic.antlers.version'),
            'static_page_cache' => config('statamic.static_caching.strategy'),
            'update_available' => $updateAvailable,
        ];
    }

    protected function collectAddonsVitals(): void
    {
        /** @var \Statamic\Extend\Addon $addon */
        foreach (Addon::all() as $addon) {
            if ($updateAvailable = (bool)$addon->changelog()?->availableUpdatesCount()) {
                $this->updatesCount++;
            }

            $this->vitals['addons'][] = [
                'name' => $addon->name(),
                'package' => $addon->package(),
                'version' => $addon->version(),
                'latest_version' => $addon->changelog()?->latest()->version,
                'update_available' => $updateAvailable,
            ];
        }
    }

    protected function collectAvailableUpdates(): void
    {
        $this->vitals['updates_available'] = $this->updatesCount ?: false;
    }

    protected function cacheVitals(): void
    {
        $expiry = Carbon::now()->addMinutes(self::CACHE_FOR_MINUTES);
        cache(['statamic.vitals' => $this->vitals], $expiry);
    }
}
