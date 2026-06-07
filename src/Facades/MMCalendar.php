<?php

namespace Naybala\MMCalendar\Facades;

use Illuminate\Support\Facades\Facade;

class MMCalendar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mm-calendar';
    }
}
