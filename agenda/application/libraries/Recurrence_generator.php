<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Recurrence Generator Library
 *
 * Generates recurrence dates based on a defined rule.
 *
 * @package Libraries
 */
class Recurrence_generator {

    /**
     * Generates an array of DateTime objects based on a recurrence rule.
     *
     * The $rule array supports the following keys:
     * - 'recurrence_type': 'daily', 'weekly', or 'monthly'.
     * - 'separation_count': integer interval count between occurrences (default 1).
     * - 'days_of_week': comma-separated list of weekdays ('mon','tue',...) for weekly recurrences.
     * - 'end_date': string 'Y-m-d' specifying the last possible occurrence date.
     * - 'max_occurrences': integer limiting total occurrences.
     *
     * @param array $rule The recurrence rule from the database.
     * @param string $start_datetime_string The start datetime of the first event in 'Y-m-d H:i:s' format.
     * @return DateTime[] Returns an array of DateTime objects for each occurrence.
     * @throws Exception on invalid recurrence type.
     */
    public function generate_dates(array $rule, string $start_datetime_string): array
    {
        $occurrences = array();
        $startDate = new DateTime($start_datetime_string);
        $time = $startDate->format('H:i:s');
        $endDate = (!empty($rule['end_date'])) ? new DateTime($rule['end_date']) : null;
        $maxOccurrences = isset($rule['max_occurrences']) ? (int)$rule['max_occurrences'] : null;
        $intervalCount = isset($rule['separation_count']) ? (int)$rule['separation_count'] : 1;

        // Add first occurrence
        $occurrences[] = clone $startDate;

        switch ($rule['recurrence_type']) {
            case 'daily':
                $current = clone $startDate;
                while (true) {
                    $current->add(new DateInterval("P{$intervalCount}D"));
                    if ($endDate && $current > $endDate) {
                        break;
                    }
                    if ($maxOccurrences !== null && count($occurrences) >= $maxOccurrences) {
                        break;
                    }
                    $occurrences[] = clone $current;
                    if (count($occurrences) > 500) {
                        break;
                    }
                }
                break;

            case 'weekly':
                $daysOfWeek = !empty($rule['days_of_week']) ? $rule['days_of_week'] : null;
                if ($daysOfWeek) {
                    $days = array_map('trim', explode(',', $daysOfWeek));
                    $dayMap = array('mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7);
                    $weekDays = array();
                    foreach ($days as $d) {
                        $key = strtolower(substr($d, 0, 3));
                        if (isset($dayMap[$key])) {
                            $weekDays[] = $dayMap[$key];
                        }
                    }
                    sort($weekDays);
                } else {
                    $weekDays = array((int)$startDate->format('N'));
                }
                $weekCount = 0;
                while (true) {
                    if ($endDate) {
                        $year = (int)$startDate->format('o');
                        $startWeek = (int)$startDate->format('W');
                        $week = $startWeek + ($weekCount * $intervalCount);
                        $checkDate = new DateTime();
                        try {
                            $checkDate->setISODate($year, $week, $weekDays[0]);
                        } catch (Exception $e) {
                            break;
                        }
                        list($fh, $fi, $fs) = explode(':', $time);
                        $checkDate->setTime((int)$fh, (int)$fi, (int)$fs);
                        if ($checkDate > $endDate) {
                            break;
                        }
                    }
                    foreach ($weekDays as $dow) {
                        $year = (int)$startDate->format('o');
                        $startWeek = (int)$startDate->format('W');
                        $week = $startWeek + ($weekCount * $intervalCount);
                        $date = new DateTime();
                        try {
                            $date->setISODate($year, $week, $dow);
                        } catch (Exception $e) {
                            continue;
                        }
                        list($h, $i, $s) = explode(':', $time);
                        $date->setTime((int)$h, (int)$i, (int)$s);
                        if ($date <= $startDate) {
                            continue;
                        }
                        if ($endDate && $date > $endDate) {
                            continue;
                        }
                        if ($maxOccurrences !== null && count($occurrences) >= $maxOccurrences) {
                            break 2;
                        }
                        $occurrences[] = clone $date;
                        if (count($occurrences) > 500) {
                            break 2;
                        }
                    }
                    $weekCount++;
                }
                break;

            case 'monthly':
                $current = clone $startDate;
                while (true) {
                    $current->add(new DateInterval("P{$intervalCount}M"));
                    list($h, $i, $s) = explode(':', $time);
                    $current->setTime((int)$h, (int)$i, (int)$s);
                    if ($endDate && $current > $endDate) {
                        break;
                    }
                    if ($maxOccurrences !== null && count($occurrences) >= $maxOccurrences) {
                        break;
                    }
                    $occurrences[] = clone $current;
                    if (count($occurrences) > 500) {
                        break;
                    }
                }
                break;

            default:
                throw new Exception("Invalid recurrence type: {$rule['recurrence_type']}");
        }

        return $occurrences;
    }
}