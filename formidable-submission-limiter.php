<?php

/**
 * Plugin Name: Formidable Submission Limiter
 * Description: Limits the rate of submissions on Formidable Forms.
 * Version: 1.0.0
 * Author: Anthony M. Darter
 * Author URI: 
 */

if (!defined('ABSPATH')) {
    exit;
}

class FormidableSubmissionLimiter
{
    public $rateLimit = 2;
    public $timeLimit = 60;
    public $limitMessage = 'Sorry, we are receiving a high volume at this time. Please try again in a minute or reach out to us by phone.';
    public $dataSize = 524288; // 0.5 MB in bytes
    public $transientExpiry = 3600; // 1 hour
    public $maxSubmissions = 100;

    function __construct($settings = [])
    {
        $this->rateLimit = $settings['rateLimit'] ?? $this->rateLimit;
        $this->timeLimit = $settings['timeLimit'] ?? $this->timeLimit;
        $this->limitMessage = $settings['limitMessage'] ?? $this->limitMessage;
        $this->dataSize = $settings['dataSize'] ?? $this->dataSize;
        $this->transientExpiry = $settings['transientExpiry'] ?? $this->transientExpiry;
        $this->maxSubmissions = $settings['maxSubmissions'] ?? $this->maxSubmissions;
    }

    /**
     * Get IP Address
     * 
     * @return string|null 
     */
    function getIp(): ?string
    {
        if (isset($_SERVER)) {
            $vars = array('REMOTE_ADDR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR');
            foreach ($vars as $var) {
                if (isset($_SERVER[$var])) {
                    $ips = explode(',', $_SERVER[$var]); // handle comma separated values
                    foreach ($ips as $ip) {
                        $ip = sanitize_text_field(trim($ip));
                        if (filter_var($ip, FILTER_VALIDATE_IP)) {
                            return $ip;
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * Clean up transient data if it exceeds a size threshold.
     * 
     * @param string $submission_data
     * @return string $submission_data
     */
    function cleanup(string $submission_data)
    {
        // Calculate the size of the transient data in bytes.
        $data_size = strlen($submission_data);

        if ($data_size > $this->dataSize) {
            delete_transient('formidable_submission_data');
            $submission_data = '{}';
        }

        return $submission_data;
    }

    /**
     * Limits the rate of submissions on Formidable Forms based on IP address.
     * 
     * @param array $errors
     * @param array $values
     * @return array $errors
     */
    function limit($errors, $values)
    {
        $current_time = time();
        $ip_address = $this->getIp();

        // Proceed only if a valid IP address is found
        if ($ip_address && filter_var($ip_address, FILTER_VALIDATE_IP)) {
            $transient = get_transient('formidable_submission_data') ?: '{}';
            if (!is_string($transient)) {
                $transient = '{}';
            }
            $submission_data =  json_decode($this->cleanup($transient), true);

            if (!is_array($submission_data)) {
                $submission_data = [];
            }

            // Check if the total submissions for this IP have exceeded the limit
            if (isset($submission_data[$ip_address]) && $submission_data[$ip_address]['total'] >= $this->maxSubmissions) {
                $errors['submission_limit_exceeded'] = 'You have reached the maximum number of submissions allowed.';
                return $errors;
            }

            if (!isset($submission_data[$ip_address])) {
                // Initialize data for a new IP address
                $submission_data[$ip_address] = [
                    'last' => $current_time,
                    'count' => 1,
                    'total' => 1
                ];
            } else {
                // Calculate the time difference since the last submission from this IP
                $last_submission_time = $submission_data[$ip_address]['last'] ?? 0;
                $time_diff = $current_time - $last_submission_time;

                if ($time_diff < $this->timeLimit) {
                    // Increment count if submission is within the time limit
                    $submission_data[$ip_address]['count']++;
                    if ($submission_data[$ip_address]['count'] > $this->rateLimit) {
                        $errors['too_fast'] = $this->limitMessage;
                    }
                } else {
                    $total_submissions = $submission_data[$ip_address]['total'] ?? 0;
                    // Reset count for this IP as the submission is outside the time limit
                    $submission_data[$ip_address] = [
                        'last' => $current_time,
                        'count' => 1,
                        'total' => $total_submissions + 1
                    ];
                }
            }

            // Set the transient.
            set_transient(
                'formidable_submission_data',
                json_encode($submission_data),
                $this->transientExpiry
            );
        }

        return $errors;
    }
}

add_filter('frm_validate_entry', function ($errors, $values) {
    $limiter = new FormidableSubmissionLimiter([
        'rateLimit' => get_option('formidable_submission_rate_limit', null),
        'timeLimit' => get_option('formidable_submission_time_limit', null),
        'limitMessage' => get_option('formidable_submission_limit_message', null),
        'dataSize' => get_option('formidable_submission_data_size', null),
        'transientExpiry' => get_option('formidable_submission_transient_expiry', null),
        'maxSubmissions' => get_option('formidable_submission_max_submissions', null)
    ]);
    return $limiter->limit($errors, $values);
}, 10, 2);
