<?php

namespace BegYazilim\DynoBuilder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PageCreateCommand extends Command
{
    public $signature = 'dyno-builder:create-page {name}';

    protected $description = 'Create a new page builder page';

    public function handle(): void
    {
        $name = $this->argument('name');
        $className = Str::studly($name);
        $navigationLabel = $this->ask('Navigation Label');
        $pageTitle = $this->ask('Page Title');
        $successNotificationTitle = $this->ask('Success Notification Title');

        $stub = file_get_contents(__DIR__.'/../../stubs/page_builder.stub');

        $stub = str_replace([
            '{{ class }}',
            '{{ navigation_label }}',
            '{{ page_title }}',
            '{{ success_notification_title }}',
        ], [
            $className,
            $navigationLabel,
            $pageTitle,
            $successNotificationTitle,
        ], $stub);

        $filePath = app_path("Filament/Pages/Builder/{$className}.php");

        if (file_exists($filePath)) {
            $this->error('Class already exists!');

            return;
        }

        $directory = dirname($filePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($filePath, $stub);

        $this->info("Page builder class '{$className}' in path '{$filePath}' created successfully.");
    }
}
