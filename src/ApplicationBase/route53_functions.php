<?php

/**
 * Check Health Status of Health Check
 * @param $client
 * @param $healthCheckId
 * @param int $failureThreshold
 * @return bool
 */
function isHealthCheckStatusHealthy($client, $healthCheckId, $failureThreshold = 5)
{
    try {
        $result = $client->getHealthCheckStatus(array('HealthCheckId' => $healthCheckId));
        $healthCheckStatus = $result->toArray();

        if (!isset($healthCheckStatus['HealthCheckObservations']) ||
            (isset($healthCheckStatus['HealthCheckObservations']) && 0 == count($healthCheckStatus['HealthCheckObservations']))
        ) {
            return false;
        }

        $failureCount = 0;
        $checkCounter = 0;
        if ($failureThreshold > 16) {
            $failureThreshold = 16;
        }

        foreach ($healthCheckStatus['HealthCheckObservations'] as $observation) {
            // If not success then Increase Failure count
            if (!(isset($observation['StatusReport']['Status']) && $observation['StatusReport']['Status'] == 'Success: HTTP Status Code 200, OK')) {
                $failureCount++;
            }
            $checkCounter++;

            // Check only last $failureThreshold records
            if ($checkCounter == $failureThreshold) {
                break;
            }
        }

        return ($failureCount < $failureThreshold ? true : false);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Terminate Instances
 * @param $globalConfig
 * @param $instances
 * @return bool
 */
function terminateInstances($globalConfig, $instances)
{
    try {
        $configEc2 = array(
            'key' => $globalConfig['ec2']['key'], // Your AWS Access Key ID
            'secret' => $globalConfig['ec2']['secret'], // Your AWS Secret Access Key
            'region' => $globalConfig['ec2']['region'],
        );

        $clientEc2 = \Aws\Ec2\Ec2Client::factory($configEc2);

        // Terminate instance API
        $result = $clientEc2->terminateInstances(array(
            'DryRun' => false,
            'InstanceIds' => $instances,
        ));
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Change Resource record set
 * @param $client
 * @param $zoneId
 * @param $createResourceRecordSet
 * @param string $action
 * @return bool
 */
function changeResourceRecordSet($client, $zoneId, $createResourceRecordSet, $action = 'UPSERT')
{
    try {
        $resourceRecordSets = array(
            'HostedZoneId' => $zoneId,
            'ChangeBatch' => array(
                'Changes' => array(
                    array(
                        'Action' => $action,
                        'ResourceRecordSet' => $createResourceRecordSet
                    )
                )
            )
        );

        $client->changeResourceRecordSets($resourceRecordSets);
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Delete Route 53 entry
 * @param $client
 * @param $zoneId
 * @param $farmUrl
 * @param $ipAddress
 * @return bool
 */
function deleteRoute53($client, $zoneId, $farmUrl, $ipAddress)
{
    try {
        // Get all available Resource records
        $result = $client->listResourceRecordSets(array(
            'HostedZoneId' => $zoneId,
            'StartRecordName' => $farmUrl,
        ));

        $route53Array = $result->toArray();

        foreach ($route53Array['ResourceRecordSets'] as $resourceRecord) {
            if (isset($resourceRecord['ResourceRecords'][0]['Value']) && $resourceRecord['ResourceRecords'][0]['Value'] == $ipAddress) {
                changeResourceRecordSet($client, $zoneId, $resourceRecord, 'DELETE');
            }
        }
        $status = true;
        return $status;

    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Delete Health Check
 * @param $client
 * @param $ipAddress
 * @return bool
 */
function deleteHealthCheck($client, $ipAddress)
{
    try {
        // Get all available health checks
        $result = $client->listHealthChecks();
        $allHealthChecks = $result->toArray();

        foreach ($allHealthChecks['HealthChecks'] as $healthCheck) {
            if (isset($healthCheck['HealthCheckConfig']['IPAddress']) && $healthCheck['HealthCheckConfig']['IPAddress'] == $ipAddress) {
                $client->deleteHealthCheck(array(
                    'HealthCheckId' => $healthCheck['Id'],
                ));
            }
        }
        $status = true;
        return $status;

    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Check Health check is created or not
 * @param $client
 * @param $ipAddress
 * @return bool
 */
function isHealthCheckExist($client, $ipAddress)
{
    $status = false;
    try {
        // Get all available health checks
        $result = $client->listHealthChecks();
        $allHealthChecks = $result->toArray();

        foreach ($allHealthChecks['HealthChecks'] as $healthCheck) {
            if (isset($healthCheck['HealthCheckConfig']['IPAddress']) && $healthCheck['HealthCheckConfig']['IPAddress'] == $ipAddress) {
                $status = true;
                break;
            }
        }
        return $status;
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Check Farm Resource Record is created or not
 * @param $client
 * @param $zoneId
 * @param $farmUrl
 * @param $myPublicIp
 * @return bool
 */
function isFarmResourceRecordExist($client, $farmUrl, $zoneId, $myPublicIp)
{
    $status = false;
    try {
        // Get all available Resource records
        $result = $client->listResourceRecordSets(array(
            'HostedZoneId' => $zoneId,
            'StartRecordName' => $farmUrl,
        ));

        $route53Array = $result->toArray();

        foreach ($route53Array['ResourceRecordSets'] as $resourceRecord) {
            if (isset($resourceRecord['ResourceRecords'][0]['Value']) && $resourceRecord['ResourceRecords'][0]['Value'] == $myPublicIp) {
                $status = true;
                break;
            }
        }
        return $status;
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Remove Route 53 and Health Check
 * @param $client
 * @param $zoneId
 * @param $farmUrl
 * @param $instanceDetails
 * @param string $source
 * @return bool
 */
function removeRoute53AndHealthCheck($client, $zoneId, $farmUrl, $instanceDetails, $source = 'Daemon')
{
    $status = false;
    try {
        global $globalConfig;
        $retry = 0;

        while (true) {
            // Max try for deleting as per config
            if ($retry >= $globalConfig['aws']['retry_delete_dns_entry_attempts']) {
                break;
            }
            $retry++;

            $isRemovedRecord = deleteRoute53($client, $zoneId, $farmUrl, $instanceDetails['ip_address']);
            if (true == $isRemovedRecord) {
                echo logTime('action') . 'Request Submitted for Resource Record Deletion with ip ' . $instanceDetails['ip_address'] . PHP_EOL;
            } else {
                echo logTime('action') . 'Request failed to Submit for Resource Record Deleted with ip ' . $instanceDetails['ip_address'] . PHP_EOL;
                sendLogErrorEmail($source, 'Request Submitting for Resource Record Deletion failure', $instanceDetails, $instanceDetails['ip_address']);
            }

            $attempt = 0;
            $isFarmResourceRecordNotDeleted = true;

            while (true) {
                echo logTime() . 'Sleeping for ' . $globalConfig['aws']['check_delete_dns_entry_seconds'] . ' seconds' . PHP_EOL;
                sleep($globalConfig['aws']['check_delete_dns_entry_seconds']);

                $isFarmResourceRecordNotDeleted = isFarmResourceRecordExist($client, $farmUrl, $zoneId, $instanceDetails['ip_address']);

                if ($isFarmResourceRecordNotDeleted == false) {
                    echo logTime() . 'Success in Deleting Resource Record for ip address ' . $instanceDetails['ip_address'] . PHP_EOL;
                    break;
                } else {
                    echo logTime() . 'Resource Record for ip address ' . $instanceDetails['ip_address'] . ' not deleted yet' . PHP_EOL;
                }
                $attempt++;

                if ($attempt == $globalConfig['aws']['check_delete_dns_entry_attempts']) {
                    echo logTime('error') . 'Failed Removing Resource Record for ip address ' . $instanceDetails['ip_address'] . ' in ' . $attempt . ' attempts. Sending email.' . PHP_EOL;
                    sendLogErrorEmail($source, 'Deleting Resource Record failure', $instanceDetails, $instanceDetails['ip_address']);
                    break;
                }
            }

            if (true == $isFarmResourceRecordNotDeleted) {
                continue;
            }

            $isRemovedHealthCheck = deleteHealthCheck($client, $instanceDetails['ip_address']);
            if (true == $isRemovedHealthCheck) {
                echo logTime('action') . 'Request Submitted for Health Check Deletion with ip ' . $instanceDetails['ip_address'] . PHP_EOL;
            } else {
                echo logTime('action') . 'Request failed to Submit for Health Check Deleted with ip ' . $instanceDetails['ip_address'] . PHP_EOL;
                sendLogErrorEmail($source, 'Request Submitting for Health Check Deletion failure', $instanceDetails, $instanceDetails['ip_address']);
            }

            $attempt = 0;
            $isHealthCheckNotDeleted = true;
            while (true) {
                echo logTime() . 'Sleeping for ' . $globalConfig['aws']['check_delete_dns_entry_seconds'] . ' seconds' . PHP_EOL;
                sleep($globalConfig['aws']['check_delete_dns_entry_seconds']);

                $isHealthCheckNotDeleted = isHealthCheckExist($client, $instanceDetails['ip_address']);

                $attempt++;
                if ($isHealthCheckNotDeleted == false) {
                    echo logTime() . 'Success in Deleting Health Check for ip address ' . $instanceDetails['ip_address'] . PHP_EOL;
                    break;
                } else {
                    echo logTime() . 'Health Check for ip address ' . $instanceDetails['ip_address'] . ' not deleted yet' . PHP_EOL;
                }

                if ($attempt == $globalConfig['aws']['check_delete_dns_entry_attempts']) {
                    echo logTime('error') . 'Failed Removing Route 53 for ip address ' . $instanceDetails['ip_address'] . ' in ' . $attempt . ' attempts. Sending email.' . PHP_EOL;
                    sendLogErrorEmail($source, 'Deleting Health Check failure', $instanceDetails, $instanceDetails['ip_address']);
                    break;
                }
            }

            if (true == $isHealthCheckNotDeleted) {
                continue;
            }
            if ($retry > 1) {
                sendLogInfoEmail($source, 'Deleting Resource Record & Health check success', $instanceDetails, $instanceDetails['ip_address']);
            }
            $status = true;
            break;
        }
        return $status;
    } catch (\Exception $e) {
        return $status;
    }
}