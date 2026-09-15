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
        return view('whatsapp.chat', compact('title'));
    }

    public function monitoring()
    {
        $isWhatsappMonitor = \App\Models\WhatsappMonitor::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        if (!$isWhatsappMonitor) {
            abort(403);
        }

        $title = "WhatsApp Monitoring";
        $logs = WhatsappReplyLog::with('employee.user')->orderBy('created_at', 'desc')->get();
        return view('whatsapp.monitoring', compact('logs', 'title'));
    }

    public function getContacts()
    {
        $isWhatsappChatter = \App\Models\WhatsappChatter::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        if (!$isWhatsappChatter) {
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

        return $response->json();
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
            'message' => 'required'
        ]);

        $setting = WhatsappSetting::first();
        if (!$setting) {
            return response()->json(['status' => false, 'message' => 'WhatsApp settings not configured']);
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
            WhatsappReplyLog::create([
                'employee_id' => auth()->user()->employee->id ?? null,
                'contact_number' => $request->number,
                'message' => $request->message
            ]);
        }

        return $resJson;
    }
}
