<?php

namespace Elama\CoreBundle\Service;

use Google_Client;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_DimensionFilter;
use Google_Service_AnalyticsReporting_DimensionFilterClause;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_GetReportsResponse;
use Google_Service_AnalyticsReporting_Report;
use Google_Service_AnalyticsReporting_ReportRow;
use Google_Service_AnalyticsReporting_DateRangeValues;

class GoogleAnalyticsReportingService
{
    // registration report constants
    const REGISTRATION_REPORT_CLIENT_ID             = '95441927';
    const REGISTRATION_REPORT_START_DATE            = '1daysAgo';
    const REGISTRATION_REPORT_END_DATE              = 'today';
    const REGISTRATION_REPORT_METRIC_EVENT          = 'ga:goal11Starts';
    const REGISTRATION_REPORT_FILTER_EVENT_CATEGORY = 'registration с фронтенда';
    const REGISTRATION_REPORT_DIMENSIONS            = [
        'ga:eventAction',       // elama user_id (должен быть первым в массиве)
        'ga:source',            // источник
        'ga:campaign',          // компания
        'ga:channelGrouping',   // ключевое слово
        'ga:adContent',         // текст объявления
    ];

    /** @var  Google_Service_AnalyticsReporting */
    private $analytics;

    public function __construct($authConfig)
    {
        $client = new Google_Client();
        $client->setApplicationName('register_reporting');
        $client->setAccessType('offline');
        $client->setAuthConfig(json_decode($authConfig, true));
        $client->setScopes([Google_Service_AnalyticsReporting::ANALYTICS_READONLY]);
        $this->analytics = new Google_Service_AnalyticsReporting($client);
    }

    private function getReport(Google_Service_AnalyticsReporting_ReportRequest $request)
    {
        $request = $this->buildRegisterReportRequest();
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        return $this->analytics->reports->batchGet( $body );
    }

    private function buildRegisterReportRequest()
    {
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId(self::REGISTRATION_REPORT_CLIENT_ID);

        $metrics = new Google_Service_AnalyticsReporting_Metric();
        $metrics->setExpression(self::REGISTRATION_REPORT_METRIC_EVENT);
        $metrics->setAlias("user_ids");
        $request->setMetrics([$metrics]);
        // Диапазон дат
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(self::REGISTRATION_REPORT_START_DATE);
        $dateRange->setEndDate(self::REGISTRATION_REPORT_END_DATE);
        $request->setDateRanges($dateRange);
        // Отображаемые поля
        $dimensions = [];
        foreach(self::REGISTRATION_REPORT_DIMENSIONS as $dimensionName) {
            $dimension = new Google_Service_AnalyticsReporting_Dimension();
            $dimension->setName($dimensionName);
            $dimensions[] = $dimension;
        }
        $request->setDimensions($dimensions);
        // Фильтруем по категории
        $dimensionFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
        $dimensionFilter->setDimensionName('ga:eventCategory');
        $dimensionFilter->setOperator('EXACT');
        $dimensionFilter->setExpressions([self::REGISTRATION_REPORT_FILTER_EVENT_CATEGORY]);

        $dimensionFilterCause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
        $dimensionFilterCause->setFilters($dimensionFilter);

        $request->setDimensionFilterClauses($dimensionFilterCause);
        return $request;
    }

    private function parseRegisterReport(Google_Service_AnalyticsReporting_GetReportsResponse $response)
    {
        $resp = [];
        $reports = $response->getReports();
        if (!count($reports)) {
            return $resp;
        }
        /** @var Google_Service_AnalyticsReporting_Report $report */
        $report = current($reports);
        $rows = $report->getData()->getRows();
        /** @var Google_Service_AnalyticsReporting_ReportRow $row */
        foreach($rows as $row) {
            $isValidValue = false;
            $metrics = $row->getMetrics();
            foreach ($metrics as $metric) {
                if ($metric instanceof Google_Service_AnalyticsReporting_DateRangeValues) {
                    $values = $metric->getValues();
                    $isValidValue = current($values) === '1';

                }
            }
            if ($isValidValue) {
                $dimensions = $row->getDimensions();
                $userId = (int) array_shift($dimensions);
                $data = implode($dimensions, ", ");
                $resp[] = [
                    'user_id' => $userId,
                    'data' => $data,
                ];
            }
        }
        return $resp;
    }

    public function getRegisterReport()
    {
        $request = $this->buildRegisterReportRequest();
        $response = $this->getReport($request);
        return $this->parseRegisterReport($response);
    }

}