<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function index()
    {
        $data = $this->shiftService->get()->getContent();
        return view('settings.time.schedule.index-2', [
            "title" => "Time Shift",
            "data" => json_decode($data, true),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('settings.time.schedule.shift', [
            "title" => "Time Shift"
        ]);
    }

    public function get()
    {
        try {
            $shifts =  $this->shiftService->get()->getContent();
            return response()->json(json_decode($shifts, true));
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], $th->getCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreShiftRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->shiftService->post($request);
            return Redirect::to('setting/shift');
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            // Log::info('Form Data:', $th->getMessage());
            return back()->with('message', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function show(Shift $shift)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = $this->shiftService->show($id)->getContent();
        return view('settings.time.schedule.shift', [
            "title" => "Time Shift",
            "data" => json_decode($data, true)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateShiftRequest  $request
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        try {
            $this->shiftService->put($id, $request);
            return Redirect::to('setting/shift');
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            // Log::info('Form Data:', $th->getMessage());
            return back()->with('message', $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shift $shift)
    {
        //
    }
}
