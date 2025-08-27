<?php
namespace Gerald\Framework\Utils;

class DateTimeHelper
{
    /**
     * Get current datetime in GMT+8 formatted for database
     */
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get current timestamp in GMT+8
     */
    public static function timestamp(): int
    {
        return time();
    }

    /**
     * Format a timestamp to readable date
     */
    public static function format(int $timestamp, string $format = 'M d, Y H:i'): string
    {
        return date($format, $timestamp);
    }

    /**
     * Format a database datetime string
     */
    public static function formatFromDb(string $datetime, string $format = 'M d, Y H:i'): string
    {
        return date($format, strtotime($datetime));
    }

    /**
     * Get timezone info
     */
    public static function getTimezone(): string
    {
        return date_default_timezone_get();
    }

    /**
     * Convert UTC datetime to local timezone
     */
    public static function utcToLocal(string $utcDatetime, string $format = 'Y-m-d H:i:s'): string
    {
        $utc = new \DateTime($utcDatetime, new \DateTimeZone('UTC'));
        $utc->setTimezone(new \DateTimeZone('Asia/Manila'));
        return $utc->format($format);
    }
}
