<?php

namespace App\Services;

use App\Models\Announcement;

interface AnnouncementService

{
    function get();
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
    function getCreateFormData(): array;
    function storeAnnouncement(array $data, int $createdBy): Announcement;
}
