<?php

namespace App\Services;

interface AnnouncementService

{
    function get($request);
    function show($id);
    function post($request);
    function put($request);
    function delete($id);
}
