<?php

namespace Naybala\MMCalendar;

use Illuminate\Support\ServiceProvider;

class MMCalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('mm-calendar', fn () => new MMCalendar());
    }
}
