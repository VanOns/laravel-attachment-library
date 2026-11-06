<?php

namespace VanOns\LaravelAttachmentLibrary\Listeners;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Factory;
use Illuminate\Support\ServiceProvider;

// Publishing migrations stays a manual step, so `migrate` names the ones an app has neither published nor run.
class WarnAboutUnpublishedMigrations
{
    public function __construct(
        protected string $provider,
        protected string $package,
        protected string $tag,
    ) {
    }

    public function handle(CommandFinished $event): void
    {
        if ($event->command !== 'migrate') {
            return;
        }

        // A hint must never break a deploy, e.g. when the migrations table cannot be read.
        $missing = rescue(fn (): array => $this->unpublished(), [], report: false);

        if ($missing === []) {
            return;
        }

        (new Factory(new OutputStyle($event->input, $event->output)))->warn(sprintf(
            'Migrations of %s that are not published: %s. Run `php artisan vendor:publish --tag=%s`, then `php artisan migrate`.',
            $this->package,
            implode(', ', $missing),
            $this->tag,
        ));
    }

    /**
     * @return list<string>
     */
    public function unpublished(): array
    {
        $ran = array_map(self::withoutTimestamp(...), app('migrator')->getRepository()->getRan());

        return collect(ServiceProvider::pathsToPublish($this->provider, $this->tag))
            ->reject(fn (string $to): bool => file_exists($to))
            ->map(fn (string $to): string => self::withoutTimestamp(basename($to, '.php')))
            ->reject(fn (string $name): bool => in_array($name, $ran, true))
            ->values()
            ->all();
    }

    protected static function withoutTimestamp(string $migration): string
    {
        return preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migration);
    }
}
