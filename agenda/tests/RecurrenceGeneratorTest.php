<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../application/libraries/Recurrence_generator.php';

class RecurrenceGeneratorTest extends TestCase
{
    public function testDailyRecurrence(): void
    {
        $rule = [
            'recurrence_type' => 'daily',
            'separation_count' => 2,
            'end_date' => '2025-01-05',
        ];
        $dates = (new \Recurrence_generator())->generate_dates($rule, '2025-01-01 00:00:00');
        $this->assertCount(3, $dates);
        $this->assertEquals('2025-01-01 00:00:00', $dates[0]->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-01-03 00:00:00', $dates[1]->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-01-05 00:00:00', $dates[2]->format('Y-m-d H:i:s'));
    }

    public function testWeeklyRecurrence(): void
    {
        $rule = [
            'recurrence_type' => 'weekly',
            'separation_count' => 1,
            'days_of_week' => 'mon,wed',
            'end_date' => '2025-01-15',
        ];
        $dates = (new \Recurrence_generator())->generate_dates($rule, '2025-01-06 10:00:00');
        // Expect 4 occurrences: 2025-01-06,08,13,15
        $this->assertCount(4, $dates);
        $this->assertEquals('2025-01-06 10:00:00', $dates[0]->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-01-08 10:00:00', $dates[1]->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-01-13 10:00:00', $dates[2]->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-01-15 10:00:00', $dates[3]->format('Y-m-d H:i:s'));
    }

    public function testMonthlyRecurrence(): void
    {
        $rule = [
            'recurrence_type' => 'monthly',
            'separation_count' => 1,
            'end_date' => '2025-04-01',
        ];
        $dates = (new \Recurrence_generator())->generate_dates($rule, '2025-01-01 09:30:00');
        $this->assertCount(4, $dates);
        $this->assertEquals('2025-01-01 09:30:00', $dates[0]->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-02-01 09:30:00', $dates[1]->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-03-01 09:30:00', $dates[2]->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-04-01 09:30:00', $dates[3]->format('Y-m-d H:i:s'));
    }
}