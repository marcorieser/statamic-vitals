<?php

namespace MarcoRieser\StatamicVitals\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Statamic\Console\RunsInPlease;
use Statamic\Support\Str;

class GenerateKey extends Command
{
    use RunsInPlease;
    use ConfirmableTrait;

    protected $signature = 'statamic:vitals:generate-key {--force : Force the operation to run when in production}';

    protected $description = 'Set the vitals key';

    public function handle()
    {
        $key = $this->generateRandomKey();

        if (!$this->setKeyInEnvironmentFile($key)) {
            return;
        }

        $this->laravel['config']['statamic.vitals.access_key'] = $key;

        $this->info('Vitals key set successfully.');
    }

    protected function generateRandomKey(): string
    {
        return Str::random(12);
    }

    protected function setKeyInEnvironmentFile(string $key): bool
    {
        $currentKey = $this->laravel['config']['statamic.vitals.access_key'];

        if ($currentKey !== '' && (!$this->confirmToProceed())) {
            return false;
        }

        $this->writeNewEnvironmentFileWith($key);

        return true;
    }

    protected function writeNewEnvironmentFileWith($key): void
    {
        $this->ensureKeyExistsInEnvironmentFile();

        file_put_contents($this->laravel->environmentFilePath(), preg_replace(
            $this->keyReplacementPattern(),
            'STATAMIC_VITALS_ACCESS_KEY=' . $key,
            file_get_contents($this->laravel->environmentFilePath())
        ));
    }

    protected function ensureKeyExistsInEnvironmentFile(): void
    {
        if (str_contains(file_get_contents($this->laravel->environmentFilePath()), 'STATAMIC_VITALS_ACCESS_KEY=')) {
            return;
        }
        file_put_contents(
            $this->laravel->environmentFilePath(),
            file_get_contents($this->laravel->environmentFilePath())
            . PHP_EOL . 'STATAMIC_VITALS_ACCESS_KEY='
        );
    }

    protected function keyReplacementPattern(): string
    {
        $escaped = preg_quote('=' . $this->laravel['config']['statamic.vitals.access_key'], '/');

        return "/^STATAMIC_VITALS_ACCESS_KEY{$escaped}/m";
    }
}
