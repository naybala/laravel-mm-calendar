# Laravel MM Calendar

A simple and elegant Laravel package for converting Gregorian dates to **Myanmar Calendar** data.

---

## Requirements

| Dependency | Version                                            |
| ---------- | -------------------------------------------------- |
| PHP        | `^8.1`                                             |
| Laravel    | `^8.0 \| ^9.0 \| ^10.0 \| ^11.0 \| ^12.0 \| ^13.0` |
| Carbon     | `^2.0 \| ^3.0`                                     |

---

## Installation

### Step 1 — Install via Composer

```bash
composer require naybala/laravel-mm-calendar:^1.1
```

---

### Step 2 — Service Provider (Auto-Discovery)

Laravel **8.x and above** support package auto-discovery. The service provider and facade alias are registered automatically — **no further action is needed**.

If auto-discovery is disabled in your project (i.e. the package is listed under `dont-discover` in `composer.json`), register manually in `config/app.php`:

```php
'providers' => [
    // ...
    Naybala\MMCalendar\MMCalendarServiceProvider::class,
],

'aliases' => [
    // ...
    'MMCalendar' => Naybala\MMCalendar\Facades\MMCalendar::class,
],
```

---

### Step 3 — Publish Calendar Data (Optional)

The package ships with pre-built calendar data for supported years. If you want to customize or extend the data files, publish them to your application:

```bash
php artisan vendor:publish --tag=mm-calendar-data
```

This copies the JSON calendar files to `resources/mm-calendar/` in your project. The package will automatically use your local copies if they exist.

---

## Basic Usage

### Via Facade

```php
use Naybala\MMCalendar\Facades\MMCalendar;

// Single date — returns MMCalendarResult (or null if not found)
$day = MMCalendar::fromGregorian('2026-06-07');

// Today
$today = MMCalendar::today();

// Carbon instance
$day = MMCalendar::fromGregorian(Carbon::parse('2026-06-07'));

// Low-level alias
$day = MMCalendar::get('2026-06-07');
```

### Via Dependency Injection

```php
use Naybala\MMCalendar\MMCalendar;

class MyController extends Controller
{
    public function __construct(protected MMCalendar $calendar) {}

    public function index()
    {
        return $this->calendar->today()->toArray();
    }
}
```

### Via App Container

```php
$day = app('mm-calendar')->get('2026-06-07');
```

---

## Batch / Array Lookup

Pass an array of dates to get results keyed by date string:

```php
$days = MMCalendar::fromGregorian(['2026-06-07', '2026-06-08']);

// => [
//      '2026-06-07' => MMCalendarResult,
//      '2026-06-08' => MMCalendarResult,
//    ]

$days['2026-06-07']->toMm();   // "နယုန်"
$days['2026-06-08']->toEn();   // "Nayon"
```

---

## MMCalendarResult Methods

Every single-date lookup returns an `MMCalendarResult` object.

### Raw Field Getters

```php
$day = MMCalendar::fromGregorian('2026-06-07');

$day->mmYear();   // 1388  (Myanmar Era year)
$day->mmMonth();  // 3     (month number)
$day->mmDay();    // 7     (day of month)
$day->mmIndex();  // sequential day index
```

### Month Names

```php
$day->toMm();              // "နယုန်"     (Myanmar script)
$day->toEn();              // "Nayon"     (English transliteration)
$day->monthName();         // "နယုန်"     (default: Myanmar)
$day->monthName('en');     // "Nayon"
```

### Myanmar Numerals

```php
$day->toMmNumerals(1388);  // "၁၃၈၈"
$day->toMmNumerals(7);     // "၇"
```

### Formatted Labels

```php
$day->label();    // "၁၃၈၈ ခုနှစ်၊ နယုန်လ ၇ ရက်"
$day->labelEn();  // "7 Nayon 1388"
```

### Custom Format

Use `{token}` placeholders:

| Token            | Output  |
| ---------------- | ------- |
| `{year}`         | `1388`  |
| `{month}`        | `3`     |
| `{day}`          | `7`     |
| `{month_mm}`     | `နယုန်` |
| `{month_en}`     | `Nayon` |
| `{year_mm}`      | `၁၃၈၈`  |
| `{month_mm_num}` | `၃`     |
| `{day_mm}`       | `၇`     |

```php
$day->format('{year} ခုနှစ်၊ {month_mm}လ {day} ရက်');
// => "1388 ခုနှစ်၊ နယုန်လ 7 ရက်"

$day->format('{year_mm} ခုနှစ်၊ {month_mm}လ {day_mm} ရက်');
// => "၁၃၈၈ ခုနှစ်၊ နယုန်လ ၇ ရက်"

$day->format('{day} {month_en} {year}');
// => "7 Nayon 1388"
```

### Raw Array

```php
$day->toArray();
// => ['mm_year' => 1388, 'mm_month' => 3, 'mm_day' => 7, 'mm_index' => 16811]
```

### Property & Array Access (Backward Compatible)

```php
$day->mm_year;      // 1388
$day['mm_month'];   // 3
```

---

## Myanmar Month Reference

| #   | Myanmar       | English    |
| --- | ------------- | ---------- |
| 1   | တန်ခူး        | Tagu       |
| 2   | ကဆုန်         | Kason      |
| 3   | နယုန်         | Nayon      |
| 4   | ဝါဆို         | Waso       |
| 5   | ဝါခေါင်       | Wagaung    |
| 6   | တော်သလင်း     | Tawthalin  |
| 7   | သီတင်းကျွတ်   | Thadingyut |
| 8   | တန်ဆောင်မုန်း | Tazaungmon |
| 9   | နတ်တော်       | Nadaw      |
| 10  | ပြာသို        | Pyatho     |
| 11  | တပို့တွဲ      | Tabodwe    |
| 12  | တပေါင်း       | Tabaung    |

---

## Error Handling

A `RuntimeException` is thrown when a calendar data file for the requested year does not exist:

```php
try {
    $day = MMCalendar::fromGregorian('2030-01-01');
} catch (RuntimeException $e) {
    // "Myanmar calendar data for year 2030 is not available."
    logger()->warning($e->getMessage());
}
```

---

## Adding Calendar Data

Calendar data is stored as JSON files in `resources/calendars/{year}_calendar.json`.

Each file is a JSON object keyed by Gregorian date:

```json
{
  "2026-01-01": {
    "mm_year": 1387,
    "mm_month": 10,
    "mm_day": 14,
    "mm_index": 16654
  },
  "2026-01-02": {
    "mm_year": 1387,
    "mm_month": 10,
    "mm_day": 15,
    "mm_index": 16654
  }
}
```

To support a new year, add the corresponding `{year}_calendar.json` to the `resources/calendars/` directory.

---

## License

Open-sourced under the [MIT license](https://opensource.org/licenses/MIT).

---

## Author

**Naybala** — Myanmar Calendar Package for Laravel
