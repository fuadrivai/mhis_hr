<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\KpiTemplate;
use App\Models\EmployeeKpi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EmployeeKpiController extends Controller
{
    private function checkEmployeeAccess($employee)
    {
        $user = auth()->user();
        if ($user && $user->roles->contains('id', 3)) {
            if (!$user->employee || !$user->employee->employment || !$employee->employment) {
                abort(403, 'Unauthorized action.');
            }
            if ($user->employee->employment->branch_id != $employee->employment->branch_id ||
                $user->employee->employment->organization_id != $employee->employment->organization_id) {
                abort(403, 'Unauthorized action.');
            }
        }
    }

    public function index($id)
    {
        $employee = Employee::with(['personal', 'employment.job_position', 'kpis.targets.subTargets', 'reprimands'])->findOrFail($id);
        $this->checkEmployeeAccess($employee);
        
        $user = auth()->user();
        $query = KpiTemplate::with('targets.subTargets');
        if ($user && $user->roles->contains('id', 3)) {
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('is_public', 1);
            });
        }
        $templates = $query->get();

        $title = "Employee KPI";
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        
        return view('employee.kpi.index', [
            'data' => $employee,
            'templates' => $templates,
            'title' => $title,
            'activeYear' => $activeYear
        ]);
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'managerial_targets' => 'nullable|array',
            'tal_targets' => 'nullable|array',
        ]);

        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            return redirect()->back()->withErrors(['academic_year' => 'No active Academic Year found. Please configure it in Settings first.']);
        }

        $employee = Employee::with(['personal', 'employment', 'reprimands.reprimandType'])->findOrFail($id);
        $this->checkEmployeeAccess($employee);

        $existingKpi = EmployeeKpi::where('employee_id', $employee->id)->where('academic_year', $activeYear->name)->first();
        if ($existingKpi) {
            return redirect()->back()->withErrors(['academic_year' => 'A KPI for the active Academic Year (' . $activeYear->name . ') already exists for this employee. Please edit the existing KPI instead.']);
        }

        // Calculate reprimand deduction
        $deduction = 0;
        if ($employee->reprimands) {
            foreach($employee->reprimands as $reprimand) {
                $deduction += $reprimand->reprimandType->deduction_score ?? 0;
            }
        }

        $kpi = DB::transaction(function () use ($request, $employee, $deduction, $activeYear) {
            $kpi = EmployeeKpi::create([
                'employee_id' => $employee->id,
                'academic_year' => $activeYear->name,
                'reprimand_deduction_percentage' => $deduction,
            ]);

            // Save Managerial targets
            if ($request->has('managerial_targets')) {
                foreach ($request->managerial_targets as $mTarget) {
                    if(empty($mTarget['name'])) continue;
                    $target = $kpi->targets()->create([
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
                    $kpi->targets()->create([
                        'type' => 'tal',
                        'name' => $tTarget['name'],
                        'target_score' => $tTarget['target_score'] ?? null,
                        'weight' => $tTarget['weight'] ?? null,
                    ]);
                }
            }

            return $kpi;
        });

        // Generate JSON Payload
        $payload = [
            "employee_email" => $employee->personal->email,
            "employee_id" => $employee->employment->employee_id ?? $employee->id,
            "employee_name" => $employee->personal->fullname,
            "academic_year" => $kpi->academic_year,
            "reprimand_deduction_percentage" => (float) $kpi->reprimand_deduction_percentage,
            "managerial_targets" => [],
            "tal_targets" => []
        ];

        foreach($kpi->targets as $target) {
            if ($target->type == 'managerial') {
                $mItem = [
                    "name" => $target->name,
                    "target_score" => (float) $target->target_score,
                    "weight" => (float) $target->weight
                ];
                $subs = $target->subTargets;
                if ($subs->count() > 0) {
                    $mItem['details'] = [];
                    // Calculate missing weights
                    $totalAssignedWeight = $subs->sum('weight');
                    $blanksCount = $subs->whereNull('weight')->count();
                    $remainingWeight = 100 - $totalAssignedWeight;
                    $avgWeight = $blanksCount > 0 ? max(0, $remainingWeight / $blanksCount) : 0;

                    foreach ($subs as $sub) {
                        $mItem['details'][] = [
                            "name" => $sub->name,
                            "target_score" => (float) $sub->target_score,
                            "weight" => $sub->weight !== null ? (float) $sub->weight : (float) $avgWeight
                        ];
                    }
                }
                $payload["managerial_targets"][] = $mItem;
            } elseif ($target->type == 'tal') {
                $payload["tal_targets"][] = [
                    "name" => $target->name,
                    "target_score" => (float) $target->target_score,
                    "weight" => (float) $target->weight
                ];
            }
        }

        // Send to Google Apps Script API
        try {
            $response = Http::post('https://script.google.com/macros/s/AKfycbwqhEWV0CjzHNABEHbHoxx-jnGNj3aNnsbCOj2o5_r2AC3Evt-kJZyMwzBZrlrE5zv7fQ/exec', $payload);
            
            if ($response->successful()) {
                $resData = $response->json();
                if (isset($resData['status']) && $resData['status'] === 'success') {
                    $kpi->update([
                        'managerial_file_url' => $resData['data']['managerial_file_url'] ?? null,
                        'tal_file_url' => $resData['data']['tal_file_url'] ?? null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('KPI Dispatch Error: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'KPI assigned and dispatched to API successfully.');
    }

    public function edit($kpi_id)
    {
        $kpi = EmployeeKpi::with(['targets.subTargets', 'employee.personal', 'employee.employment.job_position'])->findOrFail($kpi_id);
        $this->checkEmployeeAccess($kpi->employee);
        $title = "Edit Employee KPI";
        $data = $kpi->employee;
        
        return view('employee.kpi.edit', compact('kpi', 'title', 'data'));
    }

    public function update(Request $request, $kpi_id)
    {
        $request->validate([
            'managerial_targets' => 'nullable|array',
            'tal_targets' => 'nullable|array',
        ]);

        $kpi = EmployeeKpi::with(['employee.personal', 'employee.employment', 'employee.reprimands.reprimandType', 'targets'])->findOrFail($kpi_id);
        $employee = $kpi->employee;
        $this->checkEmployeeAccess($employee);

        // Calculate reprimand deduction
        $deduction = 0;
        if ($employee->reprimands) {
            foreach($employee->reprimands as $reprimand) {
                $deduction += $reprimand->reprimandType->deduction_score ?? 0;
            }
        }

        $kpi = DB::transaction(function () use ($request, $kpi, $deduction) {
            $kpi->update([
                'reprimand_deduction_percentage' => $deduction,
            ]);

            // Clear old targets
            $kpi->targets()->delete(); // will cascade delete subtargets if DB is setup with cascade, but Laravel eloquent delete() on hasMany doesn't cascade unless specified or handled via DB constraint.
            // Let's explicitly delete subtargets first to be safe if no cascade
            foreach($kpi->targets as $target) {
                $target->subTargets()->delete();
                $target->delete();
            }

            // Save Managerial targets
            if ($request->has('managerial_targets')) {
                foreach ($request->managerial_targets as $mTarget) {
                    if(empty($mTarget['name'])) continue;
                    $target = $kpi->targets()->create([
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
                    $kpi->targets()->create([
                        'type' => 'tal',
                        'name' => $tTarget['name'],
                        'target_score' => $tTarget['target_score'] ?? null,
                        'weight' => $tTarget['weight'] ?? null,
                    ]);
                }
            }

            return $kpi->fresh('targets.subTargets');
        });

        // Generate JSON Payload
        $payload = [
            "employee_email" => $employee->personal->email,
            "employee_id" => $employee->employment->employee_id ?? $employee->id,
            "employee_name" => $employee->personal->fullname,
            "academic_year" => $kpi->academic_year,
            "reprimand_deduction_percentage" => (float) $kpi->reprimand_deduction_percentage,
            "managerial_targets" => [],
            "tal_targets" => []
        ];

        foreach($kpi->targets as $target) {
            if ($target->type == 'managerial') {
                $mItem = [
                    "name" => $target->name,
                    "target_score" => (float) $target->target_score,
                    "weight" => (float) $target->weight
                ];
                $subs = $target->subTargets;
                if ($subs->count() > 0) {
                    $mItem['details'] = [];
                    // Calculate missing weights
                    $totalAssignedWeight = $subs->sum('weight');
                    $blanksCount = $subs->whereNull('weight')->count();
                    $remainingWeight = 100 - $totalAssignedWeight;
                    $avgWeight = $blanksCount > 0 ? max(0, $remainingWeight / $blanksCount) : 0;

                    foreach ($subs as $sub) {
                        $mItem['details'][] = [
                            "name" => $sub->name,
                            "target_score" => (float) $sub->target_score,
                            "weight" => $sub->weight !== null ? (float) $sub->weight : (float) $avgWeight
                        ];
                    }
                }
                $payload["managerial_targets"][] = $mItem;
            } elseif ($target->type == 'tal') {
                $payload["tal_targets"][] = [
                    "name" => $target->name,
                    "target_score" => (float) $target->target_score,
                    "weight" => (float) $target->weight
                ];
            }
        }

        // Send to Google Apps Script API
        try {
            $response = Http::post('https://script.google.com/macros/s/AKfycbwqhEWV0CjzHNABEHbHoxx-jnGNj3aNnsbCOj2o5_r2AC3Evt-kJZyMwzBZrlrE5zv7fQ/exec', $payload);
            
            if ($response->successful()) {
                $resData = $response->json();
                if (isset($resData['status']) && $resData['status'] === 'success') {
                    $kpi->update([
                        'managerial_file_url' => $resData['data']['managerial_file_url'] ?? null,
                        'tal_file_url' => $resData['data']['tal_file_url'] ?? null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('KPI Dispatch Error: ' . $e->getMessage());
        }

        return redirect()->route('employee.kpi.index', $employee->id)->with('success', 'KPI updated and dispatched to API successfully.');
    }

    public function destroy($kpi_id)
    {
        $kpi = EmployeeKpi::with(['targets.subTargets', 'employee.employment'])->findOrFail($kpi_id);
        $this->checkEmployeeAccess($kpi->employee);
        
        DB::transaction(function() use ($kpi) {
            foreach($kpi->targets as $target) {
                $target->subTargets()->delete();
                $target->delete();
            }
            $kpi->delete();
        });

        return redirect()->back()->with('success', 'KPI deleted successfully.');
    }

    public function calculate($kpi_id)
    {
        $kpi = EmployeeKpi::with(['employee.personal', 'employee.employment'])->findOrFail($kpi_id);
        $this->checkEmployeeAccess($kpi->employee);
        
        if (!$kpi->managerial_file_url || !$kpi->tal_file_url) {
            return redirect()->back()->withErrors(['api_links' => 'Managerial or TAL links are missing. Please update the KPI first to generate links.']);
        }

        try {
            $response = Http::get('https://script.google.com/macros/s/AKfycbwqhEWV0CjzHNABEHbHoxx-jnGNj3aNnsbCOj2o5_r2AC3Evt-kJZyMwzBZrlrE5zv7fQ/exec', [
                'managerial_file_url' => $kpi->managerial_file_url,
                'tal_file_url' => $kpi->tal_file_url
            ]);
            
            if ($response->successful()) {
                $resData = $response->json();
                
                if (isset($resData['status']) && $resData['status'] === 'success') {
                    $managerialTargets = $resData['data']['managerial_targets'] ?? [];
                    $talTargets = $resData['data']['tal_targets'] ?? [];
                    
                    // Database Targets
                    $dbManagerialTargets = $kpi->targets()->where('type', 'managerial')->with('subTargets')->get();
                    $dbTalTargets = $kpi->targets()->where('type', 'tal')->get();

                    // 1. Calculate Managerial (Max 100)
                    $managerialScore = 0;
                    $managerialBreakdown = [];
                    foreach ($dbManagerialTargets as $dbTarget) {
                        $actualScore = 0;
                        $apiTargetData = null;
                        
                        // Find matching target in API response
                        foreach ($managerialTargets as $apiTarget) {
                            if ($apiTarget['target_name'] === $dbTarget->name) {
                                $apiTargetData = $apiTarget;
                                break;
                            }
                        }
                        
                        $hasSubTargets = $dbTarget->subTargets->count() > 0;
                        $subBreakdowns = [];

                        if ($hasSubTargets && $apiTargetData && isset($apiTargetData['details'])) {
                            // Calculate missing weights
                            $totalAssignedWeight = $dbTarget->subTargets->sum('weight');
                            $blanksCount = $dbTarget->subTargets->whereNull('weight')->count();
                            $remainingWeight = 100 - $totalAssignedWeight;
                            $avgWeight = $blanksCount > 0 ? max(0, $remainingWeight / $blanksCount) : 0;

                            // Calculate actual score from sub-targets
                            foreach ($dbTarget->subTargets as $dbSub) {
                                $subActual = 0;
                                foreach ($apiTargetData['details'] as $apiSub) {
                                    if ($apiSub['sub_target_name'] === $dbSub->name) {
                                        $subActual = $apiSub['actual_score'] ?? 0;
                                        break;
                                    }
                                }
                                $subTargetScore = $dbSub->target_score > 0 ? $dbSub->target_score : 1;
                                $calculatedWeight = $dbSub->weight !== null ? (float) $dbSub->weight : (float) $avgWeight;
                                $subPoints = ($subActual / $subTargetScore) * $calculatedWeight;
                                $actualScore += $subPoints;

                                $subBreakdowns[] = [
                                    'name' => $dbSub->name,
                                    'target' => $dbSub->target_score,
                                    'weight' => $calculatedWeight,
                                    'actual' => $subActual,
                                    'points' => $subPoints
                                ];
                            }
                        } else {
                            $actualScore = $apiTargetData['actual_score'] ?? 0;
                        }
                        
                        $targetScore = $dbTarget->target_score > 0 ? $dbTarget->target_score : 1; // avoid div by 0
                        $points = ($actualScore / $targetScore) * $dbTarget->weight;
                        $managerialScore += $points;
                        
                        $managerialBreakdown[] = [
                            'name' => $dbTarget->name,
                            'target' => $dbTarget->target_score,
                            'weight' => $dbTarget->weight,
                            'actual' => $actualScore,
                            'points' => $points,
                            'sub_targets' => $subBreakdowns
                        ];
                    }
                    // Limit managerial to max 100 before weighting? The weights sum to 100.
                    $managerialWeighted = $managerialScore * 0.60;

                    // 2. Calculate TAL (Max 100)
                    $talScore = 0;
                    $talBreakdown = [];
                    foreach ($dbTalTargets as $dbTarget) {
                        $actualScore = 0;
                        foreach ($talTargets as $apiTarget) {
                            if ($apiTarget['target_name'] === $dbTarget->name) {
                                $actualScore = $apiTarget['actual_score'] ?? 0;
                                break;
                            }
                        }
                        
                        // All or nothing logic for TAL
                        $points = ($actualScore >= $dbTarget->target_score) ? $dbTarget->weight : 0;
                        $talScore += $points;
                        
                        $talBreakdown[] = [
                            'name' => $dbTarget->name,
                            'target' => $dbTarget->target_score,
                            'weight' => $dbTarget->weight,
                            'actual' => $actualScore,
                            'points' => $points
                        ];
                    }
                    $talWeighted = $talScore * 0.40;

                    // 3. Final Calculation
                    $deduction = $kpi->reprimand_deduction_percentage ?? 0;
                    $finalScore = $managerialWeighted + $talWeighted - $deduction;

                    $title = "Calculate KPI Score";
                    $data = $kpi->employee;

                    return view('employee.kpi.calculate', compact(
                        'kpi', 'title', 'data', 
                        'managerialBreakdown', 'managerialScore', 'managerialWeighted',
                        'talBreakdown', 'talScore', 'talWeighted',
                        'deduction', 'finalScore'
                    ));
                } else {
                    return redirect()->back()->withErrors(['api_error' => 'API returned failure status: ' . json_encode($resData)]);
                }
            } else {
                return redirect()->back()->withErrors(['api_error' => 'Failed to connect to API. Status code: ' . $response->status()]);
            }
        } catch (\Exception $e) {
            \Log::error('KPI Calculation Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['api_error' => 'Exception occurred during calculation: ' . $e->getMessage()]);
        }
    }

    public function saveScore(Request $request, $kpi_id)
    {
        $request->validate([
            'final_score' => 'required|numeric'
        ]);

        $kpi = EmployeeKpi::with('employee.employment')->findOrFail($kpi_id);
        $this->checkEmployeeAccess($kpi->employee);

        $kpi->update([
            'final_score' => $request->final_score
        ]);

        return redirect()->route('employee.kpi.index', $kpi->employee_id)->with('success', 'Final KPI score saved successfully.');
    }
}
