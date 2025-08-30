<?php

namespace App\Services;

use Carbon\Carbon;

class TimezoneService
{
    /**
     * Country code to primary timezone mappings
     * Based on most common business timezone for each country
     */
    protected static $countryTimezones = [
        // North America
        '+1' => [
            // US/Canada - Default to Eastern (most business centers)
            'default' => 'America/New_York',
            'area_codes' => [
                // Pacific Time
                '206', '253', '360', '425', '564', // Washington
                '213', '310', '323', '424', '661', '747', '818', '858', '909', '916', '925', '949', '951', // California
                '503', '541', '971', // Oregon
                '702', '725', '775', // Nevada
                // Mountain Time
                '303', '720', '970', // Colorado
                '385', '435', '801', // Utah
                '406', // Montana
                '208', '986', // Idaho
                '307', // Wyoming
                '480', '520', '602', '623', '928', // Arizona
                '505', '575', // New Mexico
                // Central Time
                '214', '469', '972', '903', '430', '432', '409', '713', '281', '832', '346', '361', '409', '430', '432', '469', '512', '682', '713', '726', '737', '806', '817', '832', '903', '915', '936', '940', '945', '956', '972', '979', // Texas
                '312', '773', '630', '708', '847', '224', '331', '464', '618', '779', '815', '872', // Illinois
                // Add more area codes as needed...
            ],
            'area_timezone_map' => [
                // Pacific Time
                '206' => 'America/Los_Angeles', '253' => 'America/Los_Angeles', '360' => 'America/Los_Angeles',
                '213' => 'America/Los_Angeles', '310' => 'America/Los_Angeles', '323' => 'America/Los_Angeles',
                '503' => 'America/Los_Angeles', '541' => 'America/Los_Angeles',
                '702' => 'America/Los_Angeles', '775' => 'America/Los_Angeles',
                // Mountain Time
                '303' => 'America/Denver', '720' => 'America/Denver', '970' => 'America/Denver',
                '801' => 'America/Denver', '435' => 'America/Denver',
                '406' => 'America/Denver',
                // Central Time  
                '214' => 'America/Chicago', '469' => 'America/Chicago', '972' => 'America/Chicago',
                '713' => 'America/Chicago', '281' => 'America/Chicago', '832' => 'America/Chicago',
                '312' => 'America/Chicago', '773' => 'America/Chicago', '630' => 'America/Chicago',
            ]
        ],
        
        // Europe
        '+44' => 'Europe/London', // UK
        '+33' => 'Europe/Paris',  // France  
        '+49' => 'Europe/Berlin', // Germany
        '+39' => 'Europe/Rome',   // Italy
        '+34' => 'Europe/Madrid', // Spain
        '+31' => 'Europe/Amsterdam', // Netherlands
        '+41' => 'Europe/Zurich', // Switzerland
        '+43' => 'Europe/Vienna', // Austria
        '+45' => 'Europe/Copenhagen', // Denmark
        '+46' => 'Europe/Stockholm', // Sweden
        '+47' => 'Europe/Oslo', // Norway
        
        // Asia Pacific
        '+81' => 'Asia/Tokyo',    // Japan
        '+86' => 'Asia/Shanghai', // China
        '+82' => 'Asia/Seoul',    // South Korea
        '+91' => 'Asia/Kolkata',  // India
        '+61' => 'Australia/Sydney', // Australia
        '+65' => 'Asia/Singapore', // Singapore
        '+852' => 'Asia/Hong_Kong', // Hong Kong
        
        // Americas
        '+52' => 'America/Mexico_City', // Mexico
        '+55' => 'America/Sao_Paulo',   // Brazil
        '+54' => 'America/Argentina/Buenos_Aires', // Argentina
        '+57' => 'America/Bogota',      // Colombia
        
        // Default fallback
        'default' => 'America/New_York'
    ];

    /**
     * Business hours configuration by timezone
     */
    protected static $businessHours = [
        'start' => 8,  // 8 AM
        'end' => 18,   // 6 PM
        'weekend_start' => 6, // Saturday = 6, Sunday = 0
        'weekend_end' => 0,   // Sunday = 0
    ];

    /**
     * Detect timezone from phone number
     */
    public static function detectTimezoneFromPhoneNumber(string $phoneNumber): string
    {
        // Clean the phone number
        $cleaned = preg_replace('/[^\d+]/', '', $phoneNumber);
        
        // Extract country code
        if (str_starts_with($cleaned, '+1')) {
            return self::detectUSCanadaTimezone($cleaned);
        }
        
        // Try other country codes in order of length (longest first)
        $countryCodes = ['+852', '+49', '+44', '+33', '+39', '+34', '+31', '+41', '+43', '+45', '+46', '+47', '+81', '+86', '+82', '+91', '+61', '+65', '+52', '+55', '+54', '+57'];
        
        foreach ($countryCodes as $code) {
            if (str_starts_with($cleaned, $code)) {
                return self::$countryTimezones[$code] ?? self::$countryTimezones['default'];
            }
        }
        
        return self::$countryTimezones['default'];
    }

    /**
     * Detect timezone for US/Canada numbers using area codes
     */
    protected static function detectUSCanadaTimezone(string $phoneNumber): string
    {
        // Extract area code (first 3 digits after +1)
        if (preg_match('/^\+1(\d{3})/', $phoneNumber, $matches)) {
            $areaCode = $matches[1];
            
            // Check area code mapping
            if (isset(self::$countryTimezones['+1']['area_timezone_map'][$areaCode])) {
                return self::$countryTimezones['+1']['area_timezone_map'][$areaCode];
            }
        }
        
        // Default to Eastern Time (most business centers)
        return self::$countryTimezones['+1']['default'];
    }

    /**
     * Check if current time is business hours in given timezone
     */
    public static function isBusinessHours(string $timezone): bool
    {
        $now = Carbon::now($timezone);
        $hour = $now->hour;
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday
        
        // Check if it's weekend
        if ($dayOfWeek === 0 || $dayOfWeek === 6) {
            return false;
        }
        
        // Check business hours (8 AM - 6 PM)
        return $hour >= self::$businessHours['start'] && $hour < self::$businessHours['end'];
    }

    /**
     * Get next business hour in given timezone
     */
    public static function getNextBusinessHour(string $timezone): Carbon
    {
        $now = Carbon::now($timezone);
        $nextBusinessHour = $now->copy();
        
        // If it's weekend, go to next Monday 8 AM
        if ($now->isWeekend()) {
            $nextBusinessHour = $now->next(Carbon::MONDAY)->hour(self::$businessHours['start'])->minute(0)->second(0);
        }
        // If it's after business hours on weekday, go to next day 8 AM
        elseif ($now->hour >= self::$businessHours['end']) {
            $nextBusinessHour = $now->addDay()->hour(self::$businessHours['start'])->minute(0)->second(0);
            // If next day is weekend, go to Monday
            if ($nextBusinessHour->isWeekend()) {
                $nextBusinessHour = $nextBusinessHour->next(Carbon::MONDAY)->hour(self::$businessHours['start'])->minute(0)->second(0);
            }
        }
        // If it's before business hours on weekday, go to today 8 AM
        elseif ($now->hour < self::$businessHours['start']) {
            $nextBusinessHour = $now->hour(self::$businessHours['start'])->minute(0)->second(0);
        }
        // Already in business hours, return current time
        else {
            return $now;
        }
        
        return $nextBusinessHour;
    }

    /**
     * Calculate delay until next business hour
     */
    public static function getDelayUntilBusinessHours(string $timezone): int
    {
        $now = Carbon::now($timezone);
        $nextBusinessHour = self::getNextBusinessHour($timezone);
        
        // If already in business hours, return 0
        if (self::isBusinessHours($timezone)) {
            return 0;
        }
        
        return $nextBusinessHour->diffInSeconds($now);
    }

    /**
     * Get business hours info for timezone
     */
    public static function getBusinessHoursInfo(string $timezone): array
    {
        $now = Carbon::now($timezone);
        
        return [
            'timezone' => $timezone,
            'current_time' => $now->format('Y-m-d H:i:s T'),
            'is_business_hours' => self::isBusinessHours($timezone),
            'is_weekend' => $now->isWeekend(),
            'next_business_hour' => self::getNextBusinessHour($timezone)->format('Y-m-d H:i:s T'),
            'delay_until_business' => self::getDelayUntilBusinessHours($timezone),
        ];
    }
}
