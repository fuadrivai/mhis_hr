<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Branch;
use App\Services\HolidayService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{

    private HolidayService $holidayService ;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }
    public function index()
    {
        $holidays = $this->holidayService->get();
        return view('settings.time.holiday', [
            'title' => 'List Holidays',
            'governments' => $holidays['government']->get(),
            'companies' => $holidays['company']->get(),
            'schools' => $holidays['school']->get(),
            'branches' => Branch::orderBy('name')->get(),
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
        $rules = [
            'type' => ['required', 'in:COMPANY,SCHOOL'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ];

        if ($request->input('type') === Holiday::TYPE_SCHOOL) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['description'] = ['nullable', 'string'];
            $rules['category'] = ['required', 'in:REGULAR,NEW_ACADEMIC_YEAR'];
        } else {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['description'] = ['required', 'string', 'max:255'];
        }

        $request->validate($rules);

        $holiday = $this->holidayService->post($request);

        return response()->json([
            'message' => 'Holiday dates created successfully.',
            'holidays' => $holiday,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function show(Holiday $holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function edit(Holiday $holiday)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Holiday $holiday)
    {
        if (!in_array($holiday->type, [Holiday::TYPE_COMPANY, Holiday::TYPE_SCHOOL], true)) {
            abort(404);
        }

        $rules = [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'same:start_date'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ];

        if ($holiday->type === Holiday::TYPE_SCHOOL) {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['description'] = ['nullable', 'string'];
            $rules['category'] = ['required', 'in:REGULAR,NEW_ACADEMIC_YEAR'];
        } else {
            $rules['name'] = ['required', 'string', 'max:255'];
            $rules['description'] = ['required', 'string', 'max:255'];
        }

        $request->validate($rules);

        $updatedHoliday = $this->holidayService->put($holiday, $request);

        return response()->json([
            'message' => 'Holiday updated successfully.',
            'holiday' => $updatedHoliday,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Holiday  $holiday
     * @return \Illuminate\Http\Response
     */
    public function destroy(Holiday $holiday)
    {
        if (!in_array($holiday->type, [Holiday::TYPE_COMPANY, Holiday::TYPE_SCHOOL], true)) {
            abort(404);
        }

        $this->holidayService->delete($holiday);

        return response()->json([
            'message' => 'Holiday deleted successfully.',
        ]);
    }
}
