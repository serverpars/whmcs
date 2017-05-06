<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'hostcontrol.helper.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'json.functions.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'hostcontrol_api_client' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'base.php';

/**
 * Configuration setup for the HostControl registrar plugin for WHMCS
 * @return array
 */
function hostcontrol_getConfigArray()
{
    hostcontrol_install_db();

    $revision_date      = "2015-07-29";
    $revision_url_check = "http://www.hostcontrol.com/whmcs/version.txt";
    $download_text      = " <a href='http://www.hostcontrol.com/whmcs/latest.zip' target='_blank'>Click here to download the latest version</a>";
    $visit_changelog    = " <a href='http://www.hostcontrol.com/whmcs/' target='_blank'>Review the changelog</a>";

    $configuration = array(
        "ApiKey" => array(
            "FriendlyName" => "API key",
            "Type" => "text",
            "Description" => "You can find your API key in the Reseller Area, at the bottom of the Dashboard &rarr; Label page."
        ),
        "AlternativePort"   => array(
            "FriendlyName" => "Use Alternative Connect Port",
            "Type" => "yesno",
            "Description" => "Enable this setting if you are experiencing any connection/timeout "
            . "issues in revision 2013-12-09-rev.1 or older.<br />"
            . "This setting will cause the module to connect to Hostcontrol on port 14739 and "
            . "may resolve 'no response received' messages."
        ),
        "DebugMode" => array(
            "FriendlyName" => "Use Debug Mode",
            "Type" => "yesno",
            "Description" => "Debug mode provides extensive information when an error occurs. "
            . "This information will be logged at <i>Utilities &rarr; Logs &rarr; Module Logs</i>.<br />"
            . "Only enable this if you want to investigate errors.<br/><strong>Please note:</strong> "
            . "Since WHMCS v6.x You have to click Enable Debug Logging first!"
        ),
        "VersionTag" => array(
            "FriendlyName" => "Version date",
            "Description" => $revision_date,
        ),
        "Updates" => array(
            "Description" => "Please check our site regularly to see if there is a new version
                              of this registrar module available for you.<br />
                               - " . $download_text . "<br /> - " . $visit_changelog
        ),
    );

    $remote_version = @file_get_contents($revision_url_check);
    if(! empty($remote_version))
    {
        $current_version_date = date('Ymd', strtotime($revision_date));
        $hostcontrol_version_date = date('Ymd', strtotime(substr($remote_version, 0, 10)));

        if($hostcontrol_version_date > $current_version_date)
        {
            $configuration["Description"]["Value"] = "<span style='color: red; font-weight: bold;'>ATTENTION</span>:
            An official update is available!<br />" . $download_text;
        }
        elseif($hostcontrol_version_date == $current_version_date)
        {
            $configuration["Description"]["Value"] = "<span style='color: darkgreen; font-weight: bold;'>Up-to-date</span>:
            You are using the latest version of the module." . $visit_changelog;
        }
    }
    else
    {
        $configuration["Description"] = array(
                "Type" => "System",
                "Value" => "Want to use the latest features? Stay up-to-date! " . $visit_changelog . " or " . $download_text
        );
    }

    return $configuration;
}

/**
 * Activate the HostControl Module by installing our datatable
 * @return array
 */
function hostcontrol_install_db()
{
    $table_name = HostControlHelper::get_table_name();
    $install_query = "
            CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `type` varchar(50) NOT NULL,
              `domain_id` int(11) NULL,
              `user_id` int(11) NULL,
              `remote_id` int(11) NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
    mysql_query($install_query);
}

/**
 * Register the domainname with all of the parameters
 * given in $params.
 * @param $params
 * @return mixed
 */
function hostcontrol_RegisterDomain($params = array())
{

    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    /* Get or create BackOffice customer ID */
    try
    {
        $hostcontrol_customer_id = HostControlHelper::get_or_create_hostcontrol_customer_id($params, $api_client);
    }
    catch(HostControlHelperError $e)
    {
        return array('error' => 'Could not create account for your domain: ' . $e->getMessage());
    }
    //interval changed to 12 month to bypass domain register for more than 1 years error
    $interval = 12;
    $privacy_protect = (empty($params["idprotection"])?false:true);

    try
    {
        
        $api_client->domain->register(
            $hostcontrol_customer_id,
            $domainname,
            $interval,
            $privacy_protect
        );
    }
    catch(HostControlAPIClientError $e)
    {
        $request = array($hostcontrol_customer_id, $domainname, $interval, $privacy_protect);
        HostControlHelper::debugLog($params, 'register-domain', $request, $e);

        return array('error' => $e->getMessage());
    }

    //do renew for n-1 registeration period 
    $regp=$params["regperiod"];
    if ($regp>1) {
 
        for ($i=0 ;$i<$regp-1 ; $i++) { 
            
            $interval = 12;

            try
            {
                
                $api_client->domain->renew($domainname, $interval);
            }
            catch(HostControlAPIClientError $e)
            {
                HostControlHelper::debugLog($params, 'renew-domain', array($domainname, $interval), $e);
                return array('error' => $e->getMessage());
            }



        }
    }

    /* It seems that domain registration succeeded, now update nameservers */
    sleep(1.5);
    $change_ns = hostcontrol_SaveNameservers($params);
    if($change_ns !== true)
    {
        $error_msg = 'Nameserver not set because of 500 error';
        if(is_array($change_ns) && array_key_exists('error', $change_ns))
        {
            $error_msg = $change_ns['error'];
        }

        HostControlHelper::debugLog(
            $params,
            'register-domain-set-nameserver',
            'Set nameserver for just registered domain ' . $domainname,
            $error_msg
        );
    }

    return true;
}

/**
 * Transfer a domainname with all of the parameters
 * given in $params.
 * @param $params
 * @return mixed
 */
function hostcontrol_TransferDomain($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    /* Get or create HostControl customer ID */
    try {
        $hostcontrol_customer_id = HostControlHelper::get_or_create_hostcontrol_customer_id($params, $api_client);
    }
    catch(HostControlHelperError $e)
    {
        return array('error' => $e->getMessage());
    }
    $interval = $params["regperiod"]*12;
    $privacy_protect = (empty($params["idprotection"])?false:true);
    $authcode = (empty($params["transfersecret"])?'':$params["transfersecret"]);
    $domain_id = $params["domainid"];

    try
    {
        $transfer = $api_client->domain->transfer(
            $hostcontrol_customer_id,
            $domainname,
            $authcode,
            $interval,
            $privacy_protect
        );
    }
    catch(HostControlAPIClientError $e)
    {
        $request = array($hostcontrol_customer_id, $domainname, $authcode, $interval, $privacy_protect);
        HostControlHelper::debugLog($params, 'transfer-domain', $request, $e);

        return array('error' => $e->getMessage());
    }

    HostControlHelper::store_transfer_id($domain_id, $transfer->id);

    return true;
}

/**
 * Request deletion for a domainname
 * @param $params
 * @return array|bool
 */
function hostcontrol_RequestDelete($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    try
    {
        $api_client->domain->delete($domainname);
    }
    catch(HostControlAPIClientError $e)
    {
        HostControlHelper::debugLog($params, 'delete-domain', array($domainname), $e);
        return array('error' => $e->getMessage());
    }

    return true;
}

/**
 * Request a renewal for a given domain
 * @param $params
 * @return array|bool
 */
function hostcontrol_RenewDomain($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);
    $interval =12;
    $regp=$params["regperiod"];
	
    if ($regp>=1) {
 
        for ($i=0 ;$i<$regp ; $i++) { 

            try
            {
                
                $api_client->domain->renew($domainname, $interval);
            }
            catch(HostControlAPIClientError $e)
            {
                HostControlHelper::debugLog($params, 'renew-domain', array($domainname, $interval), $e);
                return array('error' => $e->getMessage());
            }



        }
    }
    

    return true;
}

/**
 * Retrieve the domains nameservers
 * @param $params
 * @return array
 */
function hostcontrol_GetNameservers($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    try
    {
        $nameservers = $api_client->domain->getNameservers($domainname);
    }
    catch(HostControlAPIClientError $e)
    {
        HostControlHelper::debugLog($params, 'domain-getnameservers', array($domainname), $e);
        return array('error' => "Could not get nameservers for " . $domainname);
    }

    $ns = 1;
    $domain_data = array();
    foreach($nameservers as $nameserver)
    {
        if(! empty($nameserver->hostname))
        {
            $domain_data["ns" . $ns] = $nameserver->hostname;
            $ns++;
        }
    }

    return $domain_data;
}

/**
 * Save submitted nameservers to HostControl
 * @param $params
 * @return array|null
 */
function hostcontrol_SaveNameservers($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    $nameservers = array();
    for($nsi = 1; $nsi < 6; $nsi++)
    {
        if(! empty($params["ns" . $nsi]))
        {
            $nameservers[] = array('hostname' => $params["ns" . $nsi]);
        }
    }

    try
    {
        $result = $api_client->domain->setNameservers($domainname, $nameservers);
    }
    catch(HostControlAPIClientError $e)
    {
        HostControlHelper::debugLog($params, 'domain-setnameservers', array($domainname, $nameservers), $e);

        $error_message = $e->getMessage();
        if($error_message == "custom_nameservers_disabled")
        {
            $error_message = "Changing nameservers is disabled for this customer";
        }

        return array('error' => $error_message);
    }

    if($result->status == "delivered")
    {
        return true;
    }

    return array('error' => 'Updating nameservers failed');
}

/**
 * Get the EPP code, also known as auth-code in HostControl
 * @param $params
 * @return array
 */
function hostcontrol_GetEPPCode($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    try
    {
        $authcode = $api_client->domain->getAuthcode($domainname);
    }
    catch(HostControlAPIClientError $e)
    {
        HostControlHelper::debugLog($params, 'domain-authcode', array($domainname), $e);
        return array('error' => $e->getMessage());
    }

    $domain_data["eppcode"] = $authcode->auth_code;
    return $domain_data;
}

/**
 * Get DNS records for the specified domainname
 * Filter out a few records, because WHMCS doesn't know what to do with them
 * @param $params
 * @return array
 */
function hostcontrol_GetDNS($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    try
    {
        $dns = $api_client->domain->getDNSRecords($domainname);
    }
    catch(HostControlAPIClientError $e)
    {
        HostControlHelper::debugLog($params, 'domain-get-dns-records', array($domainname), $e);
        return array('error' => $e->getMessage());
    }

    $dns_records = array();
    foreach($dns->records as $record)
    {
        /* Filter some DNS types */
        if(in_array($record->type, HostControlHelper::get_ignored_dns_types()))
        {
            continue;
        }

        $dns_records[] = array(
            "hostname"  => $record->name,
            "type"      => $record->type,
            "address"   => $record->content,
            "ttl"       => $record->ttl,
            "priority"  => $record->prio,
        );
    }

    return $dns_records;
}

/**
 * Save custom DNS records
 * @param $params
 * @return array|null
 */
function hostcontrol_SaveDNS($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    $dns_records = array();
    foreach ($params["dnsrecords"] as $key => $values)
    {
        if(empty($values['hostname']) && empty($values['address']))
        {
            continue;
        }

        $record = array(
            'type' => $values['type'],
            'content' => $values['address'],
            'name' => $values['hostname'],
        );

        if($values['type'] == "MX")
        {
            $record['prio'] = $values['priority'];
        }

        $dns_records[] = $record;
    }

    try
    {
        $api_client->domain->setDNSRecords($domainname, $dns_records);
    }
    catch(HostControlAPIClientError $e)
    {
        HostControlHelper::debugLog($params, 'domain-set-dns-records', array($domainname, $dns_records), $e);
        $error_message = "Could not update DNS settings. Please ensure your entries were submitted correctly.";
        return array('error' => $error_message);
    }

    return true;
}

/**
 * Get current status of registrar lock for domain, if tld has locking support
 * @param $params
 * @return array|string
 */
function hostcontrol_GetRegistrarLock($params)
{
    if(! HostControlHelper::tld_has_lock_support($params["tld"]))
    {
        return false;
    }

    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    try
    {
        $domain_info = $api_client->domain->get($domainname);

        return ((bool)$domain_info->extended->is_transfer_locked) ? 'locked' : 'unlocked';
    }
    catch(HostControlAPIClientError $e)
    {
        $request = array($domainname);
        HostControlHelper::debugLog($params, 'domain-get', $request, $e);

        return array('error' => $e->getMessage());
    }
}

/**
 * Save new state of Registrar lock, returns friendly error if TLD does not support registrar lock (like .nl)
 * @param $params
 * @return array|null
 */
function hostcontrol_SaveRegistrarLock($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);

    try
    {
        HostControlHelper::debugLog($params, 'domain-update', $domainname, array(
            'transfer_lock' => ($params['lockenabled'] == 'locked') ? true : false
        ));

        $api_client->domain->update($domainname, array(
            'transfer_lock' => ($params['lockenabled'] == 'locked') ? true : false
        ));
    }
    catch(HostControlAPIClientError $e)
    {
        $request = array($domainname);
        HostControlHelper::debugLog($params, 'domain-update', $request, $e);

        return array('error' => $e->getMessage());
    }

    return array();
}



/**
 * TransferSync function that is used every time the WHMCS cronjob runs.
 * It's used to update statuses on domain name information
 * @param $params
 * @return array
 */
function hostcontrol_TransferSync($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);

    $domainname = strtolower($params["sld"] . "." . $params["tld"]);
    $domain_id  = $params['domainid'];
    $transfer_id = HostControlHelper::get_transfer_id_from_domain($domain_id);

    if(! $transfer_id)
    {
        return array(
            'failed' => true,
            'reason', 'No transfer found for this domainname (at the registrar).'
        );
    }

    try
    {
        $transfer = $api_client->domain->getTransferStatus($transfer_id);
    }
    catch(HostControlAPIClientError $e)
    {
        HostControlHelper::debugLog($params, 'get-transfer-status', array($domainname, $transfer_id), $e);
        return array('error' => $e->getMessage());
    }

    $status = array();
    if($transfer->status == 'completed')
    {
        $status['completed'] = true;

        try
        {
            $domain = $api_client->domain->get($domainname);
            $status['expirydate'] = date('Y-m-d', strtotime($domain->expires));
        }
        catch(HostControlAPIClientError $e)
        {
            HostControlHelper::debugLog($params, 'domain-get-single', array($domainname), $e);
        }

        return $status;
    }
    else if(in_array($transfer->status, array('failed', 'canceled')))
    {
        $status['failed'] = true;
        $status['reason'] = HostControlHelper::translate_transfer_status($transfer->status);
        return $status;
    }

    return null;
}

/**
 * Sync the domainnames in WHMCS with the domainname (statuses) at HostControl
 * @param $params
 * @return array
 */
function hostcontrol_Sync($params)
{
    $api_client = new HostControlAPIClient(HostControlHelper::getApiUrl($params), $params['ApiKey']);
    $domainname = strtolower($params["sld"] . "." . $params["tld"]);
    $domain_info = array();

    try
    {
        $domain = $api_client->domain->get($domainname);
        $domain_info['expirydate'] = date('Y-m-d', strtotime($domain->expires));
    }
    catch(HostControlAPIClientError $e)
    {
        HostControlHelper::debugLog($params, 'domain-get-single', array($domainname), $e);

        if(in_array($e->getMessage(), array('invalid_domain', 'invalid_resource')))
        {
            $domain_info['active'] = false;
            $domain_info['expired'] = true;
        }
    }

    return $domain_info;
}
