<?php

namespace BegYazilim\DynoBuilder\Filament\Abstracts;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

abstract class AbstractPageBuilder extends Page implements HasForms
{
    use InteractsWithActions, InteractsWithFormActions, InteractsWithForms;

    protected static string $view = 'dyno-builder::builder-page';

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Tema';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    protected bool $debug = false;

    /**
     * Page builder element classes that will be used in form and for data manipulation
     */
    abstract protected function getBuilderElements(): array;

    /**
     * Get the notification title for successful save
     */
    protected function getSuccessNotificationTitle(): string
    {
        return 'Sayfa düzeni başarıyla kaydedildi';
    }

    /**
     * Mount the form with data
     */
    public function mount(): void
    {
        // @phpstan-ignore-next-line
        $this->form->fill($this->getPageData());
    }

    /**
     * Define the form schema based on builder elements
     */
    public function form(Form $form): Form
    {
        $schema = [];

        foreach ($this->getBuilderElements() as $element) {
            $schema = array_merge($schema, $element::getFormSchema());
        }

        // Add data view section if enabled
        if ($this->showDataView && $this->debug) {
            $data = $this->getPageData();
            $formattedData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            $schema[] = Section::make('Mevcut Veri')
                ->description('Bu bölüm, mevcut sayfa yapılandırmasını göstermektedir.')
                ->schema([
                    Placeholder::make('data_view')
                        ->content(new HtmlString(
                            '<div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg overflow-auto">'.
                                '<pre class="text-sm whitespace-pre-wrap">'.htmlspecialchars($formattedData).'</pre>'.
                                '</div>'
                        ))
                        ->extraAttributes([
                            'class' => 'max-h-96 overflow-auto',
                        ]),
                ])
                ->collapsible()
                ->collapsed(false);
        }

        return $form->schema($schema)->statePath('data');
    }

    /**
     * Save the form data
     */
    public function save(): void
    {
        // @phpstan-ignore-next-line
        $form = $this->form;
        $data = $form->getState();

        if ($this->debug) {
            Log::info('Saving data', $data);
        }

        $this->saveCache($data);

        Notification::make()
            ->title($this->getSuccessNotificationTitle())
            ->success()
            ->send();

        // refresh form
        // @phpstan-ignore-next-line
        $this->form->fill($this->getPageData());
    }

    /**
     * Save data for each builder element
     */
    protected function saveCache(array $data): void
    {
        foreach ($this->getBuilderElements() as $element) {
            $key = $element::getKey();
            if (isset($data[$key])) {
                if ($this->debug) {
                    Log::info('Saving data for element: '.$key, $data[$key]);
                }
                $element::setBuilderElementData($data[$key]);
            } else {
                Log::error('No data found for element: '.$key);
            }
        }
    }

    /**
     * Get data for each builder element
     *
     * @throws \Exception if no builder elements are defined
     */
    protected function getPageData(): array
    {
        $builderElements = $this->getBuilderElements();
        $data = [];

        if (empty($builderElements)) {
            throw new \Exception('You must define at least one builder element.');
        }

        foreach ($this->getBuilderElements() as $elementClass) {
            if (! $elementClass::getComponentClass()::cacheRequired()) {
                throw new \Exception('In '.$elementClass.' cache not required please remove component from page builder.');
            }
            $data[$elementClass::getKey()] = $elementClass::getBuilderElementData();
        }

        if ($this->debug) {
            Log::info("Getting Page data for page: {$this->getSlug()}", $data);
        }

        return $data;
    }

    /**
     * Define form actions
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Kaydet')
                ->action(function () {
                    $this->save();
                }),
        ];
    }

    /**
     * Define header actions
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle_data_view')
                ->label('Mevcut Veriyi Görüntüle')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->visible($this->debug)
                ->action(function () {
                    $this->toggleDataView();
                }),
        ];
    }

    /**
     * Toggle data view visibility
     */
    public bool $showDataView = false;

    /**
     * Toggle the data view section
     */
    public function toggleDataView(): void
    {
        $this->showDataView = ! $this->showDataView;
    }

    /**
     * Set Debug mode
     */
    protected function setDebugMode(bool $debug): void
    {
        $this->debug = $debug;
    }
}
