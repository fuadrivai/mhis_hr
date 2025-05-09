<?php

namespace App\Http\Controllers;

use App\Models\Religion;
use App\Services\ReligionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ReligionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private ReligionService $religionSevice;

    public function __construct(ReligionService $religionService)
    {
        $this->religionSevice = $religionService;
    }

    public function index()
    {

        $data = $this->religionSevice->get()->getContent();
        return view('settings.religion', [
            "data" => json_decode($data, true),
            "title" => "Setting Religion"
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
        try {
            $this->religionSevice->post($request);
            return Redirect::to('religion');
        } catch (\Throwable $th) {
            return back()->with('message', $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Religion  $religion
     * @return \Illuminate\Http\Response
     */
    public function show(Religion $religion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Religion  $religion
     * @return \Illuminate\Http\Response
     */
    public function edit(Religion $religion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Religion  $religion
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $this->religionSevice->put($id, $request);
        return Redirect::to('religion');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Religion  $religion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Religion $religion)
    {
        //
    }
}
