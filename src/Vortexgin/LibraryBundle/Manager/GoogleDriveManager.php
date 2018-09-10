<?php

namespace Vortexgin\LibraryBundle\Manager;

/**
 * Google Drive manager
 * 
 * @category Manager
 * @package  Vortexgin\LibraryBundle\Manager
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class GoogleDriveManager
{

    /**
     * Google Client
     * 
     * @var \Google_Client
     */
    private $_service;

    /**
     * Construct
     * 
     * @param \Google_Client $googleClient  Google client instance
     * @param string         $appName       Application name
     * @param string         $scopes        Application scopes
     * @param string         $authConfig    Path authentification file config
     * @param string         $spreadsheetId Spreadsheet ID
     * 
     * @return void
     */
    public function __construct(\Google_Client $googleClient, $appName, $scopes, $authConfig)
    {
        $googleClient->setApplicationName($appName);
        $googleClient->setScopes($scopes);
        $googleClient->setAuthConfig($authConfig);

        $this->_service = new \Google_Service_Drive($googleClient);
    }

    /**
     * Function to search file based on filename
     * 
     * @param string $keyword Keyword of filename
     * 
     * @return mixed
     */
    public function search($keyword)
    {
        $response = $this->_service->files->listFiles(array(
            'q' => $keyword,
            'spaces' => 'drive',
            'pageToken' => null,
            'fields' => 'nextPageToken, files(id, name)',
        ));        
        return $response->files;
    }

    /**
     * Function to download file
     * 
     * @param string $fileId ID of file on google drive
     * 
     * @return mixed
     */
    public function download($fileId)
    {
        $response = $this->_service->files->get(
            $fileId, 
            array(
                'alt' => 'media'
            )
        );
        return $response->getBody()->getContents();
   }
}