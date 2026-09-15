<?php

namespace App\Http\Controllers;

use App\Models\WhatsappSetting;
use App\Models\WhatsappMonitor;
use App\Models\WhatsappChatter;
use App\Models\Employee;
use Illuminate\Http\Request;

class WhatsappSettingController extends Controller
{
    public function index()
    {
        $setting = WhatsappSetting::first();
        $title = "WhatsApp Settings";
        $employees = Employee::with('user')->get();
        $monitors = WhatsappMonitor::with('employee.user')->get();
        $chatters = WhatsappChatter::with('employee.user')->get();
        return view('settings.whatsapp.index', compact('setting', 'title', 'employees', 'monitors', 'chatters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'api_key' => 'required',
            'number' => 'required',
        ]);

        $setting = WhatsappSetting::first();
        if ($setting) {
            $setting->update($request->only('api_key', 'number'));
        } else {
            WhatsappSetting::create($request->only('api_key', 'number'));
        }

        return redirect()->back()->with('success', 'WhatsApp Settings updated successfully');
    }

    public function storeMonitor(Request $request)
    {
        $request->validate(['employee_id' => 'required']);
        WhatsappMonitor::firstOrCreate(['employee_id' => $request->employee_id]);
        return redirect()->back()->with('success', 'Monitor added successfully');
    }

    public function destroyMonitor($id)
    {
        WhatsappMonitor::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Monitor removed successfully');
    }

    public function storeChatter(Request $request)
    {
        $request->validate(['employee_id' => 'required']);
        WhatsappChatter::firstOrCreate(['employee_id' => $request->employee_id]);
        return redirect()->back()->with('success', 'Chatter added successfully');
    }

    public function destroyChatter($id)
    {
        WhatsappChatter::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Chatter removed successfully');
    }
}
