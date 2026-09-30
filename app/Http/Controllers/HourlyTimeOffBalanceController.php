<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeOff;
use App\Models\TimeoffBalanceGroup;
use App\Services\HourlyTimeOffBalanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HourlyTimeOffBalanceController extends Controller
{
    private HourlyTimeOffBalanceService $balanceService;

    public function __construct(HourlyTimeOffBalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    public function index()
    {
        $this->authorizeConfiguration();

        return view('settings.time.hourly-time-off.index', [
            'title' => 'Hourly Time Off',
            'groups' => $this->balanceService->getGroups(),
        ]);
    }

    public function create()
    {
        $this->authorizeConfiguration();

        return view('settings.time.hourly-time-off.form', [
            'title' => 'Create Balance Group',
            'timeoffs' => TimeOff::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeConfiguration();
        $request->merge(['timeoff_ids' => $request->input('timeoff_ids', [])]);
        $validated = $this->validateGroup($request);
        $this->balanceService->saveGroup($validated);

        return redirect()->route('setting.hourly-time-off.index')->with('success', 'Hourly Time Off balance group created.');
    }

    public function show($groupId)
    {
        $this->authorizeConfiguration();
        $group = $this->balanceService->getGroup($groupId);

        return view('settings.time.hourly-time-off.show', [
            'title' => $group->name,
            'group' => $group,
            'period' => $this->balanceService->getCurrentPeriod($group->id),
            'balances' => $this->balanceService->getGroupBalances($group->id),
        ]);
    }

    public function edit($groupId)
    {
        $this->authorizeConfiguration();
        $group = $this->balanceService->getGroup($groupId);

        return view('settings.time.hourly-time-off.form', [
            'title' => 'Edit Balance Group',
            'group' => $group,
            'timeoffs' => TimeOff::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, $groupId)
    {
        $this->authorizeConfiguration();
        $request->merge(['timeoff_ids' => $request->input('timeoff_ids', [])]);
        $validated = $this->validateGroup($request, $groupId);
        $this->balanceService->saveGroup($validated, $groupId);

        return redirect()->route('setting.hourly-time-off.index')->with('success', 'Hourly Time Off balance group updated.');
    }

    public function employeeBalance($groupId, $employeeId)
    {
        $this->authorizeConfiguration();
        $group = $this->balanceService->getGroup($groupId);
        $employee = Employee::with('personal')->findOrFail($employeeId);
        $balance = $this->balanceService->getCurrentBalance($employee->id, $group->id);

        return view('settings.time.hourly-time-off.employee', [
            'title' => 'Employee Hourly Time Off Balance',
            'group' => $group,
            'employee' => $employee,
            'balance' => $balance,
            'transactions' => $this->balanceService->getTransactions(
                $employee->id,
                $group->id,
                $balance->period_start->toDateString()
            ),
        ]);
    }

    private function validateGroup(Request $request, $groupId = null)
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('timeoff_balance_groups', 'code')->ignore($groupId)],
            'period_type' => ['required', Rule::in(['monthly', 'yearly', 'unlimited'])],
            'maximum_hours' => ['nullable', 'numeric', 'min:0.01', 'required_unless:period_type,unlimited'],
            'is_active' => ['required', 'boolean'],
            'timeoff_ids' => ['array'],
            'timeoff_ids.*' => ['integer', 'distinct', 'exists:timeoffs,id'],
        ]);
    }

    private function authorizeConfiguration()
    {
        $user = auth()->user();
        $employee = $user ? $user->employee : null;
        $isAdmin = $user && $user->roles->contains(function ($role) {
            return strtolower($role->name) === 'admin' || (int) $role->id === 1;
        });
        abort_unless($isAdmin || ($employee && $employee->is_hrd), 403);
    }
}
