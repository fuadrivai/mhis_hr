<?php

namespace App\Services\Implement;

use App\Models\AcademicYear;
use App\Models\LeaveAllocation;
use App\Models\LeaveAllocationHistory;
use App\Models\TimeOff;
use App\Services\TimeOffService;
use Illuminate\Support\Facades\DB;

class TimeOffImplement implements TimeOffService
{
    function get()
    {
        try {
            return TimeOff::all();
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function show($id)
    {
        try {
            return TimeOff::find($id);
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }
    function post($request)
    {
        DB::beginTransaction();
        try {
            // Create new TimeOff record
            $timeOff = TimeOff::create([
                'code' => $request['code'],
                'name' => $request['name'],
                'schema' => $request['schema'] ? json_decode($request['schema'], true) : null,
                'is_active' => true,
                'is_global' => $request->boolean('is_global'),
                'deduct_leave_balance' => $request->boolean('deduct_leave_balance')
            ]);

            DB::commit();
            return $timeOff;
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(["message" => $th->getMessage()], $th->getCode() ?: 500);
        }
    }
    function employeeAssignment($request)
    {
        return DB::transaction(function () use ($request) {

            $timeoff = TimeOff::findOrFail($request->timeoff_id);

            $academicYear = AcademicYear::findOrFail(
                $request->academic_year_id
            );

            $employeeIds = collect($request->employees)
                ->unique()
                ->values()
                ->toArray();

            $leaveBalance = (int) $request->leave_balance;

            // 1. Mapping Employee ke Timeoff
            $timeoff->employees()->sync($employeeIds);

            // 2. Jika tidak menggunakan saldo cuti
            if (!$timeoff->deduct_leave_balance) {
                return true;
            }
            
            // 3. Hanya buat allocation untuk employee baru
            foreach ($employeeIds as $employeeId) {

                $allocation = LeaveAllocation::firstOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'timeoff_id' => $timeoff->id,
                        'academic_year_id' => $academicYear->id,
                    ],
                    [
                        'total' => $leaveBalance,
                        'used' => 0,
                        'remaining' => $leaveBalance,
                    ]
                );

                // 4. History hanya dibuat saat allocation baru
                if ($allocation->wasRecentlyCreated) {

                    LeaveAllocationHistory::create([
                        'leave_allocation_id' => $allocation->id,
                        'type' => 'allocated',
                        'days' => $leaveBalance,
                        'remark' => 'Initial leave allocation',
                    ]);
                }
            }

            return true;
        });
    }

    function put($request)
    {
        DB::beginTransaction();
        try {
            $timeOff = TimeOff::find($request['id']);
            if (!$timeOff) {
                return response()->json(["message" => "TimeOff not found"], 404);
            }

            $timeOff->update([
                'code' => $request['code'],
                'name' => $request['name'],
                'schema' => isset($request['schema']) ? json_decode($request['schema'], true) : $timeOff->schema,
                'is_active' => $request['is_active'] ?? $timeOff->is_active,
                'is_global' => $request->boolean('is_global'),
                'deduct_leave_balance' => $request->boolean('deduct_leave_balance')
            ]);

            DB::commit();
            return $timeOff;
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(["message" => $th->getMessage()], $th->getCode() ?: 500);
        }
    }
    

    function delete($id)
    {
        try {
            $timeOff = TimeOff::find($id);

            if (!$timeOff) {
                return response()->json([
                    "message" => "Data not found"
                ], 404);
            }
            $timeOff->delete();
            return true;
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage()
            ], 500);
        }
    }
}
