<?php

namespace BegYazilim\DynoBuilder\Commands;

use BegYazilim\DynoBuilder\Enums\ElementTypeEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ElementCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dyno-builder:create-element
                            {name? : The name of the page element class}
                            {--page= : The page name (e.g. Home, About, etc.)}
                            {--force : Force overwrite if element already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new page component for a page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Interactive mode if arguments are missing
        $name = $this->argument('name');
        if (empty($name)) {
            $name = $this->ask('What is the name of the page element?');
        }

        $page = $this->option('page');
        if (empty($page)) {
            $page = $this->ask('What is the page name? (e.g. Home, About)');
        }

        $force = $this->option('force');

        $type = strtoupper(ElementTypeEnum::COMPONENT->value);

        // Determine namespace and path
        $baseNamespace = 'App\\View\\Components';
        $basePath = app_path('View/Components');

        $studlyPage = Str::studly($page);
        $namespace = "{$baseNamespace}\\{$studlyPage}";
        $path = "{$basePath}/{$studlyPage}";
        $key = strtolower(Str::snake($page)).'.'.strtolower(Str::snake($name));
        $viewPath = 'components.'.strtolower(Str::snake($page)).'.'.strtolower(Str::kebab($name));

        // Create directory if it doesn't exist
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // Build the class name
        $className = Str::studly($name);
        $fullPath = "{$path}/{$className}.php";

        // Check if file already exists
        if (File::exists($fullPath) && ! $force) {
            $this->error("Element {$className} already exists! Use --force to overwrite.");

            return 1;
        }

        // Get stub content and replace placeholders
        $stub = file_get_contents(__DIR__.'/../../stubs/page_element.stub');
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ key }}', '{{ type }}', '{{ view }}'],
            [$namespace, $className, $key, $type, $viewPath],
            $stub
        );

        // Write the file
        File::put($fullPath, $stub);

        // Create view file directory if it doesn't exist
        $viewDirectory = resource_path('views/'.str_replace('.', '/', $viewPath));
        $viewDirectoryPath = dirname($viewDirectory);
        if (! File::isDirectory($viewDirectoryPath)) {
            File::makeDirectory($viewDirectoryPath, 0755, true);
        }

        // Create a basic view file if it doesn't exist
        $viewFilePath = resource_path('views/'.str_replace('.', '/', $viewPath).'.blade.php');
        if (! File::exists($viewFilePath)) {
            File::put($viewFilePath, "<div class=\"theme-element {$key}\">\n    <!-- {$className} Component -->\n    <h2>{$className}</h2>\n    <p>Edit this template at: {$viewFilePath}</p>\n</div>");
            $this->info("View created at: {$viewFilePath}");
        }

        $this->info("Element {$className} created successfully!");
        $this->info("File: {$fullPath}");

        return 0;
    }
}
