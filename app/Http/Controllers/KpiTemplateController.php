<?php

namespace App\Http\Controllers;

use App\Models\KpiTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiTemplateController extends Controller
{
    public function index()
    {
        $title = "KPI Templates";
        $user = auth()->user();
        
        $query = KpiTemplate::with('targets.subTargets');
        if ($user->roles->contains('id', 3)) {
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('is_public', 1);
            });
        }
        $templates = $query->get();
        
        return view('settings.kpi_template.index', compact('templates', 'title'));
    }

    public function create()
    {
        $title = "Create KPI Template";
        return view('settings.kpi_template.create', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'managerial_targets' => 'nullable|array',
            'tal_targets' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request) {
            $template = KpiTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => auth()->id(),
                'is_public' => $request->has('is_public') ? 1 : 0,
            ]);

            // Save Managerial targets
            if ($request->has('managerial_targets')) {
                foreach ($request->managerial_targets as $mTarget) {
                    if(empty($mTarget['name'])) continue;
                    $target = $template->targets()->create([
                        'type' => 'managerial',
                        'name' => $mTarget['name'],
                        'target_score' => $mTarget['target_score'] ?? null,
                        'weight' => $mTarget['weight'] ?? null,
                    ]);

                    if (isset($mTarget['sub_targets'])) {
                        foreach ($mTarget['sub_targets'] as $sub) {
                            if (!empty($sub['name'])) {
                                $target->subTargets()->create([
                                    'name' => $sub['name'],
                                    'target_score' => $sub['target_score'] ?? null,
                                    'weight' => $sub['weight'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            // Save TAL targets
            if ($request->has('tal_targets')) {
                foreach ($request->tal_targets as $tTarget) {
                    if(empty($tTarget['name'])) continue;
                    $template->targets()->create([
                        'type' => 'tal',
                        'name' => $tTarget['name'],
                        'target_score' => $tTarget['target_score'] ?? null,
                        'weight' => $tTarget['weight'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('kpi-template.index')->with('success', 'KPI Template created successfully.');
    }

    public function show(KpiTemplate $kpi_template)
    {
        $user = auth()->user();
        if ($user->roles->contains('id', 3) && $kpi_template->created_by != $user->id && !$kpi_template->is_public) {
            abort(403, 'Unauthorized action.');
        }

        $title = "View KPI Template";
        $kpi_template->load('targets.subTargets');
        return view('settings.kpi_template.show', compact('kpi_template', 'title'));
    }

    public function edit(KpiTemplate $kpi_template)
    {
        $user = auth()->user();
        if ($user->roles->contains('id', 3) && $kpi_template->created_by != $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $title = "Edit KPI Template";
        $kpi_template->load('targets.subTargets');
        return view('settings.kpi_template.edit', compact('kpi_template', 'title'));
    }

    public function update(Request $request, KpiTemplate $kpi_template)
    {
        $user = auth()->user();
        if ($user->roles->contains('id', 3) && $kpi_template->created_by != $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'managerial_targets' => 'nullable|array',
            'tal_targets' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request, $kpi_template) {
            $kpi_template->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_public' => $request->has('is_public') ? 1 : 0,
            ]);

            // Delete old targets and recreate
            $kpi_template->targets()->delete();

            // Save Managerial targets
            if ($request->has('managerial_targets')) {
                foreach ($request->managerial_targets as $mTarget) {
                    if(empty($mTarget['name'])) continue;
                    $target = $kpi_template->targets()->create([
                        'type' => 'managerial',
                        'name' => $mTarget['name'],
                        'target_score' => $mTarget['target_score'] ?? null,
                        'weight' => $mTarget['weight'] ?? null,
                    ]);

                    if (isset($mTarget['sub_targets'])) {
                        foreach ($mTarget['sub_targets'] as $sub) {
                            if (!empty($sub['name'])) {
                                $target->subTargets()->create([
                                    'name' => $sub['name'],
                                    'target_score' => $sub['target_score'] ?? null,
                                    'weight' => $sub['weight'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            // Save TAL targets
            if ($request->has('tal_targets')) {
                foreach ($request->tal_targets as $tTarget) {
                    if(empty($tTarget['name'])) continue;
                    $kpi_template->targets()->create([
                        'type' => 'tal',
                        'name' => $tTarget['name'],
                        'target_score' => $tTarget['target_score'] ?? null,
                        'weight' => $tTarget['weight'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('kpi-template.index')->with('success', 'KPI Template updated successfully.');
    }

    public function destroy(KpiTemplate $kpi_template)
    {
        $user = auth()->user();
        if ($user->roles->contains('id', 3) && $kpi_template->created_by != $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $kpi_template->delete();
        return redirect()->route('kpi-template.index')->with('success', 'KPI Template deleted successfully.');
    }

    public function copy(KpiTemplate $kpi_template)
    {
        $user = auth()->user();
        if ($user->roles->contains('id', 3) && $kpi_template->created_by != $user->id && !$kpi_template->is_public) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($kpi_template) {
            $newTemplate = $kpi_template->replicate();
            $newTemplate->name = $newTemplate->name . ' (Copy)';
            $newTemplate->created_by = auth()->id();
            $newTemplate->is_public = 0;
            $newTemplate->save();

            $kpi_template->load('targets.subTargets');
            foreach ($kpi_template->targets as $target) {
                $newTarget = $target->replicate();
                $newTarget->kpi_template_id = $newTemplate->id;
                $newTarget->save();

                foreach ($target->subTargets as $sub) {
                    $newSub = $sub->replicate();
                    $newSub->kpi_template_target_id = $newTarget->id;
                    $newSub->save();
                }
            }
        });

        return redirect()->route('kpi-template.index')->with('success', 'KPI Template copied successfully.');
    }
}
