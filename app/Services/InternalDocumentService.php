<?php

namespace App\Services;

interface InternalDocumentService
{
    function get();
    function show($id);
    function post($request);
    function put($id, $request);
    function delete($id);
    function getByUserId($id);
}
