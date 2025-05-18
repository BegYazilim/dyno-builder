<?php

namespace BegYazilim\DynoBuilder;

use BegYazilim\DynoBuilder\Commands\DynoBuilderCommand;
use BegYazilim\DynoBuilder\Commands\ElementCreateCommand;
use BegYazilim\DynoBuilder\Commands\FormCreateCommand;
use BegYazilim\DynoBuilder\Commands\PageCreateCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DynoBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('dyno-builder')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_dyno_elements_table',
            ])
            ->hasCommands([
                DynoBuilderCommand::class,

            ])->hasConsoleCommands([
                PageCreateCommand::class,
                ElementCreateCommand::class,
                FormCreateCommand::class,
            ]);
    }
}
