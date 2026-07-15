<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Services\AnnouncementCategoryService;
use App\Services\AnnouncementService;

class AnnouncementController extends Controller
{
    private AnnouncementService $announcementService;
    private AnnouncementCategoryService $announcementCategoryService;

    public function __construct(AnnouncementService $announcementService, AnnouncementCategoryService $announcementCategoryService)
    {
        $this->announcementService = $announcementService;
        $this->announcementCategoryService = $announcementCategoryService;
    }

    public function index()
    {
        return view('announcement.index', [
            'title' => 'Announcement',
            'announcements' => $this->announcementService->get(),
        ]);
    }

    public function create()
    {
        $this->authorizeAnnouncementManagement();

        return view('announcement.create', array_merge([
            'title' => 'Create Announcement',
        ], $this->announcementService->getCreateFormData()));
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $this->authorizeAnnouncementManagement();

        $this->announcementService->storeAnnouncement($request->validated(), $request->user()->id);

        return redirect()->back()->with('success', 'Announcement created successfully.');
    }

    private function authorizeAnnouncementManagement(): void
    {
        $user = auth()->user();
        $roleNames = [];
        $roleIds = [];

        if ($user) {
            $roleNames = $user->roles->pluck('name')->all();
            $roleIds = $user->roles->pluck('id')->all();
        }

        abort_unless(
            $user && (
                in_array('hr', $roleNames, true) ||
                in_array('management', $roleNames, true) ||
                in_array('admin', $roleNames, true) ||
                in_array(1, $roleIds, true) ||
                in_array(3, $roleIds, true)
            ),
            403
        );
    }
}
