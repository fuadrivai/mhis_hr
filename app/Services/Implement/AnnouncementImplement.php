<?php

namespace App\Services\Implement;

use App\Mail\TimeoffMail;
use App\Models\AnnouncementCategory;
use App\Models\Branch;
use App\Models\Announcement;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\Organization;
use App\Models\Position;
use App\Services\AnnouncementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use function App\Helpers\sendMessage;

class AnnouncementImplement implements AnnouncementService
{
    function get($request = null)
    {
        try {
            $query = Announcement::with(['branches', 'jobLevels', 'organizations', 'positions', 'category', 'creator', 'updater']);

            if ($request !== null) {
                $user = null;

                if (is_array($request)) {
                    $user = data_get($request, 'user');
                } elseif (is_object($request)) {
                    $user = data_get($request, 'user');

                    if (!$user && method_exists($request, 'user')) {
                        $user = $request->user();
                    }
                }

                if (!$user) {
                    $user = auth()->user();
                }

                $employment = optional(optional($user)->employee)->employment;

                $query->where(function ($announcementQuery) use ($employment) {
                    $announcementQuery->where('all_employees', true);

                    if (!$employment) {
                        return;
                    }

                    $announcementQuery->orWhere(function ($audienceQuery) use ($employment) {
                        $audienceQuery->where('all_employees', false);

                        $audienceQuery->where(function ($branchScope) use ($employment) {
                            $branchScope->doesntHave('branches');

                            if (!empty($employment->branch_id)) {
                                $branchScope->orWhereHas('branches', function ($branchQuery) use ($employment) {
                                    $branchQuery->where('branches.id', $employment->branch_id);
                                });
                            }
                        });

                        $audienceQuery->where(function ($organizationScope) use ($employment) {
                            $organizationScope->doesntHave('organizations');

                            if (!empty($employment->organization_id)) {
                                $organizationScope->orWhereHas('organizations', function ($organizationQuery) use ($employment) {
                                    $organizationQuery->where('organizations.id', $employment->organization_id);
                                });
                            }
                        });

                        $audienceQuery->where(function ($jobLevelScope) use ($employment) {
                            $jobLevelScope->doesntHave('jobLevels');

                            if (!empty($employment->job_level_id)) {
                                $jobLevelScope->orWhereHas('jobLevels', function ($jobLevelQuery) use ($employment) {
                                    $jobLevelQuery->where('job_levels.id', $employment->job_level_id);
                                });
                            }
                        });

                        $audienceQuery->where(function ($positionScope) use ($employment) {
                            $positionScope->doesntHave('positions');

                            if (!empty($employment->job_position_id)) {
                                $positionScope->orWhereHas('positions', function ($positionQuery) use ($employment) {
                                    $positionQuery->where('positions.id', $employment->job_position_id);
                                });
                            }
                        });
                    });
                });

                $query->where('publish_at', '<=', Carbon::now())
                    ->orderByDesc('publish_at');
            }

            $announcements = $query->get();
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
            $attachmentPath = null;
            if (!empty($data['attachment']) && $data['attachment']->isValid()) {
                $attachmentPath = $data['attachment']->store('announcements', 'public');
            }

            $announcement = Announcement::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'link' => $data['link'] ?? null,
                'attachment' => $attachmentPath,
                'category_id' => $data['category_id'] ?? null,
                'publish_at' => Carbon::now(),
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

            $message = [
                'title' => $announcement->title,
                'content' => $announcement->content,
                'link' => $announcement->link,
                'subject'=>'You have a new announcement From MHIS HUB',
                'template'=>'email-template.announcement',
            ];

            if ($announcement->send_email) {
                $employeeQueries = Employee::where('is_active', 1)->with('personal', 'employment');
                if(!$announcement->all_employees){
                    $employeeQueries->whereHas('employment', function ($query) use ($data) {
                        if (!empty($data['branches'])) {
                            $query->whereIn('branch_id', $data['branches']);
                        }
                        if (!empty($data['organizations'])) {
                            $query->whereIn('organization_id', $data['organizations']);
                        }
                        if (!empty($data['job_levels'])) {
                            $query->whereIn('job_level_id', $data['job_levels']);
                        }
                        if (!empty($data['positions'])) {
                            $query->whereIn('job_position_id', $data['positions']);
                        }
                    });
                }
                $employees = $employeeQueries->get();
                $attachmentFullPath = $announcement->attachment
                    ? storage_path('app/public/' . $announcement->attachment)
                    : null;

                foreach ($employees as $employee) {
                    if (empty(optional($employee->personal)->email)) {
                        continue;
                    }

                    $mail = new TimeoffMail($message);
                    if ($attachmentFullPath && file_exists($attachmentFullPath)) {
                        $mail->attach($attachmentFullPath);
                    }

                    Mail::mailer('smtp')->to($employee->personal->email)->send($mail);
                }
            }

            if ($announcement->send_push_notification) {
                $employeeQuery = Employee::query()->where('is_active', 1);

                if (!$announcement->all_employees) {
                    $employeeQuery->whereHas('employment', function ($query) use ($data) {
                        if (!empty($data['branches'])) {
                            $query->whereIn('branch_id', $data['branches']);
                        }
                        if (!empty($data['organizations'])) {
                            $query->whereIn('organization_id', $data['organizations']);
                        }
                        if (!empty($data['job_levels'])) {
                            $query->whereIn('job_level_id', $data['job_levels']);
                        }
                        if (!empty($data['positions'])) {
                            $query->whereIn('job_position_id', $data['positions']);
                        }
                    });
                }

                $targetUserIds = $employeeQuery
                    ->whereNotNull('user_id')
                    ->pluck('user_id');

                $deviceTokens = DB::table('sessions')
                    ->whereIn('user_id', $targetUserIds)
                    ->whereIn('device', ['android'])
                    ->distinct()
                    ->pluck('device_id');

                $notificationData = [
                    "title" => $announcement->title,
                    "body" => "Message from " . auth()->user()->name,
                ];

                foreach ($deviceTokens as $token) {
                    sendMessage($token, $notificationData);
                }
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
    function put($request)
    {
        try {
            $payload = is_array($request)
                ? $request
                : (method_exists($request, 'all') ? $request->all() : (array) $request);

            return DB::transaction(function () use ($payload) {
                $announcement = Announcement::with(['branches', 'organizations', 'jobLevels', 'positions'])->findOrFail($payload['id']);

                $attachmentPath = $announcement->attachment;
                if (!empty($payload['attachment']) && $payload['attachment']->isValid()) {
                    $attachmentPath = $payload['attachment']->store('announcements', 'public');
                }

                $announcement->update([
                    'title' => $payload['title'],
                    'content' => $payload['content'],
                    'link' => $payload['link'] ?? null,
                    'attachment' => $attachmentPath,
                    'category_id' => $payload['category_id'] ?? null,
                    'all_employees' => (bool) ($payload['all_employees'] ?? true),
                    'send_email' => (bool) ($payload['send_email'] ?? false),
                    'send_push_notification' => (bool) ($payload['send_push_notification'] ?? true),
                    'updated_by' => auth()->id(),
                ]);

                if ($announcement->all_employees) {
                    $announcement->branches()->sync([]);
                    $announcement->organizations()->sync([]);
                    $announcement->jobLevels()->sync([]);
                    $announcement->positions()->sync([]);
                } else {
                    $announcement->branches()->sync($payload['branches'] ?? []);
                    $announcement->organizations()->sync($payload['organizations'] ?? []);
                    $announcement->jobLevels()->sync($payload['job_levels'] ?? []);
                    $announcement->positions()->sync($payload['positions'] ?? []);
                }

                return $announcement->load(['category', 'creator', 'updater', 'branches', 'organizations', 'jobLevels', 'positions']);
            });
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function delete($id) {}
}
