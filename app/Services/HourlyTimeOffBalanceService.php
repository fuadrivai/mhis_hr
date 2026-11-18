<?php

namespace App\Services;

interface HourlyTimeOffBalanceService
{
    public function getGroups();

    public function getGroup($id);

    public function saveGroup(array $attributes, $id = null);

    public function getCurrentPeriod($balanceGroupId);

    public function getCurrentBalance($employeeId, $balanceGroupId);

    public function getOrCreateCurrentBalance($employeeId, $balanceGroupId);

    public function checkAvailability($employeeId, $balanceGroupId, $hours);

    public function consume($employeeId, $balanceGroupId, $approvalRequestId, $timeoffId, $hours);

    public function reverse($employeeId, $approvalRequestId, $timeoffId = null);

    public function adjust($employeeId, $balanceGroupId, $hours, $description);

    public function getTransactions($employeeId, $balanceGroupId, $periodStart = null);

    public function getGroupBalances($balanceGroupId);

    public function getRequestedHours(array $payload);
}
