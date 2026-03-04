<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;

class GoogleDriveService
{
    protected $service;

    public function __construct()
    {
        $client = new Google_Client();
        $client->setAuthConfig(config('google.service_account_json'));
        $client->addScope(Google_Service_Drive::DRIVE);
        
        $this->service = new Google_Service_Drive($client);
    }

    public function createFolder($name, $parentId = null)
    {
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => $parentId ? [$parentId] : null
        ]);

        $folder = $this->service->files->create($fileMetadata, [
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);

        return $folder->id;
    }

    public function uploadFile($file, $folderId = null)
    {
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => $file->getClientOriginalName(),
            'parents' => $folderId ? [$folderId] : null
        ]);

        $content = file_get_contents($file->getRealPath());

        $uploadedFile = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $file->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink',
            'supportsAllDrives' => true,
        ]);

        return [
            'id' => $uploadedFile->id,
            'link' => $uploadedFile->webViewLink
        ];
    }
}
