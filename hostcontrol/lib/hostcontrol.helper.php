<?php

class HostControlHelperError extends Exception {}

class HostControlHelper
{
    const HOSTCONTROL_TEST_API_URL = "https://resello.bo.hostcontrol-ote.com/api/v1";
    const HOSTCONTROL_PRODUCTION_API_URL = "https://backoffice.hostcontrol.com/api/v1";
    const HOSTCONTROL_PRODUCTION_ALTERNATIVE_API_URL = "https://backoffice.hostcontrol.com:14739/api/v1";

    const TABLE_NAME = 'mod_hostcontrol_domain';
    const TYPE_HOSTCONTROL_CUSTOMER = 'customer';
    const TYPE_HOSTCONTROL_TRANSFER = 'transfer';

    public static $production = true;
    public static $ignored_dns_types = array("NS", "SOA", "URL", "FRAME", "MXE");
    public static $tld_with_lock_support = array("com", "net", "org", "info", "biz", "name", "cc", "me", "tv", "us");

    public static $status_info = array(
        'wait_for_approval'  => 'Waiting for your approval',
        'wait_for_auth_code' => 'Waiting for auth-code / EPPCode',
        'scheduled'          => 'Scheduled to be transferred',
        'pending'            => 'Pending',
        'completed'          => 'Completed',
        'failed'             => 'Failed',
        'canceled'           => 'Canceled'
    );

    public static $country_codes = array(
        'US' => '1',
        'CA' => '1',
        'EG' => '20',
        'MA' => '212',
        'EH' => '212',
        'DZ' => '213',
        'TN' => '216',
        'LY' => '218',
        'GM' => '220',
        'SN' => '221',
        'MR' => '222',
        'ML' => '223',
        'GN' => '224',
        'CI' => '225',
        'BF' => '226',
        'NE' => '227',
        'TG' => '228',
        'BJ' => '229',
        'MU' => '230',
        'LR' => '231',
        'SL' => '232',
        'GH' => '233',
        'NG' => '234',
        'TD' => '235',
        'CF' => '236',
        'CM' => '237',
        'CV' => '238',
        'ST' => '239',
        'GQ' => '240',
        'GA' => '241',
        'CG' => '242',
        'CD' => '243',
        'AO' => '244',
        'GW' => '245',
        'IO' => '246',
        'SC' => '248',
        'SD' => '249',
        'RW' => '250',
        'ET' => '251',
        'SO' => '252',
        'DJ' => '253',
        'KE' => '254',
        'TZ' => '255',
        'UG' => '256',
        'BI' => '257',
        'MZ' => '258',
        'ZM' => '260',
        'MG' => '261',
        'RE' => '262',
        'YT' => '262',
        'ZW' => '263',
        'NA' => '264',
        'MW' => '265',
        'LS' => '266',
        'BW' => '267',
        'SZ' => '268',
        'KM' => '269',
        'ZA' => '27',
        'SH' => '290',
        'ER' => '291',
        'AW' => '297',
        'FO' => '298',
        'GL' => '299',
        'GR' => '30',
        'NL' => '31',
        'BE' => '32',
        'FR' => '33',
        'ES' => '34',
        'GI' => '350',
        'PT' => '351',
        'LU' => '352',
        'IE' => '353',
        'IS' => '354',
        'AL' => '355',
        'MT' => '356',
        'CY' => '357',
        'FI' => '358',
        'AX' => '358',
        'BG' => '359',
        'HU' => '36',
        'LT' => '370',
        'LV' => '371',
        'EE' => '372',
        'MD' => '373',
        'AM' => '374',
        'BY' => '375',
        'AD' => '376',
        'MC' => '377',
        'SM' => '378',
        'UA' => '380',
        'RS' => '381',
        'ME' => '382',
        'HR' => '385',
        'SI' => '386',
        'BA' => '387',
        'MK' => '389',
        'IT' => '39',
        'VA' => '39',
        'RO' => '40',
        'CH' => '41',
        'CZ' => '420',
        'SK' => '421',
        'LI' => '423',
        'AT' => '43',
        'GB' => '44',
        'GG' => '44',
        'IM' => '44',
        'JE' => '44',
        'UK' => '44',
        'DK' => '45',
        'SE' => '46',
        'NO' => '47',
        'SJ' => '47',
        'PL' => '48',
        'DE' => '49',
        'FK' => '500',
        'BZ' => '501',
        'GT' => '502',
        'SV' => '503',
        'HN' => '504',
        'NI' => '505',
        'CR' => '506',
        'PA' => '507',
        'PM' => '508',
        'HT' => '509',
        'PE' => '51',
        'MX' => '52',
        'BB' => '52',
        'CU' => '53',
        'AR' => '54',
        'BR' => '55',
        'CL' => '56',
        'CO' => '57',
        'VE' => '58',
        'GP' => '590',
        'BL' => '590',
        'MF' => '590',
        'BO' => '591',
        'GY' => '592',
        'EC' => '593',
        'GF' => '594',
        'PY' => '595',
        'MQ' => '596',
        'SR' => '597',
        'UY' => '598',
        'MY' => '60',
        'AU' => '61',
        'CX' => '61',
        'CC' => '61',
        'ID' => '62',
        'PH' => '63',
        'NZ' => '64',
        'SG' => '65',
        'TH' => '66',
        'TL' => '670',
        'NF' => '672',
        'AQ' => '672',
        'BN' => '673',
        'NR' => '674',
        'PG' => '675',
        'TO' => '676',
        'SB' => '677',
        'VU' => '678',
        'FJ' => '679',
        'PW' => '680',
        'WF' => '681',
        'CK' => '682',
        'NU' => '683',
        'WS' => '685',
        'KI' => '686',
        'NC' => '687',
        'TV' => '688',
        'PF' => '689',
        'TK' => '690',
        'FM' => '691',
        'MH' => '692',
        'RU' => '7',
        'KZ' => '7',
        'JP' => '81',
        'KR' => '82',
        'VN' => '84',
        'KP' => '850',
        'HK' => '852',
        'MO' => '853',
        'KH' => '855',
        'LA' => '856',
        'CN' => '86',
        'PN' => '872',
        'BD' => '880',
        'TW' => '886',
        'TR' => '90',
        'IN' => '91',
        'PK' => '92',
        'AF' => '93',
        'LK' => '94',
        'MM' => '95',
        'MV' => '960',
        'LB' => '961',
        'JO' => '962',
        'SY' => '963',
        'IQ' => '964',
        'KW' => '965',
        'SA' => '966',
        'YE' => '967',
        'OM' => '968',
        'PS' => '970',
        'AE' => '971',
        'IL' => '972',
        'PS' => '972',
        'BH' => '973',
        'QA' => '974',
        'BT' => '975',
        'MN' => '976',
        'NP' => '977',
        'IR' => '98',
        'TJ' => '992',
        'TM' => '993',
        'AZ' => '994',
        'QN' => '994',
        'GE' => '995',
        'KG' => '996',
        'UZ' => '998',
    );

    public static $error_codes = array(
        'domain_missing_child_host' => 'A child host is missing for (one of) given name server.',
    );

    /**
     * getApiUrl
     * Get the HostControl API URL based on the testing-mode of $params
     * @param array $params
     * @return mixed
     */
    public static function getApiUrl($params = array())
    {
        if (! empty($params['AlternativePort']) && $params['AlternativePort'] == "on")
        {
            return self::HOSTCONTROL_PRODUCTION_ALTERNATIVE_API_URL;
        }

        if (in_array($_SERVER['REMOTE_ADDR'], array("212.203.0.138", "192.168.0.185")))
        {
            self::$production = false;
            return self::HOSTCONTROL_TEST_API_URL;
        }

        return self::HOSTCONTROL_PRODUCTION_API_URL;
    }

    /**
     * Convert a telephone number to a number that is accepted by the HostControl API
     * @param $phone
     * @param $country
     * @param null $format
     * @return string
     */
    public static function convertPhoneNumber($phone, $country, $format = null)
    {
        if (! $format)
        {
            return $phone;
        }

        $regex			= $format;
        $escape_chars	= '\()[]{|.?^$+*';

        for ($pos = 0; $pos < strlen($escape_chars); $pos++)
        {
            $regex = str_replace($escape_chars[$pos], "\\" . $escape_chars[$pos], $regex);
        }

        $country_code = self::$country_codes[strtoupper($country)];

        if ($country_code)
        {
            if (substr_count($regex, "%c"))
            {
                $regex = str_replace("%c", "(?P<country_code>" . $country_code . ")", $regex);
            }
        }
        else
        {
            $regex = str_replace("%c", "(?P<country_code>[1-9][0-9]{0,2})", $regex);
        }

        $regex = str_replace("%a", "(?P<area_code>[1-9][0-9]{0,6})", $regex);
        $regex = str_replace("%s", "(?P<subscriber_number>[1-9][0-9 ]{2,15})", $regex);
        $regex = "/" . $regex . "/";

        preg_match($regex, $phone, $m);

        if (! isset($m['country_code']) && $country_code)
        {
            $m['country_code'] = $country_code;
        }

        if (! isset($m['area_code']))
        {
            $m['area_code'] = "";
        }

        $strip_chars = " ";

        for ($pos = 0; $pos < strlen($strip_chars); $pos++)
        {
            $m['subscriber_number'] = str_replace($strip_chars[$pos], "", $m['subscriber_number']);
        }

        if (! $m['country_code'] || !$m['subscriber_number'])
        {
            return $phone;
        }

        $phone = "+" . $m['country_code'] . "." . $m['area_code'] . $m['subscriber_number'];

        return $phone;
    }

    /**
     * debugHandler
     * Store debug information in the WHMCS module Log if needed
     * @param $params
     * @param $action
     * @param $request
     * @param $response
     */
    public static function debugLog($params, $action, $request, $response)
    {
        if (! empty($params['DebugMode']) && $params['DebugMode'] == "on")
        {
            logModuleCall('HostControl', $action, $request, $response);
        }
    }

    /**
     * Create a new customer in the HostControl Backoffice with the specified
     * params. Also adding the HostControl customer id to the internal WHMCS
     * notes for later use
     * @param $api_client
     * @param $params
     * @return bool
     * @throws HostControlHelperError
     */
    public static function create_hostcontrol_customer($api_client, $params)
    {
        $address = $params["address1"];
        if(! empty($params["address2"]))
        {
            $address .= " " . $params["address2"];
        }

        $ip_address = (empty($_SERVER['HTTP_X_FORWARDED_FOR']))?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_X_FORWARDED_FOR'];
        if(empty($ip_address))
        {
            $ip_address = '123.234.123.234';
        }

        $customer_info = array(
            'name'              => $params["firstname"] . ' ' .$params["lastname"],
            'address'           => substr($address, 0, 62),
            'zipcode'           => $params["postcode"],
            'city'              => $params["city"],
            'state'             => $params["state"],
            'country'           => ($params['country'] == "UK" ? "GB" : $params['country']),
            'voice'             => self::convertPhoneNumber($params['phonenumber'], $params['country']),
            'password'          => sha1(md5(time())),
            'email'             => strtolower($params["email"]),
            'registration_ip'   => $ip_address,
        );

        try
        {
            $hostcontrol_customer       = $api_client->customer->create($customer_info);
            $hostcontrol_customer_id    = $hostcontrol_customer->id;

            self::store_customer_id($params['userid'], $hostcontrol_customer_id);

            return $hostcontrol_customer_id;
        }
        catch(HostControlAPIClientError $e)
        {
            logModuleCall('HostControl', 'create-customer-error', $customer_info, $e);
            throw new HostControlHelperError($e->getMessage());
        }

        return false;
    }

    /**
     * Just a combined function of get_hostcontrol_id_from_user() and
     * create_reseller_customer() to make things easier
     * @param $params
     * @param $api_client
     * @throws HostControlHelperError
     * @return bool|mixed
     */
    public static function get_or_create_hostcontrol_customer_id($params, $api_client)
    {
        $hostcontrol_client_id = self::get_customer_id_from_whmcs_user($params['userid']);

        if(! empty($hostcontrol_client_id))
        {
            return $hostcontrol_client_id;
        }

        try
        {
            $whmcs_client_email = strtolower(self::get_whmcs_client_email_address($params['userid']));

            if(empty($whmcs_client_email))
            {
                logModuleCall('HostControl', 'lookup-customer-error', 'No customer emailaddress found for WHMCS customer ' . $params['userid'], $params['userid']);
                throw new HostControlHelperError('No customer emailaddress found for WHMCS client ' . $params['userid'] . '. Please insert an emailaddress for this client.');
            }

            $hostcontrol_customer = array_shift($api_client->customer->lookup($whmcs_client_email));

            if(! empty($hostcontrol_customer->id))
            {
                self::store_customer_id($params['userid'], $hostcontrol_customer->id);
                return $hostcontrol_customer->id;
            }
        }
        catch(HostControlAPIClientError $e)
        {
            logModuleCall('HostControl', 'lookup-customer-error', $whmcs_client_email, $e);
        }

        return self::create_hostcontrol_customer($api_client, $params);
    }

    /**
     * Return array of supported DNS record types
     * @return array
     */
    public static function get_ignored_dns_types()
    {
        return self::$ignored_dns_types;
    }

    /**
     * Checks whether a given TLD's has RegistrarLock support
     * @param $tld
     * @return bool
     */
    public static function tld_has_lock_support($tld)
    {
        return array_key_exists($tld, self::$tld_with_lock_support);
    }

    /**
     * @param $status
     * @return bool
     */
    public static function translate_transfer_status($status)
    {
        if(! array_key_exists($status, self::$status_info))
        {
            return $status;
        }

        return self::$status_info[$status];
    }

    /**
     *
     * @param $code
     * @return string
     */
    public static function get_human_friendly_error($code)
    {
        if(! array_key_exists($code, self::$error_codes))
        {
            return ucfirst(str_replace('_', ' ', $code));
        }

        return self::$error_codes[$code];
    }

    /**
     * Return table name for HostControl storage module
     * @return string
     */
    public static function get_table_name()
    {
        return self::TABLE_NAME;
    }

    /**
     * Get the ID that is associated with this transfer. This is necessary
     * to check the transfer status in Backoffice.
     * @param $domain_id
     * @return bool|int
     */
    public static function get_transfer_id_from_domain($domain_id)
    {
        $transfer_q = "SELECT `remote_id` FROM `%s` WHERE `type` = '%s' AND `domain_id` = %d ORDER BY `id` DESC LIMIT 1";
        $transfer_res = mysql_query(sprintf($transfer_q, self::TABLE_NAME, self::TYPE_HOSTCONTROL_TRANSFER, $domain_id));

        if(! mysql_num_rows($transfer_res))
        {
            return false;
        }

        $row = mysql_fetch_row($transfer_res);
        return intval($row[0]);
    }

    /**
     * Get an e-mailaddress for a WHMCS user
     * @param $user_id
     * @return bool|int
     */
    public static function get_whmcs_client_email_address($user_id)
    {
        return get_query_val('tblclients', 'email', array('id' => $user_id));
    }

    /**
     * Get the remote customer id that is associate with a WHMCS user
     * @param $user_id
     * @return bool|int
     */
    public static function get_customer_id_from_whmcs_user($user_id)
    {
        $transfer_q = "SELECT `remote_id` FROM `%s` WHERE `type` = '%s' AND `user_id` = %d ORDER BY `id` DESC LIMIT 1";
        $transfer_res = mysql_query(sprintf($transfer_q, self::TABLE_NAME, self::TYPE_HOSTCONTROL_CUSTOMER, $user_id));

        if(! mysql_num_rows($transfer_res))
        {
            return false;
        }

        $row = mysql_fetch_row($transfer_res);
        return intval($row[0]);
    }

    /**
     * Store the HostControl Backoffice customer ID
     * @param $whmcs_user_id
     * @param $customer_id
     * @return resource
     */
    public static function store_customer_id($whmcs_user_id, $customer_id)
    {
        $store_customer_sql = "INSERT INTO `%s` (`type`, `user_id`, `remote_id`) VALUES ('%s', %d, %d)";
        $stored = mysql_query(sprintf($store_customer_sql, self::TABLE_NAME, self::TYPE_HOSTCONTROL_CUSTOMER,
            $whmcs_user_id, $customer_id));
        return $stored;
    }

    /**
     * Store the HostControl Backoffice customer ID
     * @param $whmcs_domain_id
     * @param $transfer_id
     * @return resource
     */
    public static function store_transfer_id($whmcs_domain_id, $transfer_id)
    {
        $store_transfer_sql = "INSERT INTO `%s` (`type`, `domain_id`, `remote_id`) VALUES ('%s', %d, %d)";
        $stored = mysql_query(sprintf($store_transfer_sql, self::TABLE_NAME, self::TYPE_HOSTCONTROL_TRANSFER,
            $whmcs_domain_id, $transfer_id));
        return $stored;
    }
}
