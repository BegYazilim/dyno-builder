<?php

namespace BegYazilim\DynoBuilder\Filament\Abstracts;

abstract class AbstractBuilderForm
{
    protected static string $sectionName;

    protected static string $key;

    protected static string $componentClass;

    public static function getSectionName(?string $append = null): string
    {
        return static::$sectionName.($append ? " {$append}" : '');
    }

    public static function getKey(): string
    {
        return static::$key;
    }

    /**
     * Dynamically generates input names
     *
     * @param  string  $inputName  Input name
     * @return string Input name (e.g.: "{key}.{inputName}")
     */
    public static function generateInputName(string $inputName): string
    {
        return static::$key.'.'.$inputName;
    }

    /**
     * Abstract method for each subclass to define its own form schema
     */
    abstract public static function getFormSchema(): array;

    /**
     * Abstract method for each subclass to define its own element data
     */
    public static function getBuilderElementData(): array
    {
        if (static::$componentClass) {
            return static::$componentClass::getElementData() ?? [];
        }

        return [];
    }

    public static function setBuilderElementData(array $data): void
    {
        if (static::$componentClass) {
            static::$componentClass::setElementData($data);
        }
    }

    public static function getComponentClass(): string
    {
        return static::$componentClass;
    }
}
