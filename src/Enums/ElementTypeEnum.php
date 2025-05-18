<?php

namespace BegYazilim\DynoBuilder\Enums;

enum ElementTypeEnum: string
{
    case COMPONENT = 'component';
    case PAGE = 'page';
    case GLOBAL = 'global';
    case GENERAL = 'general';

    /**
     * Get the label for the element type.
     */
    public function label(): string
    {
        return match ($this) {
            self::PAGE => 'Page',
            self::GLOBAL => 'Global',
            default => 'General',
        };
    }
}
