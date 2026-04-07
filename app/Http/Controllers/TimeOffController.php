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
        return view('settings.timeoff.index', [
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
        return view('settings.timeoff.form', [
            "title" => "Time Off Form",
        ]);
    }

    /**
     * Show the preview of the form fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function preview(Request $request)
    {
        $schema = $request->get('schema', '[]');
        $fields = json_decode($schema, true);

        return view('settings.timeoff.preview', [
            "title" => "Form Preview",
            "fields" => $fields
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTimeOffRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'code' => 'required|string|max:255|unique:timeoffs,code',
            'name' => 'required|string|max:255'
        ]);
        $this->timeOffService->post($request);
        return redirect()->route('timeoff.index')->with('success', 'TimeOff created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TimeOff  $timeOff
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $timeOff = $this->timeOffService->show($id);
        return response()->json($timeOff);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TimeOff  $timeOff
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $timeOff = $this->timeOffService->show($id);
        return view('settings.timeoff.form', [
            "title" => "Edit Time Off",
            "timeOff" => $timeOff
        ]);
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
        $request->merge(['id' => $id]);
        $timeOff = $this->timeOffService->put($request);
        if (isset($timeOff->id)) {
            return redirect()->route('timeoff.index')->with('success', 'TimeOff updated successfully');
        } else {
            return redirect()->route('timeoff.index')->with('error', 'Failed to update TimeOff');
        }

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
