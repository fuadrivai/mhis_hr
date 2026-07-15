<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementCategory;
use App\Services\AnnouncementCategoryService;
use Illuminate\Http\Request;

class AnnouncementCategoryController extends Controller
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
    public function index()
    {
        $categories = $this->announcementCategoryService->get();
        return view('announcement.category.index', [
            'title' => 'Announcement Category',
            'categories' => $categories
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $category = $this->announcementCategoryService->post($request->all());
        return redirect()->back()->with('success', 'Category created successfully.');
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AnnouncementCategory  $announcementCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(AnnouncementCategory $announcementCategory)
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
    public function update(Request $request, AnnouncementCategory $category)
    {
        $this->announcementCategoryService->put(array_merge($request->all(), [
            'id' => $category->id,
        ]));
        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AnnouncementCategory  $announcementCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(AnnouncementCategory $category)
    {
        $this->announcementCategoryService->delete($category->id);
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
