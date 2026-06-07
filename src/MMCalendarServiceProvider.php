<?php

namespace Naybala\MMCalendar;

use Illuminate\Support\ServiceProvider;

class MMCalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('mm-calendar', fn () => new MMCalendar());
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/calendars' => resource_path('mm-calendar'),
            ], 'mm-calendar-data');
        }
    }
}
