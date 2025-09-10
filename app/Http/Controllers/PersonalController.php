<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Services\PersonalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private PersonalService $personalService;

    public function __construct(PersonalService $personalService)
    {
        $this->personalService = $personalService;
    }

    public function index()
    {
        //
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
     * @param  \App\Models\Personal  $personal
     * @return \Illuminate\Http\Response
     */
    public function show(Personal $personal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Personal  $personal
     * @return \Illuminate\Http\Response
     */
    public function edit(Personal $personal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Personal  $personal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:personals,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile_phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'birth_place' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'gendre' => 'nullable|string|max:20',
            'marital_status' => 'required|string|max:50',
            'blood_type' => 'nullable|string|max:5',
            'religion_id' => 'required|integer',
            'identity_number' => 'nullable|string|max:100',
            'passport_number' => 'nullable|string|max:100',
            'expired_date_identity_id' => 'nullable|date',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'current_address' => 'nullable|string',
        ]);
        $this->personalService->put($validated);
        return Redirect::to('employee');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Personal  $personal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Personal $personal)
    {
        //
    }
}
