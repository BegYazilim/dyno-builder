<?php

namespace BegYazilim\DynoBuilder\Commands;

use Illuminate\Console\Command;

class DynoBuilderCommand extends Command
{
    public $signature = 'dyno-builder';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
