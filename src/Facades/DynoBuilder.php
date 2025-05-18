<?php

namespace BegYazilim\DynoBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BegYazilim\DynoBuilder\DynoBuilder
 */
class DynoBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BegYazilim\DynoBuilder\DynoBuilder::class;
    }
}
