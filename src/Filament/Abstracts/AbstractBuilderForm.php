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
     * Input isimlerini dinamik olarak oluşturur
     *
     * @param  string  $inputName  Input ismi
     * @return string Input ismi (örn: "{key}.{inputName}")
     */
    public static function generateInputName(string $inputName): string
    {
        return static::$key.'.'.$inputName;
    }

    /**
     * Her alt sınıfın kendi form şemasını tanımlaması için abstract metot
     */
    abstract public static function getFormSchema(): array;

    /**
     * Her alt sınıfın kendi element verisini tanımlaması için abstract metot
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
