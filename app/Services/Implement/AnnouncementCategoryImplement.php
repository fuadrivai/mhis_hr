<?php

namespace App\Services\Implement;

use App\Models\AnnouncementCategory;
use App\Services\AnnouncementCategoryService;

class AnnouncementCategoryImplement implements AnnouncementCategoryService
{
    function get($request)
    {
        try {
            $category = AnnouncementCategory::all();
            return response()->json($category);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {}
    function post($request)
    {
        try {
            $category = new AnnouncementCategory();
            $category->name = $request['name'];
            $category->description = $request['description'];
            $category->save();
            return response()->json($category);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($request) {}
    function delete($id) {}
}
