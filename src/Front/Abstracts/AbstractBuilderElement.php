<?php

namespace BegYazilim\DynoBuilder\Front\Abstracts;

use BegYazilim\DynoBuilder\Enums\ElementTypeEnum;
use BegYazilim\DynoBuilder\Models\ThemeElement;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

abstract class AbstractBuilderElement extends Component
{
    /**
     * The element type for the component.
     */
    public static ElementTypeEnum $elementType = ElementTypeEnum::COMPONENT;

    /**
     * Component Key
     */
    protected static string $key = 'default';

    /**
     * Cache duration in hours
     */
    protected static int $cacheDuration = 24;

    /**
     * Cache required for this component
     * If set to true, the component will use cache for data retrieval.
     */
    protected static bool $cacheRequired = false;

    /**
     * Get the data that should be supplied to the view.
     */
    abstract protected function componentData(): array;

    /**
     * Render the component without element storage.
     */
    abstract protected function renderComponent(): mixed;

    /**
     * Component'i render eder
     */
    public function render(): mixed
    {
        return $this->renderComponent();
    }

    /**
     * Set Key
     */
    public function setKey(string $key): self
    {
        static::setStaticKey($key);

        return $this;
    }

    /**
     * Set the static key value
     */
    public static function setStaticKey(string $key): void
    {
        if (property_exists(static::class, 'key')) {
            $reflection = new \ReflectionClass(static::class);
            $property = $reflection->getProperty('key');
            $property->setValue(null, $key);
        }
    }

    /**
     * Get the key for the component.
     */
    public static function getKey(): string
    {
        $reflection = new \ReflectionClass(static::class);
        $property = $reflection->getProperty('key');

        return $property->isInitialized() ? $property->getValue() : 'default';
    }

    /**
     * Get a specific element key with suffix.
     */
    protected static function getElementKeyWithSuffix(string $suffix): string
    {
        return static::getKey().'.'.$suffix;
    }

    /**
     * Get the element key.
     */
    protected static function getElementKey(): string
    {
        return static::getKey();
    }

    /**
     * Get cache key for element
     */
    protected static function getCacheKey(string $elementKey, ?string $page_id = null): string
    {
        return 'theme_element_'.$elementKey.($page_id ? '_'.$page_id : '');
    }

    /**
     * Helper method to extract specific data from element storage
     */
    protected static function extractElementData(?array $elementData, string $key, mixed $default = null): mixed
    {
        if (! $elementData) {
            return $default;
        }

        return $elementData[$key] ?? $default;
    }

    /**
     * Get element data from cache or database
     */
    public static function getElementData(?string $page_id = null): ?array
    {
        if (! static::$cacheRequired) {
            return null;
        }

        $elementKey = static::getKey();
        $cacheKey = static::getCacheKey($elementKey, $page_id);

        // Check if data is available in cache
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            return $cachedData;
        }

        // If cache is not available, fetch from database
        $data = ThemeElement::getElementData($elementKey, $page_id);

        // Komponentler için created_at alanını ekle
        if ($data && ! isset($data['created_at']) && static::getElementType() === ElementTypeEnum::COMPONENT) {
            $data['created_at'] = now()->timestamp;
            $data['element_key'] = static::getElementKey();
        }

        // Veri varsa önbelleğe al
        if ($data) {
            Cache::put($cacheKey, $data, now()->addHours(static::$cacheDuration));
        }

        return $data;
    }

    /**
     * Set element data and cache it
     */
    public static function setElementData(array $data, ?string $page_id = null): void
    {
        if (! static::$cacheRequired) {
            return;
        }

        $elementKey = static::getKey();

        // created_at check
        if (! isset($data['created_at'])) {
            $data['created_at'] = now()->timestamp;
            $data['element_key'] = static::getElementKey();
        }

        // Save to database
        static::saveElement($elementKey, $data, $page_id);

        // Save to cache
        $cacheKey = static::getCacheKey($elementKey, $page_id);
        Cache::put($cacheKey, $data, now()->addHours(static::$cacheDuration));
    }

    /**
     * Helper method to save elements
     * This method ensures data is first saved to database, then to file system.
     */
    protected static function saveElement(string $elementKey, array $data, ?string $page_id = null): void
    {
        ThemeElement::saveElement(
            $elementKey,
            static::getElementType()->value,
            $data,
            $page_id,
            true // store_in_file - ensures data is stored in both database and file system
        );
    }

    /**
     * Delete element and all related elements including cache.
     */
    public static function deleteElement(?string $page_id = null): void
    {
        static::deleteElementByKey(static::getKey(), $page_id);
    }

    /**
     * Delete specific element by key.
     */
    public static function deleteElementByKey(string $key, ?string $page_id = null): void
    {
        ThemeElement::deleteElement($key, $page_id);

        // Clear cache
        $cacheKey = static::getCacheKey($key, $page_id);
        Cache::forget($cacheKey);
    }

    /**
     * Delete all elements for a specific type.
     */
    public static function deleteElementsByType(?ElementTypeEnum $type = null, ?string $page_id = null): void
    {
        $typeToUse = $type ?? static::getElementType();
        $elements = $page_id
            ? ThemeElement::getElementsByType($typeToUse->value, $page_id)
            : ThemeElement::getElementsByType($typeToUse->value);

        foreach ($elements as $element) {
            // @phpstan-ignore-next-line
            ThemeElement::deleteElement($element->element_key, $element->page_id);

            // Clear cache
            // @phpstan-ignore-next-line
            $cacheKey = static::getCacheKey($element->element_key, $element->page_id);
            Cache::forget($cacheKey);
        }
    }

    /**
     * Get the element type.
     */
    public static function getElementType(): ElementTypeEnum
    {
        return static::$elementType ?? ElementTypeEnum::GENERAL;
    }

    /**
     * Get Cache Required
     */
    public static function cacheRequired(): bool
    {
        return static::$cacheRequired ?? false;
    }
}
