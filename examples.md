# Dyno Builder Examples

This document provides examples of how to use the Dyno Builder package in your Laravel application.

## Page Builder Example

Here's an example of an implemented page builder:

```php
namespace App\Filament\Pages\Builder;

use App\Filament\PageForms\Home\ProcessFlow;
use BegYazilim\DynoBuilder\Filament\Abstracts\AbstractPageBuilder;

class Homepage extends AbstractPageBuilder
{
    protected static ?string $navigationLabel = 'Anasayfa';
    protected static ?string $title = 'Anasayfa';

    protected bool $debug = true;

    /**
     * Define the builder elements used in this page
     */
    protected function getBuilderElements(): array
    {
        return [
            ProcessFlow::class
        ];
    }

    /**
     * Custom success notification title
     */
    protected function getSuccessNotificationTitle(): string
    {
        return 'Success';
    }
}
```

## Form Component Example

Here's an example of an implemented form:

```php
namespace App\Filament\PageForms\Home;

use App\View\Components\Home\ProcessFlow as HomeProcessFlow;
use BegYazilim\DynoBuilder\Filament\Abstracts\AbstractBuilderForm;
use Filament\Forms;

class ProcessFlow extends AbstractBuilderForm
{
    protected static string $sectionName = 'Process Flow';

    protected static string $key = 'process_flow';

    protected static string $componentClass = HomeProcessFlow::class;

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make(self::getSectionName())
                ->description('Form for process flow')
                ->statePath(self::getKey())
                ->schema([
                    Forms\Components\Repeater::make('process_steps')
                        ->schema([
                            Forms\Components\FileUpload::make('icon')
                                ->required()
                                ->image()
                                ->imageEditor()
                                ->directory('process-icons'),

                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('subtitle')
                                ->maxLength(255),
                        ])
                        ->maxItems(4)
                        ->collapsible(),
                ]),
        ];
    }
}
```

## View Component Example

Here's an example of an implemented element:

```php
namespace App\View\Components\Home;

use BegYazilim\DynoBuilder\Front\Abstracts\AbstractBuilderElement;
use BegYazilim\DynoBuilder\Enums\ElementTypeEnum;

class ProcessFlow extends AbstractBuilderElement
{
    public static string $key = 'home.process_flow';

    protected static bool $cacheRequired = true;

    /**
     * Get the element type.
     */
    public static function getElementType(): ElementTypeEnum
    {
        return ElementTypeEnum::COMPONENT;
    }

    /**
     * Prepare component data.
     */
    protected function componentData(): array
    {
        $data = self::getElementData();
        return [
            'process_steps' => $data['process_steps'] ?? [],
        ];
    }

    /**
     * Render the component.
     */
    protected function renderComponent(): mixed
    {
        return view('components.home.process-flow', $this->componentData());
    }
}
```

## Blade Template Example

Here's an example of a blade template that uses the data from the ProcessFlow element:

```blade
<section class="process__area pt-120 pb-105">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="section__wrapper section__wrapper-2 mb-30 text-center">
                    <span class="st-meta">Flow</span>
                    <h4 class="section__title">Process Flow</h4>
                </div>
            </div>
        </div>
        <div class="row mt-30">
            @foreach ($process_steps as $process_step)
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <div class="process__item text-center mb-40">
                        <div class="process__content">
                            <div class="process__list-icon2 mb-20">
                                <img src={{ \Storage::url($process_step['icon']) }} alt="icon">
                            </div>
                            <span>{{ $process_step['subtitle'] }}</span>
                            <h5 class="p-name mt-20">{{ $process_step['title'] }}</h5>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
```

To use this component in your views:

```blade
<x-home.process-flow />
```

## Advanced Usage Examples

### Caching

Dyno Builder includes a built-in caching system to improve performance. You can control caching behavior for each element:

```php
protected static bool $cacheRequired = true;
protected static int $cacheDuration = 24; // hours
```

### Theme Element Storage

Elements can be stored in both the database and file system for optimal performance and flexibility:

```php
ThemeElement::saveElement(
    $elementKey,
    $elementType,
    $data,
    $pageId,
    true // store_in_file - ensures data is stored in both database and file system
);
```

### Debugging

You can enable debugging mode in your page builders to view the raw data structure:

```php
protected bool $debug = true;
```

This will add a "Toggle Data View" button in the Filament admin panel that allows you to inspect the current data structure.
