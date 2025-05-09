<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PersonService;
use Illuminate\Http\Request;

class PersonApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private PersonService $personSevice;
    public function __construct(PersonService $personService)
    {
        $this->personSevice = $personService;
    }

    public function index()
    {
        $request = request();
        return $this->personSevice->get($request);
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
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->personSevice->show($id);
    }
    public function byEmail($email)
    {
        return $this->personSevice->showByemail($email);
    }
    public function getPersonalData($companyId, Request $request)
    {
        return $this->personSevice->getPersonalData($companyId, $request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Person  $person
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
