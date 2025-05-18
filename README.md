# Dyno Builder

[![Latest Version on Packagist](https://img.shields.io/packagist/v/begyazilim/dyno-builder.svg?style=flat-square)](https://packagist.org/packages/begyazilim/dyno-builder)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/begyazilim/dyno-builder/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/begyazilim/dyno-builder/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/begyazilim/dyno-builder/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/begyazilim/dyno-builder/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/begyazilim/dyno-builder.svg?style=flat-square)](https://packagist.org/packages/begyazilim/dyno-builder)

> **Note:** This package is currently under development and is intended for hobby use. It is not recommended for production environments, and the API may change without notice. Please use it with this understanding.

Dyno Builder is a dynamic page building system for Laravel applications with Filament admin panel integration. This package provides developers with tools to create customizable page elements, forms, and components that can be managed through an intuitive admin interface.

## About

Dyno Builder was created to simplify the process of building dynamic page elements in Laravel applications using Filament. The package is still in early development and is primarily maintained as a hobby project.

For detailed code examples, please see the [examples.md](examples.md) file.

## Features

- **Dynamic Page Building**: Create and manage page elements through a user-friendly Filament interface
- **Reusable Components**: Build a library of reusable UI components for your Laravel application
- **Filament Integration**: Seamless integration with the Filament admin panel
- **Caching System**: Built-in caching system for optimal performance
- **Theme Element Storage**: Store theme elements in both database and file system
- **Command Line Tools**: Generate pages, elements, and forms via Artisan commands

## Requirements

- PHP 8.2 or higher
- Laravel 11.x or higher
- Filament 3.x

## Installation

You can install the package via composer:

```bash
composer require begyazilim/dyno-builder
```

After installing the package, publish and run the migrations:

```bash
php artisan vendor:publish --tag="dyno-builder-migrations"
php artisan migrate
```

To publish the configuration file:

```bash
php artisan vendor:publish --tag="dyno-builder-config"
```

Optionally, you can publish the views:

```bash
php artisan vendor:publish --tag="dyno-builder-views"
```

## Usage

Dyno Builder provides several artisan commands to help you create the necessary components for your dynamic pages:

### Basic Commands

```bash
# Create a new page builder
php artisan dyno-builder:create-page Homepage

# Create a form component
php artisan dyno-builder:create-form ProcessFlow

# Create a view component
php artisan dyno-builder:create-element ProcessFlow --page=Home
```

### Component Structure

Dyno Builder uses a three-part structure:

1. **Page Builder** - Defines which forms are used on a page (in `App\Filament\Pages\Builder`)
2. **Form Component** - Creates Filament forms for the admin panel (in `App\Filament\PageForms`)
3. **View Component** - Implements the front-end component logic (in `App\View\Components`)

For detailed code examples of each component, please see the [examples.md](examples.md) file.

## Installation

You can install the package via composer:

```bash
composer require begyazilim/dyno-builder
```

After installing, publish and run the migrations:

```bash
php artisan vendor:publish --tag="dyno-builder-migrations"
php artisan migrate
```

## Advanced Features

The package includes several advanced features that are documented in the [examples.md](examples.md) file:

- **Caching System** - For improved performance
- **Theme Element Storage** - Store elements in both database and file system
- **Debugging Mode** - View raw data structure in the admin panel

## Troubleshooting

If you encounter issues, check the following:

- Ensure element classes have `$cacheRequired = true` property
- Verify blade templates exist in the correct path
- Clear cache with `php artisan cache:clear` if needed

## Credits

- [Berke GULEC](https://github.com/berkegulec)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
