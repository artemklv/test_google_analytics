<?php

namespace Elama\CoreBundle\Service;
use Google_Client;
use Google_Service_Analytics;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_GetReportsRequest;

class GoogleAnalyticsReportingService
{
    const REGISTRATION_REPORT_CLIENT_ID = '95441927';

    /** @var  Google_Service_AnalyticsReporting */
    private $analytics;

    public function __construct($authConfig)
    {
        $client = new Google_Client();
        $client->setApplicationName('register_reporting');
        $client->setAccessType('offline');
        $client->setAuthConfig(json_decode($authConfig, true));
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $this->analytics = new Google_Service_AnalyticsReporting($client);
    }

    public function getRegisterReport()
    {
        // Create the DateRange object.
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate("7daysAgo");
        $dateRange->setEndDate("today");

        // Create the Metrics object.
        $sessions = new Google_Service_AnalyticsReporting_Metric();
        $sessions->setExpression("ga:sessions");
        $sessions->setAlias("sessions");

        // Create the ReportRequest object.
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId('95441927');
        $request->setDateRanges($dateRange);
        $request->setMetrics([$sessions]);

        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        return $this->analytics->reports->batchGet( $body );
    }
}