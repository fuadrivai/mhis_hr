<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WhatsappSetting;
use App\Models\WhatsappReplyLog;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class WhatsappChatController extends Controller
{
    public function index()
    {
        $isWhatsappChatter = \App\Models\WhatsappChatter::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        if (!$isWhatsappChatter) {
            abort(403);
        }

        $title = "WhatsApp Chat";
        $tags = \App\Models\WhatsappTag::all();
        return view('whatsapp.chat', compact('title', 'tags'));
    }

    public function monitoring()
    {
        $isWhatsappMonitor = \App\Models\WhatsappMonitor::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        if (!$isWhatsappMonitor) {
            abort(403);
        }

        $title = "WhatsApp Monitoring";
        $logs = WhatsappReplyLog::with('employee.user')->orderBy('created_at', 'desc')->get();

        $stats = $logs->whereNotNull('employee_id')->groupBy('employee_id')->map(function ($group) {
            $employee = $group->first()->employee;
            $name = $employee && $employee->user ? $employee->user->name : 'Unknown';
            $totalReplies = $group->count();
            $uniqueContacts = $group->pluck('contact_number')->unique()->count();
            
            $totalResponseTime = $group->sum('response_time_seconds');
            $repliesWithResponseTime = $group->whereNotNull('response_time_seconds')->count();
            $avgResponseTime = $repliesWithResponseTime > 0 ? $totalResponseTime / $repliesWithResponseTime : 0;
            
            return [
                'name' => $name,
                'total_replies' => $totalReplies,
                'unique_contacts' => $uniqueContacts,
                'avg_response_time' => $avgResponseTime
            ];
        });

        return view('whatsapp.monitoring', compact('logs', 'title', 'stats'));
    }

    public function getContacts()
    {
        $chatter = \App\Models\WhatsappChatter::with('tags')->where('employee_id', auth()->user()->employee->id ?? 0)->first();
        if (!$chatter) {
            return response()->json(['status' => false, 'message' => 'Unauthorized']);
        }

        $setting = WhatsappSetting::first();
        if (!$setting) {
            return response()->json(['status' => false, 'message' => 'WhatsApp settings not configured']);
        }

        $response = Http::get('https://mhisnetshield.us/apiv2/contact.php', [
            'api_key' => $setting->api_key,
            'nomor' => $setting->number
        ]);

        $resJson = $response->json();

        if (isset($resJson['status']) && $resJson['status'] == true && isset($resJson['data'])) {
            $chatterTagIds = $chatter->is_all_tags ? [] : $chatter->tags->pluck('id')->toArray();
            $contactTags = \App\Models\WhatsappContactTag::with('tag')->get()->groupBy('contact_number');
            $contactStates = \App\Models\WhatsappContactState::all()->keyBy('contact_number');

            $filteredData = [];
            foreach ($resJson['data'] as $contact) {
                $number = $contact['number'];
                $ctags = isset($contactTags[$number]) ? $contactTags[$number]->pluck('tag')->toArray() : [];
                $ctagIds = array_column($ctags, 'id');

                // Filter by chatter tags if not "All"
                if (!$chatter->is_all_tags) {
                    if (empty(array_intersect($chatterTagIds, $ctagIds))) {
                        continue;
                    }
                }

                $contact['tags'] = $ctags;

                // Red dot logic: cached in WhatsappContactState
                $contact['unread'] = false;
                $contact['check_unread'] = false;
                
                if (isset($contact['last_msg_timestamp'])) {
                    $ts = (string)$contact['last_msg_timestamp'];
                    $state = $contactStates[$number] ?? null;
                    
                    if ($state && $state->api_last_msg_timestamp === $ts) {
                        $contact['unread'] = (bool)$state->is_unread;
                    } else {
                        $contact['check_unread'] = true;
                    }
                }

                $filteredData[] = $contact;
            }
            $resJson['data'] = $filteredData;
        }

        return response()->json($resJson);
    }

    public function getMessages(Request $request)
    {
        $isWhatsappChatter = \App\Models\WhatsappChatter::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        if (!$isWhatsappChatter) {
            return response()->json(['status' => false, 'message' => 'Unauthorized']);
        }

        $contactNumber = $request->query('contact_number');
        $setting = WhatsappSetting::first();
        if (!$setting) {
            return response()->json(['status' => false, 'message' => 'WhatsApp settings not configured']);
        }

        $response = Http::get('https://mhisnetshield.us/apiv2/get_message.php', [
            'api_key' => $setting->api_key,
            'nomor' => $setting->number,
            'm_from' => $contactNumber
        ]);

        $resJson = $response->json();

        if (isset($resJson['status']) && $resJson['status'] == true && isset($resJson['data'])) {
            $logs = WhatsappReplyLog::where('contact_number', $contactNumber)
                        ->with('employee.user')
                        ->get();

            foreach ($resJson['data'] as &$msg) {
                if (isset($msg['from_me']) && ($msg['from_me'] === "true" || $msg['from_me'] === true)) {
                    $matchedLogKey = $logs->search(function($log) use ($msg) {
                        return $log->message == $msg['message'];
                    });
                    
                    if ($matchedLogKey !== false) {
                        $matchedLog = $logs[$matchedLogKey];
                        if ($matchedLog->employee && $matchedLog->employee->user) {
                            $msg['employee_name'] = $matchedLog->employee->user->name;
                        } else {
                            $msg['employee_name'] = 'System';
                        }
                        $logs->forget($matchedLogKey);
                    } else {
                        $msg['employee_name'] = 'System';
                    }
                }
            }
        }

        return response()->json($resJson);
    }

    public function sendMessage(Request $request)
    {
        $isWhatsappChatter = \App\Models\WhatsappChatter::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        if (!$isWhatsappChatter) {
            return response()->json(['status' => false, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'number' => 'required',
        ]);

        $setting = WhatsappSetting::first();
        if (!$setting) {
            return response()->json(['status' => false, 'message' => 'WhatsApp settings not configured']);
        }

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/whatsapp_media'), $filename);
            
            $url = asset('uploads/whatsapp_media/' . $filename);
            $caption = $request->message ?? '';
            
            $response = Http::asForm()->post('https://mhisnetshield.us/apiv2/send-media.php', [
                'api_key' => $setting->api_key,
                'sender' => $setting->number,
                'number' => $request->number,
                'caption' => $caption,
                'url' => $url,
                'ex' => strtolower($file->getClientOriginalExtension()),
                'filename' => $filename
            ]);
            
            $resJson = $response->json();
            
            if (isset($resJson['status']) && $resJson['status'] == true) {
                // Find the last incoming message timestamp
                $incomingMsgTs = null;
                $contactState = \App\Models\WhatsappContactState::where('contact_number', $request->number)->first();
                if ($contactState && $contactState->api_last_msg_timestamp) {
                    $alreadyReplied = WhatsappReplyLog::where('contact_number', $request->number)
                        ->where('incoming_msg_timestamp', (string)$contactState->api_last_msg_timestamp)
                        ->exists();
                    
                    if (!$alreadyReplied) {
                        $incomingMsgTs = $contactState->api_last_msg_timestamp;
                    }
                }

                $responseTime = $incomingMsgTs ? $this->calculateWorkingTimeInSeconds($incomingMsgTs, time(), $setting) : null;

                WhatsappReplyLog::create([
                    'employee_id' => auth()->user()->employee->id ?? null,
                    'contact_number' => $request->number,
                    'message' => '[Media: ' . $filename . '] ' . $caption,
                    'response_time_seconds' => $responseTime,
                    'incoming_msg_timestamp' => $incomingMsgTs,
                ]);
                
                // mark as read
                if ($contactState) {
                    $contactState->update(['is_unread' => false]);
                }
            }
            
            return $resJson;
        }

        // Standard text message
        if (empty($request->message)) {
            return response()->json(['status' => false, 'message' => 'Message is required if no media is attached']);
        }

        $response = Http::asForm()->post('https://mhisnetshield.us/apiv2/send-message.php', [
            'api_key' => $setting->api_key,
            'sender' => $setting->number,
            'number' => $request->number,
            'message' => $request->message
        ]);

        $resJson = $response->json();

        // If sent successfully, log it
        if (isset($resJson['status']) && $resJson['status'] == true) {
            // Find the last incoming message timestamp
            $incomingMsgTs = null;
            $contactState = \App\Models\WhatsappContactState::where('contact_number', $request->number)->first();
            if ($contactState && $contactState->api_last_msg_timestamp) {
                $alreadyReplied = WhatsappReplyLog::where('contact_number', $request->number)
                    ->where('incoming_msg_timestamp', (string)$contactState->api_last_msg_timestamp)
                    ->exists();
                
                if (!$alreadyReplied) {
                    $incomingMsgTs = $contactState->api_last_msg_timestamp;
                }
            }

            $responseTime = $incomingMsgTs ? $this->calculateWorkingTimeInSeconds($incomingMsgTs, time(), $setting) : null;

            WhatsappReplyLog::create([
                'employee_id' => auth()->user()->employee->id ?? null,
                'contact_number' => $request->number,
                'message' => $request->message,
                'response_time_seconds' => $responseTime,
                'incoming_msg_timestamp' => $incomingMsgTs,
            ]);
            
            // mark as read
            if ($contactState) {
                $contactState->update(['is_unread' => false]);
            }
        }

        return $resJson;
    }

    private function calculateWorkingTimeInSeconds($startTs, $endTs, $setting)
    {
        if (!$startTs || !$endTs) return null;
        
        $startTsInt = (int)$startTs;
        if (strlen((string)$startTsInt) > 11) {
            $startTsInt = (int)($startTsInt / 1000);
        }
        $endTsInt = (int)$endTs;

        if ($endTsInt < $startTsInt) return 0;
        
        $start = Carbon::createFromTimestamp($startTsInt);
        $end = Carbon::createFromTimestamp($endTsInt);

        if (!$setting->working_hour_start || !$setting->working_hour_end || empty($setting->working_days)) {
            return $end->diffInSeconds($start);
        }

        $workingDays = is_array($setting->working_days) ? $setting->working_days : json_decode($setting->working_days, true) ?? [];
        if (empty($workingDays)) {
             return $end->diffInSeconds($start);
        }
        
        $workStart = Carbon::parse($setting->working_hour_start)->format('H:i:s');
        $workEnd = Carbon::parse($setting->working_hour_end)->format('H:i:s');
        
        $totalSeconds = 0;
        $current = $start->copy();

        while ($current->lt($end)) {
            $isWorkingDay = in_array($current->dayOfWeek, $workingDays);
            
            $dayStart = $current->copy()->setTimeFromTimeString($workStart);
            $dayEnd = $current->copy()->setTimeFromTimeString($workEnd);
            
            if (!$isWorkingDay) {
                $current->addDay()->setTimeFromTimeString($workStart);
                continue;
            }

            if ($current->lt($dayStart)) {
                $current = $dayStart->copy();
            }

            if ($current->gte($dayEnd)) {
                $current->addDay()->setTimeFromTimeString($workStart);
                continue;
            }

            $timeToAddEnd = $end->lt($dayEnd) ? $end : $dayEnd;
            $seconds = $timeToAddEnd->diffInSeconds($current);
            $totalSeconds += $seconds;
            
            $current = $timeToAddEnd->copy();
            if ($current->eq($dayEnd)) {
                $current->addDay()->setTimeFromTimeString($workStart);
            }
        }

        return $totalSeconds;
    }

    public function setContactTag(Request $request)
    {
        $isWhatsappChatter = \App\Models\WhatsappChatter::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        if (!$isWhatsappChatter) {
            return response()->json(['status' => false, 'message' => 'Unauthorized']);
        }

        $request->validate([
            'number' => 'required',
            'tags' => 'array'
        ]);
        
        $tags = $request->input('tags', []);
        
        \App\Models\WhatsappContactTag::where('contact_number', $request->number)->delete();
        foreach ($tags as $tagId) {
            \App\Models\WhatsappContactTag::create([
                'contact_number' => $request->number,
                'whatsapp_tag_id' => $tagId
            ]);
        }
        
        return response()->json(['status' => true, 'message' => 'Tags updated successfully']);
    }

    public function checkUnread(Request $request)
    {
        $number = $request->input('number');
        $ts = $request->input('ts');
        
        $setting = WhatsappSetting::first();
        if (!$setting || !$number) return response()->json(['status' => false]);
        
        $response = Http::get('https://mhisnetshield.us/apiv2/get_message.php', [
            'api_key' => $setting->api_key,
            'nomor' => $setting->number,
            'm_from' => $number
        ]);
        
        $resJson = $response->json();
        $unread = false;
        
        if (isset($resJson['status']) && $resJson['status'] == true && isset($resJson['data'])) {
            $messages = $resJson['data'];
            if (count($messages) > 0) {
                $lastMsg = end($messages);
                if (!isset($lastMsg['from_me']) || ($lastMsg['from_me'] !== "true" && $lastMsg['from_me'] !== true)) {
                    $unread = true;
                }
            }
        }
        
        \App\Models\WhatsappContactState::updateOrCreate(
            ['contact_number' => $number],
            ['api_last_msg_timestamp' => $ts, 'is_unread' => $unread]
        );
        
        return response()->json(['status' => true, 'unread' => $unread]);
    }

    public function markRead(Request $request)
    {
        $number = $request->input('number');
        if ($number) {
            \App\Models\WhatsappContactState::where('contact_number', $number)->update(['is_unread' => false]);
        }
        return response()->json(['status' => true]);
    }
}
