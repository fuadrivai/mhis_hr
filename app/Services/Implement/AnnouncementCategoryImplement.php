<?php

namespace App\Services\Implement;

use App\Models\AnnouncementCategory;
use App\Services\AnnouncementCategoryService;

class AnnouncementCategoryImplement implements AnnouncementCategoryService
{
    function get()
    {
        try {
            $category = AnnouncementCategory::all();
            return $category;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {
        try {
            $category = AnnouncementCategory::find($id);
            if (!$category) {
                return response()->json(["message" => "Category not found"], 404);
            }
            return $category;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function post($request)
    {
        try {
            $category = new AnnouncementCategory();
            $category->name = $request['name'];
            $category->description = $request['description'];
            $category->is_active = $request['is_active'] ==true ? 1 : 0;
            $category->save();
            return $category;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($request) {
        try {
            $category = AnnouncementCategory::find($request['id']);
            if (!$category) {
                return response()->json(["message" => "Category not found"], 404);
            }
            $category->name = $request['name'];
            $category->description = $request['description'];
            $category->is_active = $request['is_active'] ==true ? 1 : 0;
            $category->save();
            return $category;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {
        try {
            $category = AnnouncementCategory::find($id);
            if (!$category) {
                return response()->json(["message" => "Category not found"], 404);
            }
            $category->delete();
            return response()->json(["message" => "Category deleted successfully"]);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
}
