<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Cutoff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    public function attendance()
    {
        return view('report.attendance', [
            'title' => 'Attendance Report',
        ]);
    }

    public function filterReport(Request $request)
    {
        $_month = $request->input('month');
        $cutoff = Cutoff::where('is_active', true)->firstOrFail();
        $month = Carbon::parse($_month);

        $endDate =  $month->copy()->day($cutoff->cutoff_day);
        $startDate = $endDate->copy()->subMonth()->addDay();

        $attendances = Attendance::query()
                        ->whereBetween('date', [$startDate, $endDate])
                        ->get();
        return response()->json($attendances);
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
