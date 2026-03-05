<?php

namespace App\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_Permission;

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

        $permission = new Google_Service_Drive_Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);

        $this->service->permissions->create(
            $uploadedFile->id,
            $permission,
            ['supportsAllDrives' => true]
        );

        return [
            'id' => $uploadedFile->id,
            'link' => $uploadedFile->webViewLink
        ];
    }

    public function deleteFile($fileId)
    {
        $this->service->files->delete($fileId, ['supportsAllDrives' => true]);
    }

    public function checkFile($fileId)
    {
        return $this->service->files->get($fileId, [
            'supportsAllDrives' => true,
            'fields' => 'id, capabilities, driveId, owners'
        ]);
    }

    public function findFolder($name, $parentId = null)
    {
        // escape quote supaya query tidak rusak
        $name = str_replace("'", "\\'", $name);

        $query = "name = '$name' 
                and mimeType = 'application/vnd.google-apps.folder' 
                and trashed = false";

        if ($parentId) {
            $query .= " and '$parentId' in parents";
        }

        $response = $this->service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        $files = $response->getFiles();

        if (count($files) > 0) {
            return $files[0]->getId();
        }

        return null;
    }
}
