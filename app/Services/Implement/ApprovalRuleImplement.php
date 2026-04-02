<?php

namespace App\Services\Implement;

use App\Models\ApprovalRule;
use App\Services\ApprovalRuleService;
use Illuminate\Support\Facades\DB;

class ApprovalRuleImplement implements ApprovalRuleService
{
    public function get($with = [])
    {
        try {
            return ApprovalRule::with($with)->get();
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $rule = ApprovalRule::find($id);

            return $rule;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function post($request)
    {
        try {
            DB ::beginTransaction();

            $rule = new ApprovalRule();
            $rule->name = $request['name'] ?? null;
            $rule->branch_id = $request['branch'] ?? null;
            $rule->organization_id = $request['organization'] ?? null;
            $rule->job_level_id = $request['level'] ?? null;
            $rule->position_id = $request['position'] ?? null;
            $rule->save();

            $steps = collect($request['steps'])->flatMap(function ($step) use ($rule) {
                return collect($step['approvers'])->map(function ($approver) use ($step, $rule) {
                    return [
                        'approval_rule_id' => $rule->id,
                        'name' => $step['name'],
                        'step_order' => $step['index'],
                        'approver_type' => $step['approver_type'] ?? 'employment',
                        'approver_position_id' =>
                            ($step['approver_type'] ?? 'employment') == 'position'
                                ? $approver['employeeId']
                                : null,
                        'approver_employment_id' =>
                            ($step['approver_type'] ?? 'employment') == 'employment'
                                ? $approver['employeeId']
                                : null,
                        'approval_mode' => $step['approval_mode'] ?? 'any',
                    ];
                });
            })->toArray();

            $rule->steps()->insert($steps);

            DB::commit();
            return $rule->load('steps');

        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception($th->getMessage());
        }
    }

    public function put($request)
    {
        try {
            $rule = ApprovalRule::find($request['id']);

            if (!$rule) {
                return response()->json(['message' => 'Approval rule not found'], 404);
            }

            $rule->name = $request['name'] ?? $rule->name;
            $rule->branch_id = $request['branch_id'] ?? $rule->branch_id;
            $rule->organization_id = $request['organization_id'] ?? $rule->organization_id;
            $rule->level_id = $request['level_id'] ?? $rule->level_id;
            $rule->position_id = $request['position_id'] ?? $rule->position_id;
            $rule->save();

            return response()->json($rule);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $rule = ApprovalRule::find($id);

            if (!$rule) {
                return response()->json(['message' => 'Approval rule not found'], 404);
            }

            $rule->delete();
            return response()->json(['message' => 'Approval rule deleted successfully']);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
