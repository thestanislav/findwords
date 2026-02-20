<?php

namespace ExprAs\Core\Stdlib;

class DateTimeUtils
{
    public static function getTimezoneOffset(?string $timezoneName = null): int
    {
        $timezone = new \DateTimeZone($timezoneName ?? date_default_timezone_get());
        // Get the current time in the specified timezone
        $dateTime = new \DateTime('now', $timezone);
        // Get the offset in seconds (from UTC) using the format 'P'
        $offset = $dateTime->format('P');

        // Convert the offset string to seconds (e.g., '+02:00' -> 7200 seconds)
        sscanf($offset, "%+d:%d", $hours, $minutes);
        return $hours * 3600 + $minutes * 60;
    }


    public static function getTimezoneOffsetFormatted(?string $timezoneName = null): string
    {
        $timezone = new \DateTimeZone($timezoneName ?? date_default_timezone_get());
        // Get the current time in the specified timezone
        $dateTime = new \DateTime('now', $timezone);
        // Get the offset in the format '+HH:MM'
        return $dateTime->format('P');
    }

    public static function calculateTimezoneOffset(string $timezoneName1, string $timezoneName2): int
    {
        // Create DateTimeZone objects for the two timezones
        $timezone1 = new \DateTimeZone($timezoneName1);
        $timezone2 = new \DateTimeZone($timezoneName2);

        // Create a DateTime object (defaults to "now")
        $date = new \DateTime('now');

        // Get the offset in seconds from UTC for both timezones
        $offset1 = $timezone1->getOffset($date);
        $offset2 = $timezone2->getOffset($date);

        // Calculate the difference in seconds
        $offsetDifference = $offset1 - $offset2;

        // Convert the difference to hours and minutes
        $hours = intdiv($offsetDifference, 3600);
        $minutes = abs($offsetDifference % 3600 / 60);

        // Format the offset string
        return $hours * 3600 + $minutes * 60;
    }

    public static function calculateTimezoneOffsetFormatted(string $timezoneName1, string $timezoneName2): string
    {
        $offset = self::calculateTimezoneOffset($timezoneName1, $timezoneName2);
        $hours = intdiv($offset, 3600);
        $minutes = abs($offset % 3600 / 60);

        // Format the offset string
        return sprintf('%+03d:%02d', $hours, $minutes);
    }
}