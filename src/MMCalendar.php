<?php

namespace Naybala\MMCalendar;

use Carbon\Carbon;
use RuntimeException;

class MMCalendar
{
    protected array $cache = [];

    public function get(string|Carbon $date): ?array
    {
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        $year = substr($date, 0, 4);

        $data = $this->loadYear($year);

        return $data[$date] ?? null;
    }

    /**
     * Convert one or more Gregorian dates to Myanmar calendar data.
     *
     * - Single date (string or Carbon): returns ?array with mm_year, mm_month, mm_day, mm_index
     * - Array of dates: returns array keyed by each date string, value is the same ?array per date
     *
     * Examples:
     *   MMCalendar::fromGregorian('2026-06-07');
     *   // => ['mm_year' => 1388, 'mm_month' => 3, 'mm_day' => 7, 'mm_index' => ...]
     *
     *   MMCalendar::fromGregorian(['2026-06-07', '2026-06-08']);
     *   // => [
     *   //      '2026-06-07' => ['mm_year' => 1388, 'mm_month' => 3, 'mm_day' => 7,  ...],
     *   //      '2026-06-08' => ['mm_year' => 1388, 'mm_month' => 3, 'mm_day' => 8,  ...],
     *   //    ]
     */
    public function fromGregorian(string|Carbon|array $date): array|null
    {
        if (is_array($date)) {
            return array_combine(
                $date,
                array_map(fn ($d) => $this->get($d), $date)
            );
        }

        return $this->get($date);
    }

    public function today(): ?array
    {
        return $this->get(now());
    }

    protected function loadYear(string $year): array
    {
        if (isset($this->cache[$year])) {
            return $this->cache[$year];
        }

        $file = __DIR__ . '/../resources/calendars/' . $year . '_calendar.json';

        if (!file_exists($file)) {
            throw new RuntimeException(
                "Myanmar calendar data for year {$year} is not available. "
                . "Expected file: {$file}"
            );
        }

        // JSON is already keyed by gregorian_date — decode directly.
        return $this->cache[$year] = json_decode(file_get_contents($file), true);
    }
}
