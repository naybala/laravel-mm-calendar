<?php
namespace Naybala\MMCalendar;

use ArrayAccess;

class MMCalendarResult implements ArrayAccess
{
    protected static array $mmMonths = [
        1 => 'တန်ခူး', 2   => 'ကဆုန်', 3     => 'နယုန်', 4       => 'ဝါဆို',
        5 => 'ဝါခေါင်', 6  => 'တော်သလင်း', 7 => 'သီတင်းကျွတ်', 8 => 'တန်ဆောင်မုန်း',
        9 => 'နတ်တော်', 10 => 'ပြာသို', 11   => 'တပို့တွဲ', 12   => 'တပေါင်း',
    ];

    protected static array $mmMonthsEnglish = [
        1 => 'Tagu', 2    => 'Kason', 3     => 'Nayon', 4      => 'Waso',
        5 => 'Wagaung', 6 => 'Tawthalin', 7 => 'Thadingyut', 8 => 'Tazaungmon',
        9 => 'Nadaw', 10  => 'Pyatho', 11   => 'Tabodwe', 12   => 'Tabaung',
    ];

    protected static array $mmNumerals = ['၀', '၁', '၂', '၃', '၄', '၅', '၆', '၇', '၈', '၉'];

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function __construct(protected readonly array $data)
    {}

    // -------------------------------------------------------------------------
    // Raw field getters
    // -------------------------------------------------------------------------

    /** Myanmar Era year, e.g. 1388 */
    public function mmYear(): int
    {
        return $this->data['mm_year'];
    }

    /** Myanmar month number (1–12), e.g. 3 */
    public function mmMonth(): int
    {
        return $this->data['mm_month'];
    }

    /** Day of the Myanmar month, e.g. 7 */
    public function mmDay(): int
    {
        return $this->data['mm_day'];
    }

    /** Sequential Myanmar calendar day index */
    public function mmIndex(): int
    {
        return $this->data['mm_index'];
    }

    // -------------------------------------------------------------------------
    // Month name helpers
    // -------------------------------------------------------------------------

    /**
     * Myanmar (Burmese) month name.
     *
     *   ->toMm()   // "နယုန်"
     */
    public function toMm(): string
    {
        return static::$mmMonths[$this->data['mm_month']] ?? '';
    }

    /**
     * English transliteration of the Myanmar month name.
     *
     *   ->toEn()   // "Nayon"
     */
    public function toEn(): string
    {
        return static::$mmMonthsEnglish[$this->data['mm_month']] ?? '';
    }

    /**
     * Month name by locale key.
     *
     *   ->monthName()       // "နယုန်"  (default: 'mm')
     *   ->monthName('en')   // "Nayon"
     */
    public function monthName(string $locale = 'mm'): string
    {
        return $locale === 'en' ? $this->toEn() : $this->toMm();
    }

    // -------------------------------------------------------------------------
    // Formatting
    // -------------------------------------------------------------------------

    /**
     * Format the date using simple tokens.
     *
     * Available tokens:
     *   {year}      → mm_year as integer      (e.g. 1388)
     *   {month}     → mm_month as integer     (e.g. 3)
     *   {day}       → mm_day as integer       (e.g. 7)
     *   {month_mm}  → Myanmar month name      (e.g. နယုန်)
     *   {month_en}  → English month name      (e.g. Nayon)
     *   {year_mm}   → year in Myanmar numerals (e.g. ၁၃၈၈)
     *   {month_mm_num} → month in Myanmar numerals (e.g. ၃)
     *   {day_mm}    → day in Myanmar numerals  (e.g. ၇)
     *
     * Examples:
     *   ->format('{year} ခုနှစ်၊ {month_mm}လ {day} ရက်')
     *   // => "1388 ခုနှစ်၊ နယုန်လ 7 ရက်"
     *
     *   ->format('{year_mm} ခုနှစ်၊ {month_mm}လ {day_mm} ရက်')
     *   // => "၁၃၈၈ ခုနှစ်၊ နယုန်လ ၇ ရက်"
     */
    public function format(string $pattern): string
    {
        return str_replace(
            ['{year}', '{month}', '{day}', '{month_mm}', '{month_en}', '{year_mm}', '{month_mm_num}', '{day_mm}'],
            [
                $this->data['mm_year'],
                $this->data['mm_month'],
                $this->data['mm_day'],
                $this->toMm(),
                $this->toEn(),
                $this->toMmNumerals($this->data['mm_year']),
                $this->toMmNumerals($this->data['mm_month']),
                $this->toMmNumerals($this->data['mm_day']),
            ],
            $pattern
        );
    }

    /**
     * Full Myanmar date label (Myanmar script).
     *
     *   ->label()
     *   // => "၁၃၈၈ ခုနှစ်၊ နယုန်လ ၇ ရက်"
     */
    public function label(): string
    {
        $phase   = $this->isWaxing() ? 'လဆန်း' : 'လဆုတ်';
        $display = $this->displayDay();
        return "{$this->toMmNumerals($this->data['mm_year'])} ခုနှစ်၊ {$this->toMm()} {$phase} ({$this->toMmNumerals($display)}) ရက်";
    }

    /**
     * Full English label.
     *
     *   ->labelEn()
     *   // => "7 Nayon 1388"
     */
    public function labelEn(): string
    {
        $phase   = $this->isWaxing() ? 'Waxing' : 'Waning';
        $display = $this->displayDay();
        return "{$display} {$this->toEn()} {$this->data['mm_year']} ({$phase})";
    }

    // -------------------------------------------------------------------------
    // Waxing / Waning helpers
    // -------------------------------------------------------------------------

    /** True when day is 1–15 (Waxing) */
    public function isWaxing(): bool
    {
        return ($this->data['mm_day'] ?? 0) <= 15;
    }

    /** Display day within the waxing/waning cycle (1–15) */
    public function displayDay(): int
    {
        $d = $this->data['mm_day'] ?? 0;
        return $d <= 15 ? $d : ($d - 15);
    }

    // -------------------------------------------------------------------------
    // Numeral conversion
    // -------------------------------------------------------------------------

    /**
     * Convert an Arabic integer to Myanmar numeral string.
     *
     *   ->toMmNumerals(1388)  // "၁၃၈၈"
     */
    public function toMmNumerals(int $number): string
    {
        return implode('', array_map(
            fn($d) => static::$mmNumerals[(int) $d],
            str_split((string) $number)
        ));
    }

    // -------------------------------------------------------------------------
    // Array / property access
    // -------------------------------------------------------------------------

    /**
     * Raw data array.
     *
     *   ->toArray()
     *   // => ['mm_year' => 1388, 'mm_month' => 3, 'mm_day' => 7, 'mm_index' => ...]
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /** Property-style access: $result->mm_year, $result->mm_day, etc. */
    public function __get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    // ArrayAccess — $result['mm_year'] etc. for backward compatibility.

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Immutable — no-op.
    }

    public function offsetUnset(mixed $offset): void
    {
        // Immutable — no-op.
    }
}
