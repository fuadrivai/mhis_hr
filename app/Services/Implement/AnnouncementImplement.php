<?php

namespace App\Services\Implement;

use App\Models\AnnouncementCategory;
use App\Models\Branch;
use App\Models\Announcement;
use App\Models\JobLevel;
use App\Models\Organization;
use App\Models\Position;
use App\Services\AnnouncementService;
use Illuminate\Support\Facades\DB;

use function App\Helpers\sendMessage;

class AnnouncementImplement implements AnnouncementService
{
    function get()
    {
        try {
            $announcements = Announcement::with(['branches', 'jobLevels', 'organizations', 'positions', 'category', 'creator', 'updater'])->get();
            return $announcements;
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {
        return Announcement::with(['branches', 'jobLevels', 'organizations', 'positions', 'category', 'creator', 'updater'])->find($id);
    }
    function getCreateFormData(): array
    {
        return [
            'categories' => AnnouncementCategory::query()->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            'organizations' => Organization::query()->orderBy('name')->get(['id', 'name']),
            'jobLevels' => JobLevel::query()->orderBy('name')->get(['id', 'name']),
            'positions' => Position::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function storeAnnouncement(array $data, int $createdBy): Announcement
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $announcement = Announcement::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'link' => $data['link'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'publish_at' => $data['publish_at'],
                'all_employees' => (bool) ($data['all_employees'] ?? true),
                'send_email' => (bool) ($data['send_email'] ?? false),
                'send_push_notification' => (bool) ($data['send_push_notification'] ?? true),
                'created_by' => $createdBy,
                'status' => $data['status'] ?? 'draft',
            ]);

            if (!$announcement->all_employees) {
                $announcement->branches()->sync($data['branches'] ?? []);
                $announcement->organizations()->sync($data['organizations'] ?? []);
                $announcement->jobLevels()->sync($data['job_levels'] ?? []);
                $announcement->positions()->sync($data['positions'] ?? []);
            }

            return $announcement->load(['category', 'creator', 'updater', 'branches', 'organizations', 'jobLevels', 'positions']);
        });
    }

    function post($request)
    {
        try {
            $payload = is_array($request)
                ? $request
                : (method_exists($request, 'all') ? $request->all() : (array) $request);

            $data = [
                'title' => data_get($payload, 'title', data_get($payload, 'subject')),
                'content' => data_get($payload, 'content'),
                'category_id' => data_get($payload, 'category_id', data_get($payload, 'category.id')),
                'publish_at' => data_get($payload, 'publish_at', data_get($payload, 'date')),
                'link' => data_get($payload, 'link'),
                'all_employees' => data_get($payload, 'all_employees', true),
                'send_email' => data_get($payload, 'send_email', false),
                'send_push_notification' => data_get($payload, 'send_push_notification', true),
                'branches' => data_get($payload, 'branches', []),
                'organizations' => data_get($payload, 'organizations', []),
                'job_levels' => data_get($payload, 'job_levels', data_get($payload, 'levels', [])),
                'positions' => data_get($payload, 'positions', []),
                'status' => data_get($payload, 'status', 'draft'),
            ];

            $createdBy = data_get($payload, 'user.id', auth()->id());

            if (!$data['all_employees']) {
                $hasAudience = collect(['branches', 'organizations', 'job_levels', 'positions'])
                    ->contains(function ($field) use ($data) {
                        return !empty($data[$field]);
                    });

                if (!$hasAudience) {
                    return response()->json(["message" => "Select at least one branch, organization, job level, or position."], 422);
                }
            }

            $announcement = $this->storeAnnouncement($data, (int) $createdBy);

            $device_token = [];
            if (!$data['all_employees']) {
                $announcement->branches()->sync($data['branches']);
                $announcement->organizations()->sync($data['organizations']);
                $announcement->jobLevels()->sync($data['job_levels']);
                $announcement->positions()->sync($data['positions']);
            } else {
                $device_token = DB::table('sessions')->where('user_id', '!=', $createdBy)->pluck('device_id');
            }
            $data = [
                "title" => $announcement->title,
                "body" => "Message from " . data_get($payload, 'user.name', 'System'),
            ];
            for ($i = 0; $i < count($device_token); $i++) {
                $token =  $device_token[$i];
                sendMessage($token, $data);
            }

            $fullData = Announcement::with(['branches', 'jobLevels', 'organizations', 'positions'])->find($announcement->id);
            return response()->json($fullData);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($request) {}
    function delete($id) {}
}
