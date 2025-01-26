<?php

namespace App\Service;

class RecurringBookingService
{
    public function generateDates(\DateTime $startDate, string $recurrence): array
    {
        $dates = [];
        $currentDate = clone $startDate;

        switch ($recurrence) {
            case 'daily':
                // Generate up to 5 weekdays (Monday to Friday)
                $this->generateWeekdays($currentDate, $dates, 5);
                break;

            case 'weekly':
                for ($i = 0; $i < 5; $i++) { // Generate for 5 weeks
                    $dates[] = (clone $currentDate)->modify('+' . ($i * 7) . ' days');
                }
                break;

            case 'monthly':
                for ($i = 0; $i < 5; $i++) { // Generate for 5 months
                    $dates[] = (clone $currentDate)->modify('+' . $i . ' months');
                }
                break;

            case 'yearly':
                for ($i = 0; $i < 5; $i++) { // Generate for 5 years
                    $dates[] = (clone $currentDate)->modify('+' . $i . ' years');
                }
                break;

            case 'none':
            default:
                $dates[] = $startDate; // Generate a single date
                break;
        }

        return $dates;
    }

    private function generateWeekdays(\DateTime $currentDate, array &$dates, int $count): void
    {
        $daysGenerated = 0;

        while ($daysGenerated < $count) {
            if ($this->isWeekday($currentDate)) {
                $dates[] = clone $currentDate;
                $daysGenerated++;
            }
            $currentDate->modify('+1 day');
        }
    }

    private function isWeekday(\DateTime $date): bool
    {
        $dayOfWeek = (int) $date->format('N'); // 1 (Monday) to 7 (Sunday)
        return $dayOfWeek >= 1 && $dayOfWeek <= 5; // Monday to Friday
    }
}
