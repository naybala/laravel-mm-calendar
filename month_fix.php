<?php
$file = 'resources/calendars/2028_calendar.json';
$data = json_decode(file_get_contents($file), true);

$start_date = '2028-08-01';
$end_date = '2028-12-31';

$current_date = new DateTime($start_date);
$end = new DateTime($end_date);

while ($current_date <= $end) {
    $date_str = $current_date->format('Y-m-d');
    if (isset($data[$date_str])) {
        // Increment the month by 1
        $data[$date_str]['mm_month'] += 1;
        
        // Note: We don't need to check for > 12 here because the max month 
        // in this range is currently 9, so it will become 10.
    }
    $current_date->modify('+1 day');
}

file_put_contents($file, preg_replace("/^(  +?)\\\1(?=[^ ])/m", "$1", json_encode($data, JSON_PRETTY_PRINT)));
echo "Done.\n";
