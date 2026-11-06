<?php

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

// Laravel only sends CommandFinished outside unit tests, so the tests send it themselves.
function outputAfter(string $command): string
{
    event(new CommandFinished($command, new ArrayInput([]), $output = new BufferedOutput(), 0));

    return $output->fetch();
}

afterEach(function () {
    File::delete(File::glob(database_path('migrations/*_attach*.php')));
});

it('names the migrations an app has neither published nor run after migrate', function () {
    expect(outputAfter('migrate'))
        ->toContain('laravel-attachment-library has 7 migrations that are not published')
        ->toContain('create_attachment_captions_table')
        ->toContain('php artisan vendor:publish --tag=attachment-library-migrations')
        ->and(outputAfter('migrate:status'))->toBe('');
});

it('stays quiet once the migrations are published', function () {
    $this->artisan('vendor:publish', ['--tag' => 'attachment-library-migrations'])->assertSuccessful();

    expect(outputAfter('migrate'))->toBe('');
});

it('stays quiet about migrations that ran but whose files are gone, as after schema:dump --prune', function () {
    foreach (File::files(__DIR__ . '/../database/migrations') as $file) {
        DB::table('migrations')->insert([
            'migration' => '2020_01_01_000000_' . Str::before($file->getFilename(), '.php'),
            'batch' => 1,
        ]);
    }

    expect(outputAfter('migrate'))->toBe('');
});
