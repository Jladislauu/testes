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
     * @param array $rule The recurrence rule from the database.
     * @param string $start_datetime_string The start datetime of the first event in 'Y-m-d H:i:s' format.
     * @return DateTime[] Returns an array of DateTime objects for each occurrence.
     * @throws Exception
     */
    public function generate_dates(array $rule, string $start_datetime_string): array
    {
        $occurrences = [];
        $current_date = new DateTime($start_datetime_string);
        $end_date = isset($rule['end_date']) ? new DateTime($rule['end_date']) : null;
        $max_occurrences = $rule['max_occurrences'] ?? null;
        $interval = (int)($rule['separation_count'] ?? 1);

        // The first occurrence is the start date itself.
        $occurrences[] = clone $current_date;

        while (true) {
            // Move to the next potential date
            switch ($rule['recurrence_type']) {
                case 'daily':
                    $current_date->add(new DateInterval("P{$interval}D"));
                    break;
                case 'weekly':
                    // Default to the start date's day of the week if not provided.
                    if (empty($rule['days_of_week'])) {
                        $current_date->add(new DateInterval("P{$interval}W"));
                    } else {
                        // This is a simplified logic for finding the next day.
                        // A full implementation would require more complex date calculations.
                        // For now, we advance by the interval of weeks and assume the day is correct.
                        // This part might need refinement for complex multi-day weekly recurrences.
                        log_message('debug', 'Processing complex weekly recurrence (not fully implemented).');
                        $current_date->add(new DateInterval("P{$interval}W"));
                    }
                    break;
                case 'monthly':
                    $current_date->add(new DateInterval("P{$interval}M"));
                    break;
                default:
                    // Invalid type, stop generating
                    return $occurrences;
            }

            // Check stop conditions
            if ($end_date && $current_date > $end_date) {
                break; // Stop if we passed the end date
            }

            if ($max_occurrences && count($occurrences) >= $max_occurrences) {
                break; // Stop if we reached the max number of occurrences
            }

            $occurrences[] = clone $current_date;

            // Safety break to prevent infinite loops in case of misconfiguration
            if (count($occurrences) > 500) {
                break;
            }
        }

        return $occurrences;
    }
}