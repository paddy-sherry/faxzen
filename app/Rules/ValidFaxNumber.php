<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidFaxNumber implements ValidationRule
{
    protected $countryCode;

    public function __construct($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove any non-numeric characters for validation
        $cleanNumber = preg_replace('/[^0-9]/', '', $value);
        
        // Basic checks
        if (empty($cleanNumber)) {
            $fail('The fax number is required.');
            return;
        }

        // Check for minimum length (most countries require at least 7 digits)
        if (strlen($cleanNumber) < 7) {
            $fail('The fax number must be at least 7 digits long.');
            return;
        }

        // Check for maximum length (E.164 format allows max 15 digits total)
        if (strlen($cleanNumber) > 15) {
            $fail('The fax number is too long (maximum 15 digits).');
            return;
        }

        // Country-specific validation
        if (!$this->validateForCountry($cleanNumber)) {
            $fail($this->getCountrySpecificError($cleanNumber));
            return;
        }

        // Check for obviously invalid patterns
        if ($this->hasInvalidPatterns($cleanNumber)) {
            $fail('The fax number contains an invalid pattern.');
            return;
        }
    }

    /**
     * Validate number format for specific countries
     */
    protected function validateForCountry(string $number): bool
    {
        switch ($this->countryCode) {
            case '+1': // US/Canada
                return $this->validateNorthAmerica($number);
            case '+44': // UK
                return $this->validateUK($number);
            case '+33': // France
                return $this->validateFrance($number);
            case '+49': // Germany
                return $this->validateGermany($number);
            case '+39': // Italy
                return $this->validateItaly($number);
            case '+34': // Spain
                return $this->validateSpain($number);
            case '+61': // Australia
                return $this->validateAustralia($number);
            default:
                // For other countries, use general validation
                return $this->validateGeneral($number);
        }
    }

    /**
     * US/Canada validation (NANP - North American Numbering Plan)
     */
    protected function validateNorthAmerica(string $number): bool
    {
        // NANP format: NXX-NXX-XXXX (where N = 2-9, X = 0-9)
        if (strlen($number) !== 10) {
            return false;
        }

        // First digit of area code cannot be 0 or 1
        if (in_array($number[0], ['0', '1'])) {
            return false;
        }

        // First digit of exchange code cannot be 0 or 1
        if (in_array($number[3], ['0', '1'])) {
            return false;
        }

        // Check for special service codes (e.g., 911, 411)
        $areaCode = substr($number, 0, 3);
        $exchange = substr($number, 3, 3);
        
        // Invalid area codes (only block truly invalid codes, not toll-free numbers)
        // Toll-free numbers (800, 888, 877, 866, 855, 844, 833) are valid for fax
        $invalidAreaCodes = ['000', '911']; // Only block truly invalid codes
        if (in_array($areaCode, $invalidAreaCodes)) {
            return false;
        }

        return true;
    }

    /**
     * UK validation
     */
    protected function validateUK(string $number): bool
    {
        // UK numbers are typically 10-11 digits after country code
        $length = strlen($number);
        return $length >= 10 && $length <= 11;
    }

    /**
     * France validation
     */
    protected function validateFrance(string $number): bool
    {
        // French numbers are 9 digits after country code
        return strlen($number) === 9;
    }

    /**
     * Germany validation
     */
    protected function validateGermany(string $number): bool
    {
        // German numbers are 10-12 digits after country code
        $length = strlen($number);
        return $length >= 10 && $length <= 12;
    }

    /**
     * Italy validation
     */
    protected function validateItaly(string $number): bool
    {
        // Italian numbers are 9-10 digits after country code
        $length = strlen($number);
        return $length >= 9 && $length <= 10;
    }

    /**
     * Spain validation
     */
    protected function validateSpain(string $number): bool
    {
        // Spanish numbers are 9 digits after country code
        return strlen($number) === 9;
    }

    /**
     * Australia validation
     */
    protected function validateAustralia(string $number): bool
    {
        // Australian numbers are 9 digits after country code
        return strlen($number) === 9;
    }

    /**
     * General validation for other countries
     */
    protected function validateGeneral(string $number): bool
    {
        // Most international numbers are 7-12 digits
        $length = strlen($number);
        return $length >= 7 && $length <= 12;
    }

    /**
     * Check for invalid patterns
     */
    protected function hasInvalidPatterns(string $number): bool
    {
        // All same digits (e.g., 1111111111)
        if (preg_match('/^(\d)\1+$/', $number)) {
            return true;
        }

        // Sequential digits (e.g., 1234567890)
        if (preg_match('/^0123456789|1234567890|9876543210|0987654321$/', $number)) {
            return true;
        }

        // Common test numbers (be less restrictive for fax numbers)
        $testNumbers = ['0000000000', '1111111111', '2222222222', '3333333333', '4444444444', '6666666666', '7777777777', '8888888888', '9999999999'];
        if (in_array($number, $testNumbers)) {
            return true;
        }

        return false;
    }

    /**
     * Get country-specific error message
     */
    protected function getCountrySpecificError(string $number): string
    {
        switch ($this->countryCode) {
            case '+1':
                return 'Please enter a valid US/Canada fax number (10 digits, area code cannot start with 0 or 1).';
            case '+44':
                return 'Please enter a valid UK fax number (10-11 digits).';
            case '+33':
                return 'Please enter a valid French fax number (9 digits).';
            case '+49':
                return 'Please enter a valid German fax number (10-12 digits).';
            case '+39':
                return 'Please enter a valid Italian fax number (9-10 digits).';
            case '+34':
                return 'Please enter a valid Spanish fax number (9 digits).';
            case '+61':
                return 'Please enter a valid Australian fax number (9 digits).';
            default:
                return 'Please enter a valid fax number for the selected country.';
        }
    }
}
