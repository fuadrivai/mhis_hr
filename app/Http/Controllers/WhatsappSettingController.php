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
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        $isWhatsappMonitor = \App\Models\WhatsappMonitor::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        abort_if(!$isAdmin && !$isWhatsappMonitor, 403);

        $setting = WhatsappSetting::first();
        $title = "WhatsApp Settings";
        $employees = Employee::with('user')->get();
        $monitors = WhatsappMonitor::with('employee.user')->get();
        $chatters = WhatsappChatter::with('employee.user', 'tags')->get();
        $accounts = \App\Models\WhatsappAccount::all();
        $tags = WhatsappTag::all();
        return view('settings.whatsapp.index', compact('setting', 'title', 'employees', 'monitors', 'chatters', 'tags', 'accounts'));
    }

    public function store(Request $request)
    {
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        $isWhatsappMonitor = \App\Models\WhatsappMonitor::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        abort_if(!$isAdmin && !$isWhatsappMonitor, 403);

        $rules = [
            'working_hour_start' => 'nullable|date_format:H:i',
            'working_hour_end' => 'nullable|date_format:H:i',
            'working_days' => 'nullable|array',
        ];

        $request->validate($rules);

        $data = $request->only('working_hour_start', 'working_hour_end', 'working_days');
        
        $setting = WhatsappSetting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            WhatsappSetting::create($data);
        }

        return redirect()->back()->with('success', 'WhatsApp Settings updated successfully');
    }

    public function storeAccount(Request $request)
    {
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        abort_if(!$isAdmin, 403);
        $request->validate([
            'name' => 'nullable|string',
            'api_key' => 'required|string',
            'number' => 'required|string',
        ]);
        \App\Models\WhatsappAccount::create($request->only('name', 'api_key', 'number'));
        return redirect()->back()->with('success', 'WhatsApp Account added successfully');
    }

    public function destroyAccount($id)
    {
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        abort_if(!$isAdmin, 403);
        \App\Models\WhatsappAccount::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'WhatsApp Account removed successfully');
    }

    public function storeMonitor(Request $request)
    {
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        abort_if(!$isAdmin, 403);
        $request->validate(['employee_id' => 'required']);
        WhatsappMonitor::firstOrCreate(['employee_id' => $request->employee_id]);
        return redirect()->back()->with('success', 'Monitor added successfully');
    }

    public function destroyMonitor($id)
    {
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        abort_if(!$isAdmin, 403);
        WhatsappMonitor::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Monitor removed successfully');
    }

    public function storeChatter(Request $request)
    {
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        abort_if(!$isAdmin, 403);
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
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        abort_if(!$isAdmin, 403);
        WhatsappChatter::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Chatter removed successfully');
    }

    public function storeTag(Request $request)
    {
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        $isWhatsappMonitor = \App\Models\WhatsappMonitor::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        abort_if(!$isAdmin && !$isWhatsappMonitor, 403);
        $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'required|string|max:50',
        ]);

        WhatsappTag::create($request->only('name', 'color_code'));
        return redirect()->back()->with('success', 'Tag created successfully');
    }

    public function updateTag(Request $request, $id)
    {
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        $isWhatsappMonitor = \App\Models\WhatsappMonitor::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        abort_if(!$isAdmin && !$isWhatsappMonitor, 403);
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
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->roles->contains('id', 1);
        $isWhatsappMonitor = \App\Models\WhatsappMonitor::where('employee_id', auth()->user()->employee->id ?? 0)->exists();
        abort_if(!$isAdmin && !$isWhatsappMonitor, 403);
        WhatsappTag::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Tag deleted successfully');
    }
}
