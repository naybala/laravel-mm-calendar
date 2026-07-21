<?php
$file = 'resources/calendars/2028_calendar.json';
$old_data = json_decode(file_get_contents($file), true);
$new_data = [];

$start_date = '2028-01-01';
$end_date = '2028-12-31';
$shift_start = '2028-03-01';

$current_date = new DateTime($start_date);
$end = new DateTime($end_date);

while ($current_date <= $end) {
    $date_str = $current_date->format('Y-m-d');
    
    if ($date_str < $shift_start) {
        $new_data[$date_str] = $old_data[$date_str];
    } elseif ($date_str === '2028-12-31') {
        $new_data[$date_str] = [
            'mm_year' => 1390,
            'mm_month' => 9,
            'mm_day' => 16,
            'mm_index' => 16689
        ];
    } else {
        $next_date_str = (clone $current_date)->modify('+1 day')->format('Y-m-d');
        $new_data[$date_str] = $old_data[$next_date_str];
    }
    
    $current_date->modify('+1 day');
}

file_put_contents($file, preg_replace("/^(  +?)\\\1(?=[^ ])/m", "$1", json_encode($new_data, JSON_PRETTY_PRINT)));
echo "Done.\n";
