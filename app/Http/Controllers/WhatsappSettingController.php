<?php

namespace App\Http\Controllers;

use App\Models\WhatsappSetting;
use App\Models\WhatsappMonitor;
use App\Models\WhatsappChatter;
use App\Models\Employee;
use App\Models\WhatsappTag;
use Illuminate\Http\Request;

class WhatsappSettingController extends Controller
{
    public function index()
    {
        $setting = WhatsappSetting::first();
        $title = "WhatsApp Settings";
        $employees = Employee::with('user')->get();
        $monitors = WhatsappMonitor::with('employee.user')->get();
        $chatters = WhatsappChatter::with('employee.user', 'tags')->get();
        $tags = WhatsappTag::all();
        return view('settings.whatsapp.index', compact('setting', 'title', 'employees', 'monitors', 'chatters', 'tags'));
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
        $chatter = WhatsappChatter::firstOrCreate(['employee_id' => $request->employee_id]);
        
        $is_all_tags = $request->has('is_all_tags');
        $chatter->update(['is_all_tags' => $is_all_tags]);
        
        if (!$is_all_tags && $request->has('tags')) {
            $chatter->tags()->sync($request->tags);
        } else {
            $chatter->tags()->detach();
        }
        
        return redirect()->back()->with('success', 'Chatter configured successfully');
    }

    public function destroyChatter($id)
    {
        WhatsappChatter::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Chatter removed successfully');
    }

    public function storeTag(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'required|string|max:50',
        ]);

        WhatsappTag::create($request->only('name', 'color_code'));
        return redirect()->back()->with('success', 'Tag created successfully');
    }

    public function updateTag(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'required|string|max:50',
        ]);

        $tag = WhatsappTag::findOrFail($id);
        $tag->update($request->only('name', 'color_code'));
        return redirect()->back()->with('success', 'Tag updated successfully');
    }

    public function destroyTag($id)
    {
        WhatsappTag::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Tag deleted successfully');
    }
}
