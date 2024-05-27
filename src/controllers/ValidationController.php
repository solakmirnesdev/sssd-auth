<?php

namespace Solakmirnes\SssdAuth\Controllers;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

/**
 * ValidationController class for managing validation-related actions.
 */
class ValidationController {

    /**
     * Check if a password has been pwned using the Have I Been Pwned API.
     *
     * This method sends the first 5 characters of the SHA-1 hash of the password
     * to the Have I Been Pwned API and checks if the password has been found in a data breach.
     *
     * @param string $password The password to check.
     * @return bool True if the password has been pwned, false otherwise.
     */
    public static function isPasswordPwned($password) {
        $sha1Password = sha1($password);
        $prefix = substr($sha1Password, 0, 5);
        $suffix = substr($sha1Password, 5);

        $url = "https://api.pwnedpasswords.com/range/$prefix";
        $response = file_get_contents($url);

        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            list($hashSuffix, $count) = explode(":", $line);
            if (strcasecmp($hashSuffix, $suffix) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate a phone number using the Google libphonenumber library.
     *
     * This method validates the format of the phone number to ensure it is a valid mobile number.
     *
     * @param string $phoneNumber The phone number to validate.
     * @return bool True if the phone number is valid, false otherwise.
     */
    public static function isValidPhoneNumber($phoneNumber) {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($phoneNumber, "BA"); // For Bosnia
            return $phoneUtil->isValidNumber($numberProto);
        } catch (NumberParseException $e) {
            return false;
        }
    }

    /**
     * Check if the email domain extension is valid.
     *
     * @param string $email The email address to check.
     * @return bool True if the domain extension is valid, false otherwise.
     */
    public static function isValidDomainExtension($email) {
        $domain = explode('@', $email)[1];
        $extension = explode('.', $domain);
        $tld = array_pop($extension);

        $tldList = file('https://data.iana.org/TLD/tlds-alpha-by-domain.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return in_array(strtoupper($tld), $tldList);
    }

    /**
     * Check if the email domain has valid MX records.
     *
     * @param string $email The email address to check.
     * @return bool True if the domain has valid MX records, false otherwise.
     */
    public static function hasValidMXRecords($email) {
        $domain = explode('@', $email)[1];
        return checkdnsrr($domain, 'MX');
    }

    /**
     * Verify the hCaptcha response token.
     *
     * @param string $token The hCaptcha response token.
     * @return bool True if the token is valid, false otherwise.
     */
    public static function verifyCaptcha($token) {
        $url = 'https://hcaptcha.com/siteverify';
        $data = [
            'secret' => HCAPTCHA_SERVER_SECRET,
            'response' => $token
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response);

        return $result && $result->success;
    }
}
