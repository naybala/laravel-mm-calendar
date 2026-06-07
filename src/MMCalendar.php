<?php

namespace Naybala\MMCalendar;

use Carbon\Carbon;
use RuntimeException;

class MMCalendar
{
    protected array $cache = [];

    public function get(string|Carbon $date): ?MMCalendarResult
    {
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        $year = substr($date, 0, 4);

        $data = $this->loadYear($year);

        return isset($data[$date]) ? new MMCalendarResult($data[$date]) : null;
    }

    /**
     * Convert one or more Gregorian dates to Myanmar calendar data.
     *
     * - Single date (string|Carbon) → ?MMCalendarResult
     * - Array of dates             → array<string, MMCalendarResult|null>
     *
     * Examples:
     *   MMCalendar::fromGregorian('2026-06-07');
     *   MMCalendar::fromGregorian('2026-06-07')->toMm();   // "နယုန်"
     *   MMCalendar::fromGregorian('2026-06-07')->toEn();   // "Nayon"
     *   MMCalendar::fromGregorian('2026-06-07')->toArray();
     *   MMCalendar::fromGregorian('2026-06-07')->mm_year;  // 1388
     *
     *   MMCalendar::fromGregorian(['2026-06-07', '2026-06-08']);
     *   // => [
     *   //      '2026-06-07' => MMCalendarResult,
     *   //      '2026-06-08' => MMCalendarResult,
     *   //    ]
     */
    public function fromGregorian(string|Carbon|array $date): MMCalendarResult|array|null
    {
        if (is_array($date)) {
            return array_combine(
                $date,
                array_map(fn ($d) => $this->get($d), $date)
            );
        }

        return $this->get($date);
    }

    public function today(): ?MMCalendarResult
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
