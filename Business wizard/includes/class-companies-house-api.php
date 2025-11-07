<?php
/**
 * Companies House API Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class BIZ_WIZARD_Companies_House_API {

    private static $api_key;
    private static $api_base_url = 'https://api.companieshouse.gov.uk';
    private static $timeout;

    public function __construct() {
        $api_settings = get_option('biz_wizard_api_settings', array());
        self::$api_key = isset($api_settings['biz_wizard_companies_house_api']) ? $api_settings['biz_wizard_companies_house_api'] : '';
        self::$timeout = isset($api_settings['biz_wizard_api_timeout']) ? $api_settings['biz_wizard_api_timeout'] : 15;
    }
    
    private static function request($endpoint) {
        if (empty(self::$api_key)) {
            return new WP_Error('no_api_key', __('Companies House API key not configured', 'business-wizard'));
        }

        $url = self::$api_base_url . $endpoint;
        $response = wp_remote_get($url, [
            'headers' => ['Authorization' => 'Basic ' . base64_encode(self::$api_key . ':')],
            'timeout' => self::$timeout
        ]);

        if (is_wp_error($response)) {
            if (WP_DEBUG) {
                error_log('Companies House API Error: ' . $response->get_error_message());
            }
            return $response;
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            if (WP_DEBUG) {
                error_log('Companies House API Error: ' . wp_remote_retrieve_response_message($response));
            }
            return new WP_Error('api_error', __('API request failed', 'business-wizard'));
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
    
    public function search_company($query) {
        $query = urlencode(trim($query));
        
        // Check cache
        $cache_key = 'biz_wizard_company_search_' . md5($query);
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $data = self::request("/search/companies?q={$query}");
        if (is_wp_error($data)) {
            return $data;
        }
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            if (WP_DEBUG) {
                error_log('Companies House API JSON Error: ' . json_last_error_msg());
            }
            return new WP_Error('json_error', __('Invalid response from Companies House API', 'business-wizard'));
        }
        
        if (!isset($data['items']) || !is_array($data['items'])) {
            return array();
        }
        
        $results = array();
        foreach ($data['items'] as $item) {
            $results[] = array(
                'company_number' => $item['company_number'] ?? '',
                'company_name' => $item['title'] ?? '',
                'company_type' => $item['company_type'] ?? '',
                'company_status' => $item['company_status'] ?? '',
                'address' => $this->format_address($item['address'] ?? array()),
                'date_of_creation' => $item['date_of_creation'] ?? ''
            );
        }
        
        // Cache for 24 hours
        set_transient($cache_key, $results, DAY_IN_SECONDS);
        
        return $results;
    }
    
    public function get_company_details($company_number) {
        // Check cache
        $cache_key = 'biz_wizard_company_details_' . $company_number;
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $data = self::request("/company/{$company_number}");
        if (is_wp_error($data)) {
            return $data;
        }

        // Get officers data
        $officers = self::request("/company/{$company_number}/officers");
        $director = '';
        if (!is_wp_error($officers)) {
            foreach ($officers['items'] ?? [] as $person) {
                if (($person['officer_role'] ?? '') === 'director' && !empty($person['name'])) {
                    $director = $person['name'];
                    break;
                }
            }
        }

        $addr = '';
        if (isset($data['registered_office_address'])) {
            $parts = array_filter([
                $data['registered_office_address']['address_line_1'] ?? '',
                $data['registered_office_address']['address_line_2'] ?? '',
                $data['registered_office_address']['locality'] ?? '',
                $data['registered_office_address']['postal_code'] ?? ''
            ]);
            $addr = implode(', ', $parts);
        }

        // Get accounts dates (ONLY from accounts section)
        $year_end_raw = null;
        $next_due_raw = null;

        if (!empty($data['accounts'])) {
            if (!empty($data['accounts']['last_accounts']['made_up_to'])) {
                $year_end_raw = $data['accounts']['last_accounts']['made_up_to'];
            }

            if (!empty($data['accounts']['next_accounts']['due_on'])) {
                $next_due_raw = $data['accounts']['next_accounts']['due_on'];
            } elseif (!empty($data['accounts']['next_due'])) {
                $next_due_raw = $data['accounts']['next_due'];
            } elseif (!empty($data['accounts']['next_made_up_to'])) {
                $next_due_raw = $data['accounts']['next_made_up_to'];
            }
        }

        // Helper to format ISO date → "31 March 2025"
        $formatCompanyDate = function($raw) {
            if (empty($raw)) return 'Not available';
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
                try {
                    $dt = new DateTime($raw);
                    return $dt->format('j F Y');
                } catch (Exception $e) {
                    return $raw;
                }
            }
            return $raw;
        };

        $year_end = $formatCompanyDate($year_end_raw);
        $next_due = $formatCompanyDate($next_due_raw);

        // Calculate pending tax years (Full Year dropdown)
        $pending_years = [];
        if (!empty($year_end_raw) && !empty($next_due_raw)) {
            $last_year = (int) date('Y', strtotime($year_end_raw));
            $next_year = (int) date('Y', strtotime($next_due_raw));

            // Generate all missing UK tax years from last filing to next due
            for ($y = $last_year + 1; $y <= $next_year; $y++) {
                $prev = $y - 1;
                $pending_years[] = $prev . '/' . substr($y, -2); // e.g. 2023/24
            }
        }

        $details = [
            'company_number' => $data['company_number'] ?? '',
            'company_name' => $data['company_name'] ?? '',
            'registered_address' => $addr,
            'company_type' => ucwords(str_replace('_', ' ', $data['type'] ?? '')),
            'director_name' => $director,
            'year_end' => $year_end,
            'next_filing_due' => $next_due,
            'incorporation_date' => $data['date_of_creation'] ?? '',
            'pending_tax_years' => $pending_years // for Full Year dropdown
        ];

        // Cache for 24 hours
        set_transient($cache_key, $details, DAY_IN_SECONDS);

        return $details;
    }
    
    public function get_company_officers($company_number) {
        if (empty($this->api_key)) {
            return new WP_Error('no_api_key', __('Companies House API key not configured', 'business-wizard'));
        }
        
        $url = $this->api_base_url . '/company/' . urlencode($company_number) . '/officers';
        
        $response = wp_remote_get($url, array(
            'timeout' => $this->timeout,
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->api_key . ':')
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['items'])) {
            return array();
        }
        
        $officers = array();
        foreach ($data['items'] as $officer) {
            $officers[] = array(
                'name' => $officer['name'] ?? '',
                'officer_role' => $officer['officer_role'] ?? '',
                'appointed_on' => $officer['appointed_on'] ?? '',
                'resigned_on' => $officer['resigned_on'] ?? null
            );
        }
        
        return $officers;
    }
    
    private function format_address($address) {
        if (empty($address)) {
            return '';
        }
        
        $parts = array();
        
        // Handle both address_line_1 and premises/address_line_1 format
        if (!empty($address['premises'])) {
            $parts[] = $address['premises'];
        }
        if (!empty($address['address_line_1'])) {
            $parts[] = $address['address_line_1'];
        }
        if (!empty($address['address_line_2'])) {
            $parts[] = $address['address_line_2'];
        }
        if (!empty($address['locality'])) {
            $parts[] = $address['locality'];
        }
        if (!empty($address['region'])) {
            $parts[] = $address['region'];
        }
        if (!empty($address['postal_code'])) {
            $parts[] = $address['postal_code'];
        }
        if (!empty($address['country'])) {
            $parts[] = $address['country'];
        }
        
        $parts = array_filter($parts); // Remove empty elements
        return implode(', ', $parts);
    }
}
