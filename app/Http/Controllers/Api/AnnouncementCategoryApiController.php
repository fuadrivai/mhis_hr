<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnnouncementCategory;
use App\Services\AnnouncementCategoryService;
use Illuminate\Http\Request;

class AnnouncementCategoryApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private AnnouncementCategoryService $announcementCategoryService;

    public function __construct(AnnouncementCategoryService $announcementCategoryService)
    {
        $this->announcementCategoryService = $announcementCategoryService;
    }
    public function index(Request $request)
    {
        return $this->announcementCategoryService->get($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->announcementCategoryService->post($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AnnouncementCategory  $announcementCategory
     * @return \Illuminate\Http\Response
     */
    public function show(AnnouncementCategory $announcementCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AnnouncementCategory  $announcementCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AnnouncementCategory $announcementCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AnnouncementCategory  $announcementCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(AnnouncementCategory $announcementCategory)
    {
        //
    }
}
