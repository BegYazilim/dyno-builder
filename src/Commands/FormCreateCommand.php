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

        // Name parametresinden dizin yapısını çıkar
        $nameParts = explode('/', $name);
        $formClassName = array_pop($nameParts); // Son elemanı sınıf adı olarak al
        $directory = ! empty($nameParts) ? implode('/', $nameParts) : 'Home'; // Geri kalanı dizin yapısı olarak kullan, boşsa Home olsun

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

        // Sadece form oluştur, component oluşturmayı önerme
        $this->info("Form class {$className} created successfully at {$fullPath}");

        $componentPath = app_path("View/Components/{$directory}/{$className}.php");
        if (! File::exists($componentPath)) {
            $this->info("Not: Component sınıfı oluşturmak için: sail artisan theme:make-page {$formClassName} --page={$directory}");
        }

        return 0;
    }
}
