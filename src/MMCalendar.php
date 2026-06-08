<?php
namespace Naybala\MMCalendar;

use Carbon\Carbon;
use RuntimeException;

class MMCalendar
{
    protected array $cache = [];

    protected array $mmMonths = [
        1 => 'တန်ခူး', 2      => 'ကဆုန်', 3         => 'နယုန်', 4    => 'ဝါဆို', 5   => 'ဝါခေါင်', 6   => 'တော်သလင်း',
        7 => 'သီတင်းကျွတ်', 8 => 'တန်ဆောင်မုန်း', 9 => 'နတ်တော်', 10 => 'ပြာသို', 11 => 'တပို့တွဲ', 12 => 'တပေါင်း',
    ];

    protected array $mmMonthsEnglish = [
        1 => 'Tagu', 2       => 'Kason', 3      => 'Nayon', 4  => 'Waso', 5    => 'Wakhaung', 6 => 'Tawthalin',
        7 => 'Thadingyut', 8 => 'Tazaungmon', 9 => 'Nadaw', 10 => 'Pyatho', 11 => 'Tabodwe', 12 => 'Tabaung',
    ];

    public function get(string | Carbon $date): ?MMCalendarResult
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
    public function fromGregorian(string | Carbon | array $date): MMCalendarResult | array | null
    {
        if (is_array($date)) {
            return array_combine(
                $date,
                array_map(fn($d) => $this->get($d), $date)
            );
        }

        return $this->get($date);
    }

    public function today(): ?MMCalendarResult
    {
        return $this->get(Carbon::now());
    }

    public function getMyanmarDate(Carbon $date, $useBurmese = false): string
    {
        $mm        = $this->convert($date);
        $months    = $useBurmese ? $this->mmMonths : $this->mmMonthsEnglish;
        $monthName = $months[$mm['month']] ?? $mm['month'];

        // Determine Waxing/Waning (1-15 Waxing, 16-30 Waning)
        $isWaxing   = $mm['day'] <= 15;
        $displayDay = $isWaxing ? $mm['day'] : ($mm['day'] - 15);
        $status     = $isWaxing ? ($useBurmese ? 'လဆန်း' : 'Waxing') : ($useBurmese ? 'လဆုတ်' : 'Waning');

        if ($useBurmese) {
            return "{$this->toMyanmarNumber($mm['year'])} ခု၊ {$monthName} {$status} ({$this->toMyanmarNumber($displayDay)}) ရက်";
        }

        return "{$mm['year']}-{$monthName}-{$status}-{$displayDay}";
    }

    protected function loadYear(string $year): array
    {
        if (isset($this->cache[$year])) {
            return $this->cache[$year];
        }

        $file = __DIR__ . '/../resources/calendars/' . $year . '_calendar.json';

        if (! file_exists($file)) {
            throw new RuntimeException(
                "Myanmar calendar data for year {$year} is not available. "
                . "Expected file: {$file}"
            );
        }

        // JSON is already keyed by gregorian_date — decode directly.
        return $this->cache[$year] = json_decode(file_get_contents($file), true);
    }

    protected function toMyanmarNumber(int $number): string
    {
        $myanmarNumbers = [
            0 => '၀', 1 => '၁', 2 => '၂', 3 => '၃', 4 => '၄',
            5 => '၅', 6 => '၆', 7 => '၇', 8 => '၈', 9 => '၉',
        ];

        return str_replace(array_keys($myanmarNumbers), array_values($myanmarNumbers), $number);
    }

    protected function convert(Carbon $date): array
    {
        $mmCalendarResult = $this->get($date);
        if ($mmCalendarResult) {
            return $mmCalendarResult->toArray();
        }
        return [];
    }
}
