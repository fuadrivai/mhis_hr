<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GsheetLinkService;
use Illuminate\Http\Request;

class GsheetLinkApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private GsheetLinkService $gSheetLinkService;

    public function __construct(GsheetLinkService $gSheetLinkService)
    {
        $this->gSheetLinkService = $gSheetLinkService;
    }

    public function getSchoolCalendar()
    {
        return $this->gSheetLinkService->getSchoolCalendar();
    }

    public function getNewsletter()
    {
        return $this->gSheetLinkService->getNewsletter();
    }
    public function kpi()
    {
        $request = request();
        return $this->gSheetLinkService->getKPI($request);
    }

    public function index()
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
