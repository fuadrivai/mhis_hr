<?php

namespace App\Http\Controllers;

use App\Models\TimeOff;
use App\Http\Requests\UpdateTimeOffRequest;
use App\Services\TimeOffService;
use Illuminate\Http\Request;
use Yajra\DataTables\Utilities\Request as UtilitiesRequest;

class TimeOffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private TimeOffService $timeOffService;

    public function __construct(TimeOffService $timeOffService)
    {
        $this->timeOffService = $timeOffService;
    }
    public function index()
    {
        return view('settings.timeoff', [
            "title" => "Time Off",
        ]);
    }

    public function dataTable(UtilitiesRequest $request)
    {
        $timeOffs = TimeOff::query();
        if ($request->ajax()) {
            return datatables()->of($timeOffs)->make(true);
        }
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
     * @param  \App\Http\Requests\StoreTimeOffRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deduct_from_leave' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'need_attachment' => 'nullable|boolean',
        ]);

        $timeOff = $this->timeOffService->post($validated);
        return response()->json($timeOff);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TimeOff  $timeOff
     * @return \Illuminate\Http\Response
     */
    public function show(TimeOff $timeOff)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TimeOff  $timeOff
     * @return \Illuminate\Http\Response
     */
    public function edit(TimeOff $timeOff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTimeOffRequest  $request
     * @param  \App\Models\TimeOff  $timeOff
     * @return \Illuminate\Http\Response
     */
    public function update($id , Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:time_offs,id',
            'code' => 'sometimes|required|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'deduct_from_leave' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'need_attachment' => 'nullable|boolean',
        ]);

        $validated['id'] = $id;

        $timeOff = $this->timeOffService->put($validated);
        return response()->json($timeOff);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TimeOff  $timeOff
     * @return \Illuminate\Http\Response
     */
    public function destroy(TimeOff $timeOff)
    {
        //
    }
}
