<?php
namespace App\Services;

use App\Models\Request;
use App\Models\ApprovalRule;
use App\Models\Branch;
use App\Models\RequestApproval;
use App\Models\Employment;
use Illuminate\Support\Collection;

class ApprovalEngine
{
    public function generate($request)
    {
        $employment = $request->employment;

        if ($this->isSupportStaff($employment)) {
            return $this->handleSupportStaff($request, $employment);
        }

        $rule = $this->findRule($employment);

        if (!$rule) {
            return $this->fallbackToManager($request, $employment);
        }

        $steps = $this->resolveSteps($rule, $employment);

        $this->storeSteps($request, $steps);

        return $steps;
    }

    protected function findRule($employment)
    {
        return ApprovalRule::with('steps')
            ->where('branch_id', $employment->branch_id)
            ->where(function ($q) use ($employment) {
                $q->whereNull('organization_id')
                ->orWhere('organization_id', $employment->organization_id);
            })
            ->where('level', $employment->level_id)
            ->where(function ($q) use ($employment) {
                $q->whereNull('job_position_id')
                ->orWhere('job_position_id', $employment->job_position_id);
            })
            ->first();
    }

    protected function resolveSteps($rule, $employment): Collection
    {
        $results = collect();

        foreach ($rule->steps as $step) {

            $approvers = collect();

            // 🔹 BY POSITION
            if ($step->approver_type === 'position') {

                $branchId = $this->resolveBranch($employment, $step);

                $approvers = Employment::where('job_position_id', $step->approver_position_id)
                    ->where('branch_id', $branchId)
                    ->where('is_active', true)
                    ->get();
            }

            // 🔹 BY EMPLOYMENT
            if ($step->approver_type === 'employment') {

                $approver = Employment::find($step->approver_employment_id);

                if ($approver && $approver->is_active) {
                    $approvers->push($approver);
                }
            }

            // 🔥 remove self approval
            $approvers = $approvers->filter(fn($a) => $a->id !== $employment->id);

            // 🔥 HRD special filter
            $approvers = $this->filterHRD($approvers, $employment);

            foreach ($approvers as $approver) {
                $results->push([
                    'step_order' => $step->step_order,
                    'approval_mode' => $step->approval_mode,
                    'approver_employment_id' => $approver->id,
                ]);
            }
        }

        return $results;
    }

    protected function resolveBranch($employment, $step)
    {
        $positionName = strtolower(optional($step->approverPosition)->name);

        // Chief → selalu pusat
        if (str_contains($positionName, 'chief')) {
            return $this->getPusatBranchId();
        }

        // HRD pusat
        if (str_contains($positionName, 'hrd pusat')) {
            return $this->getPusatBranchId();
        }

        return $employment->branch_id;
    }

    protected function filterHRD($approvers, $employment)
    {
        return $approvers->filter(function ($a) use ($employment) {

            // ❌ HRD tidak boleh approve dirinya sendiri
            if ($a->is_hrd && $a->id === $employment->id) {
                return false;
            }

            return true;
        });
    }

    protected function isSupportStaff($employment)
    {
        return $employment->level->name === 'Support Staff';
    }

    protected function handleSupportStaff($request, $employment)
    {
        // cari GA
        $ga = Employment::whereHas('position', function ($q) {
            $q->where('name', 'GA Staff');
        })->where('branch_id', $employment->branch_id)->first();

        if (!$ga) return;

        // RequestApproval::create([
        //     'request_id' => $request->id,
        //     'step_order' => 1,
        //     'approver_employment_id' => $ga->id,
        // ]);

        // next: HRD cabang (auto generate step berikutnya)
    }

    protected function fallbackToManager($request, $employment)
    {
        if (!$employment->reports_to) return;

        // RequestApproval::create([
        //     'request_id' => $request->id,
        //     'step_order' => 1,
        //     'approver_employment_id' => $employment->reports_to,
        // ]);
    }

    protected function storeSteps($request, $steps)
    {
        foreach ($steps as $step) {
            // RequestApproval::create([
            //     'request_id' => $request->id,
            //     'step_order' => $step['step_order'],
            //     'approver_employment_id' => $step['approver_employment_id'],
            //     'status' => 'pending'
            // ]);
        }
    }

    public function approve($approvalId)
    {
        // $approval = RequestApproval::findOrFail($approvalId);

        // $approval->update([
        //     'status' => 'approved',
        //     'approved_at' => now()
        // ]);

        // $sameStep = RequestApproval::where('request_id', $approval->request_id)
        //     ->where('step_order', $approval->step_order)
        //     ->get();

        // $mode = $approval->approval_mode ?? 'any';

        // if ($mode === 'any') {
        //     // auto approve semua
        //     foreach ($sameStep as $item) {
        //         if ($item->id !== $approval->id) {
        //             $item->update(['status' => 'approved']);
        //         }
        //     }
        // }

        // if ($mode === 'all') {
        //     $allApproved = $sameStep->every(fn($i) => $i->status === 'approved');

        //     if (!$allApproved) return;
        // }

        // lanjut ke step berikutnya (optional trigger)
    }


    protected function getPusatBranchId()
    {
        return Branch::where('code', 'HO')->value('id');
    }
}
