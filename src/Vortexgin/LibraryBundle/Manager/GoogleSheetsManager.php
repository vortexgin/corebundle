<?php

namespace Vortexgin\LibraryBundle\Manager;

/**
 * Google Sheets manager
 * 
 * @category Manager
 * @package  Vortexgin\LibraryBundle\Manager
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class GoogleSheetsManager
{

    /**
     * Google Client
     * 
     * @var \Google_Client
     */
    private $_service;

    /**
     * Spreadsheet ID
     * 
     * @var string
     */
    private $_spreadsheetId;

    /**
     * Construct
     * 
     * @param \Google_Client $googleClient  Google client instance
     * @param string         $spreadsheetId Spreadsheet ID
     * 
     * @return void
     */
    public function __construct(\Google_Client $googleClient, $spreadsheetId)
    {
        $this->_service = new \Google_Service_Sheets($googleClient);
        $this->_spreadsheetId = $spreadsheetId;
    }

    /**
     * Function to get values from range
     * 
     * @param string $range Range of cell
     * 
     * @return mixed
     */
    public function get($range)
    {
        $sheets = $this->_service->spreadsheets_values->get($this->_spreadsheetId, $range);
        return $response->getValues();
    }

    /**
     * Function to update sheet
     * 
     * @param string $range  Range of cell
     * @param array  $values Value paramenter
     * 
     * @return mixed
     */
    public function update($range, array $values)
    {
        $body = new \Google_Service_Sheets_ValueRange(
            [
            'values' => $values
            ]
        );
        $params = [
            'valueInputOption' => 'RAW'
        ];
        return  $_service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
    }
}