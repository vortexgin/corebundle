<?php

namespace Vortexgin\LibraryBundle\Manager;

/**
 * Google Analytics manager
 * 
 * @category Manager
 * @package  Vortexgin\LibraryBundle\Manager
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class GoogleAnalyticsManager
{

    /**
     * Google Analytics Service
     * 
     * @var \Google_Service_AnalyticsReporting
     */
    private $_service;

    /**
     * View ID
     * 
     * @var string
     */
    private $_viewId;

    /**
     * Date Range
     * 
     * @var \Google_Service_AnalyticsReporting_DateRange
     */
    private $_dateRange;

    /**
     * Metrics
     * 
     * @var array
     */
    private $_metrics;

    /**
     * Dimensions
     * 
     * @var array
     */
    private $_dimensions;

    /**
     * Construct
     * 
     * @param \Google_Client $googleClient Google client instance
     * @param string         $appName      Application name
     * @param string         $scopes       Application scopes
     * @param string         $authConfig   Path authentification file config
     * @param string         $viewId       View ID
     * 
     * @return void
     */
    public function __construct(\Google_Client $googleClient, $appName, $scopes, $authConfig, $viewId = '')
    {
        $googleClient->setApplicationName($appName);
        $googleClient->setScopes($scopes);
        $googleClient->setAuthConfig($authConfig);
        //$googleClient->setAccessType('offline');

        $this->_service = new \Google_Service_AnalyticsReporting($googleClient);
        $this->_viewId = $viewId;
    }

    /**
     * Set view id
     * 
     * @param string $viewId View ID
     * 
     * @return self
     */
    public function setViewId($viewId)
    {
        $this->_viewId = $viewId;

        return $this;
    }

    /**
     * Set date range
     * 
     * @param string $start Start date
     * @param string $end   End date
     * 
     * @return self
     */
    public function setDateRange($start, $end)
    {
        $this->_dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $this->_dateRange->setStartDate($start);
        $this->_dateRange->setEndDate($end);      

        return $this;
    }

    /**
     * Set metrics
     * 
     * @param string $metric Metric
     * @param string $alias  Alias
     * 
     * @return self
     */
    public function setMetrics($metric, $alias = null)
    {
        $session = new \Google_Service_AnalyticsReporting_Metric();
        $session->setExpression($metric);
        if (!empty($alias)) {
            $session->setAlias($alias);
        }
        $this->_metrics[] = $session;
      
        return $this;
    }

    /**
     * Set dimensions
     * 
     * @param string $dimension Dimension
     * 
     * @return self
     */
    public function setDimensions($dimension)
    {
        $session = new \Google_Service_AnalyticsReporting_Dimension();
        $session->setName($dimension);
        $this->_dimensions[] = $session;
      
        return $this;
    }

    /**
     * Request report
     * 
     * @return mixed
     */
    public function request()
    {
        if (empty($this->_viewId)) {
            return 'Please set view id';
        }
        if (empty($this->_dateRange)) {
            return 'Please set date range';
        }
        if (empty($this->_metrics)) {
            return 'Please set metrics';
        }

        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($this->_viewId);
        $request->setDateRanges($this->_dateRange);
        $request->setMetrics($this->_metrics);
        if (!empty($this->_dimensions)) {
            $request->setDimensions($this->_dimensions);
        }

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($request));

        return $this->_service->reports->batchGet($body);
    }

    /**
     * Extract response request
     * 
     * @param array $responses Responses
     * 
     * @return array
     */
    public function extractResponse($responses)
    {
        $return = [];
        foreach ($responses as $response) {
            $metricHeaders = [];
            foreach ($response->getColumnHeader()->getMetricHeader()->getMetricHeaderEntries() as $headerEntry) {
                $metricHeaders[] = $headerEntry->getName();
            }
            $data = [];
            foreach ($response->getData()->getRows() as $row) {
                $item = [];
                foreach ($row->getDimensions() as $dimension) {
                    foreach ($row->getMetrics()[0]->getValues() as $metric) {
                        $item[$dimension][] = !empty($metric)?$metric:0;
                    }

                }
                $data[] = $item;
            }
            $return[] = array(
                'dimensionHeaders' => $response->getColumnHeader()->getDimensions(), 
                'metricHeaders' => $metricHeaders, 
                'data' => $data, 
            );
        }

        return $return;
    }
}