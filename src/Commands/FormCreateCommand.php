<?php

namespace BegYazilim\DynoBuilder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FormCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dyno-builder:create-form
                            {name? : The name of the builder form class (can include directory structure e.g. Home/SliderSection)}
                            {--section-name= : Display name for the section}
                            {--description= : Form description}
                            {--force : Force overwrite if form already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new ThemeBuilder Filament form class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Interactive mode if arguments are missing
        $name = $this->argument('name');
        if (empty($name)) {
            $name = $this->ask('What is the name of the form class?');
        }

        // Extract directory structure from the name parameter
        $nameParts = explode('/', $name);
        $formClassName = array_pop($nameParts); // Take the last element as the class name
        $directory = ! empty($nameParts) ? implode('/', $nameParts) : 'Home'; // Use the rest as directory structure, default to Home if empty

        $sectionName = $this->option('section-name');
        if (empty($sectionName)) {
            $sectionName = $this->ask('What is the display name for this section?', Str::title(Str::snake($formClassName, ' ')));
        }

        $description = $this->option('description');
        if (empty($description)) {
            $description = $this->ask('Enter a description for this form (optional)', 'Form for '.Str::snake($formClassName, ' '));
        }

        $force = $this->option('force');

        // Build the class name and key
        $className = Str::studly($formClassName);
        $key = Str::snake($formClassName);

        // Determine namespace and path
        $baseNamespace = 'App\\Filament\\PageForms';
        $basePath = app_path('Filament/PageForms');

        // Create subdirectory for page if specified
        $namespace = $baseNamespace.'\\'.$directory;
        $path = $basePath.'/'.$directory;

        // Create directory if it doesn't exist
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // Full path to the new file
        $fullPath = "{$path}/{$className}.php";

        // Check if file already exists
        if (File::exists($fullPath) && ! $force) {
            $this->error("Form {$className} already exists! Use --force to overwrite.");

            return 1;
        }

        // Get stub content and replace placeholders
        $stub = file_get_contents(__DIR__.'/../../stubs/form.stub');
        $stub = str_replace(
            [
                '{{ namespace }}',
                '{{ class }}',
                '{{ page }}',
                '{{ section_name }}',
                '{{ key }}',
                '{{ description }}',
            ],
            [
                $namespace,
                $className,
                $directory,
                $sectionName,
                $key,
                $description,
            ],
            $stub
        );

        // Write the file
        File::put($fullPath, $stub);

        // Only create the form, don't create a component
        $this->info("Form class {$className} created successfully at {$fullPath}");

        $componentPath = app_path("View/Components/{$directory}/{$className}.php");
        if (! File::exists($componentPath)) {
            $this->info("Note: To create a component class, run: sail artisan dyno-builder:create-element {$formClassName} --page={$directory}");
        }

        return 0;
    }
}
