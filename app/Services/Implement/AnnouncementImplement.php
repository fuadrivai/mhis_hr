<?php

namespace App\Services\Implement;

use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Support\Facades\DB;

use function App\Helpers\sendMessage;

class AnnouncementImplement implements AnnouncementService
{
    function get($request)
    {
        try {
            $announcements = Announcement::with(['branches', 'levels', 'organizations', 'positions', 'category', 'user'])->get();
            return response()->json($announcements);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function show($id) {}
    function post($request)
    {
        try {
            $announcement = new Announcement();
            $announcement->subject = $request['subject'];
            $announcement->content = $request['content'];
            $announcement->category_id = $request['category']['id'];
            $announcement->user_id = $request['user']['id'];
            $announcement->date = $request['date'];
            $announcement->link = $request['link'];
            $announcement->all_employees = $request['all_employees'];
            $announcement->save();

            $device_token = [];
            if (!$request['all_employees']) {
                $null = !isset($request['branches']) && !isset($request['organizations']) && !isset($request['positions']) && !isset($request['levels']);
                if ($null) {
                    return response()->json(["message" => "Invalid request format"], 400);
                } else {
                    $empty = (count($request['branches']) == 0) && (count($request['organizations']) == 0) && (count($request['positions']) == 0) && (count($request['levels']) == 0);
                    if ($empty) {
                        return response()->json(["message" => "Invalid request format"], 400);
                    }
                }
                $announcement->branches()->sync($request['branches']);
                $announcement->organizations()->sync($request['organizations']);
                $announcement->levels()->sync($request['levels']);
                $announcement->positions()->sync($request['positions']);
            } else {
                $device_token = DB::table('sessions')->where('user_id', '!=', $request['user']['id'])->pluck('device_id');
            }
            $data = [
                "title" => $announcement->subject,
                "body" => "Message from " . $request['user']['name'],
            ];
            for ($i = 0; $i < count($device_token); $i++) {
                $token =  $device_token[$i];
                sendMessage($token, $data);
            }

            $fullData = Announcement::with(['branches', 'levels', 'organizations', 'positions'])->find($announcement->id);
            return response()->json($fullData);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 500);
        }
    }
    function put($request) {}
    function delete($id) {}
}
