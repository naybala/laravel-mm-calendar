# Laravel MM Calendar

A simple and elegant Laravel package for converting Gregorian dates to **Myanmar Calendar** data.

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^8.2` |
| Laravel | `^11.0 \| ^12.0 \| ^13.0` |
| Carbon | `^3.0` |

---

## Installation

Install via Composer:

```bash
composer require naybala/laravel-mm-calendar
```

The package is **auto-discovered** by Laravel — no manual service provider registration is needed.

---

## Usage

### Via Facade

```php
use Naybala\MMCalendar\Facades\MMCalendar;

// Look up a specific date (string)
$day = MMCalendar::get('2026-06-07');

// Look up today's date
$today = MMCalendar::today();

// Pass a Carbon instance
$day = MMCalendar::get(now());
$day = MMCalendar::get(Carbon::parse('2026-06-07'));
```

### Via Dependency Injection

```php
use Naybala\MMCalendar\MMCalendar;

class MyController extends Controller
{
    public function __construct(protected MMCalendar $calendar) {}

    public function index()
    {
        return $this->calendar->today();
    }
}
```

### Via Helper (app container)

```php
$day = app('mm-calendar')->get('2026-06-07');
```

---

## Return Value

Each method returns an `array` for a matched date, or throws a `RuntimeException` if calendar data for the requested year is not available.

```php
[
    "gregorian_date" => "2026-06-07",
    "mm_year"        => 1388,
    "mm_month"       => 3,
    "mm_day"         => 22,
    "mm_index"       => 16811,
]
```

| Field | Type | Description |
|---|---|---|
| `gregorian_date` | `string` | The Gregorian date (`Y-m-d`) |
| `mm_year` | `int` | Myanmar Era year |
| `mm_month` | `int` | Myanmar month number |
| `mm_day` | `int` | Day of the Myanmar month |
| `mm_index` | `int` | Sequential day index in the Myanmar calendar |

---

## Error Handling

If a calendar data file for the requested year does not exist, a `RuntimeException` is thrown:

```php
use RuntimeException;

try {
    $day = MMCalendar::get('2030-01-01');
} catch (RuntimeException $e) {
    // "Myanmar calendar data for year 2030 is not available."
    logger()->warning($e->getMessage());
}
```

---

## Adding Calendar Data

Calendar data is stored as JSON files in:

```
resources/calendars/{year}_calendar.json
```

Each file is a JSON array of daily entries:

```json
[
    {
        "gregorian_date": "2026-01-01",
        "mm_year": 1387,
        "mm_month": 10,
        "mm_day": 14,
        "mm_index": 16654
    }
]
```

To add support for a new year, simply add the corresponding `{year}_calendar.json` file to the `resources/calendars/` directory.

---

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Author

**Naybala** — Myanmar Calendar Package for Laravel
