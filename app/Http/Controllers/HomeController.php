<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user->hasRole('admin')) {
            $employee = $user->employee;
            return redirect('/profile/personal/' . $employee->id);
        }

        // Active Employees right now
        $now = \Carbon\Carbon::now();
        $employees = \App\Models\Employee::where('is_active', 1)->whereHas('employment')->with('employment')->get();
        $activeEmployees = $employees->filter(function($emp) use ($now) {
            return $emp->employment && ($emp->employment->resign_date === null || \Carbon\Carbon::parse($emp->employment->resign_date)->isAfter($now));
        });

        // Employment Status
        $empStatusDataRaw = [];
        $totalActive = $activeEmployees->count();
        
        foreach ($activeEmployees as $emp) {
            $status = $emp->employment->employment_status ? ucfirst($emp->employment->employment_status) : 'Unknown';
            if (!isset($empStatusDataRaw[$status])) {
                $empStatusDataRaw[$status] = 0;
            }
            $empStatusDataRaw[$status]++;
        }
        
        $empStatusData = [];
        foreach($empStatusDataRaw as $k => $v) {
            $empStatusData[] = [
                'name' => $k,
                'count' => $v,
                'percentage' => $totalActive > 0 ? round(($v / $totalActive) * 100, 1) : 0
            ];
        }

        // Length of Service
        $lengthOfService = [
            '< 1 yr' => 0,
            '1-3 yr' => 0,
            '3-5 yr' => 0,
            '5-10 yr' => 0,
            '> 10 yr' => 0,
        ];

        foreach ($activeEmployees as $emp) {
            if (!$emp->employment || !$emp->employment->join_date) continue;
            $joinDate = \Carbon\Carbon::parse($emp->employment->join_date);
            $diffDays = $joinDate->diffInDays($now);
            $diffYears = $diffDays / 365.25;

            if ($diffYears < 1) {
                $lengthOfService['< 1 yr']++;
            } elseif ($diffYears < 3) {
                $lengthOfService['1-3 yr']++;
            } elseif ($diffYears < 5) {
                $lengthOfService['3-5 yr']++;
            } elseif ($diffYears <= 10) {
                $lengthOfService['5-10 yr']++;
            } else {
                $lengthOfService['> 10 yr']++;
            }
        }
        $losData = [];
        foreach($lengthOfService as $key => $val) {
            $losData[] = [$key, $val];
        }

        // Job Level
        $jobLevelDataRaw = [];
        foreach ($activeEmployees as $emp) {
            $level = $emp->employment->job_level ? $emp->employment->job_level->name : 'Unknown';
            if (!isset($jobLevelDataRaw[$level])) {
                $jobLevelDataRaw[$level] = 0;
            }
            $jobLevelDataRaw[$level]++;
        }
        $jobLevelData = [];
        foreach($jobLevelDataRaw as $k => $v) {
            $jobLevelData[] = [
                'name' => $k,
                'count' => $v,
                'percentage' => $totalActive > 0 ? round(($v / $totalActive) * 100, 1) : 0
            ];
        }

        // Gender Diversity
        $activeEmployeesWithPersonal = \App\Models\Employee::whereIn('id', $activeEmployees->pluck('id'))
            ->whereHas('personal')
            ->with('personal')
            ->get();
            
        $genderDataRaw = [
            'Female' => 0,
            'Male' => 0
        ];
        
        foreach($activeEmployeesWithPersonal as $emp) {
            $g = $emp->personal->gendre;
            if ($g == 1 || strtolower($g) == 'male') {
                $genderDataRaw['Male']++;
            } elseif ($g == 2 || strtolower($g) == 'female') {
                $genderDataRaw['Female']++;
            }
        }
        $genderData = [];
        foreach($genderDataRaw as $k => $v) {
            $genderData[] = [
                'name' => $k,
                'count' => $v,
                'percentage' => $totalActive > 0 ? round(($v / $totalActive) * 100, 1) : 0
            ];
        }

        // KPI Data
        $newThisMonth = \App\Models\Employee::whereHas('employment', function($q) use ($now) {
            $q->whereMonth('join_date', $now->month)->whereYear('join_date', $now->year);
        })->count();

        $presentToday = \App\Models\Attendance::whereDate('date', $now->toDateString())
                            ->where('status', 'present')
                            ->count();
        
        $presentPercentage = $totalActive > 0 ? round(($presentToday / $totalActive) * 100, 1) : 0;

        $openRequests = \App\Models\ApprovalRequest::where('status', 'pending')->count();

        return view('home.home', [
            "title" => "Home Page",
            "totalActive" => $totalActive,
            "newThisMonth" => $newThisMonth,
            "presentToday" => $presentToday,
            "presentPercentage" => $presentPercentage,
            "openRequests" => $openRequests,
            "empStatusData" => $empStatusData,
            "lengthOfService" => $losData,
            "jobLevelData" => $jobLevelData,
            "genderData" => $genderData
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
