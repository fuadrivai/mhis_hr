<?php

namespace App\Http\Controllers;

use App\Models\RejectionReason;
use Illuminate\Http\Request;

class RecruitmentRejectionReasonController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !$user->canAccessRecruitment()) {
                abort(403, 'Unauthorized access to recruitment settings.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $reasons = RejectionReason::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();

        return view('recruitment.settings.rejection-reasons.index', [
            'title' => 'Master Rejection Reasons',
            'reasons' => $reasons,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rejection_reasons,name',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        RejectionReason::create([
            'name' => trim($validated['name']),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($request->has('is_active') ? $request->boolean('is_active') : true),
        ]);

        return redirect()->route('recruitment.settings.rejection-reasons.index')
            ->with('success', 'Rejection reason created successfully.');
    }

    public function update(Request $request, RejectionReason $reason)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rejection_reasons,name,' . $reason->id,
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $reason->update([
            'name' => trim($validated['name']),
            'sort_order' => (int) ($validated['sort_order'] ?? $reason->sort_order),
            'is_active' => (bool) ($request->has('is_active') ? $request->boolean('is_active') : $reason->is_active),
        ]);

        return redirect()->route('recruitment.settings.rejection-reasons.index')
            ->with('success', 'Rejection reason updated successfully.');
    }

    public function toggleActive(RejectionReason $reason)
    {
        $reason->update(['is_active' => !$reason->is_active]);

        return redirect()->route('recruitment.settings.rejection-reasons.index')
            ->with('success', "Status for '{$reason->name}' updated successfully.");
    }

    public function destroy(RejectionReason $reason)
    {
        if ($reason->rejections()->exists()) {
            return redirect()->route('recruitment.settings.rejection-reasons.index')
                ->with('error', 'Cannot delete rejection reason because it is referenced in candidate rejections. Deactivate it instead.');
        }

        $reason->delete();

        return redirect()->route('recruitment.settings.rejection-reasons.index')
            ->with('success', 'Rejection reason deleted successfully.');
    }
}

