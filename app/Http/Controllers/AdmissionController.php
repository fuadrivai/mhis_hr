<?php

namespace App\Http\Controllers;

use App\Services\AdmissionService;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    protected AdmissionService $admissionService;

    public function __construct(AdmissionService $admissionService)
    {
        $this->admissionService = $admissionService;
    }
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('admission.enrolment', ['title' => 'Enrolment']);
        }

        $response = $this->admissionService->getEnrolment($request);
        if (!$response->successful()) {
            return response()->json([
                'draw' => (int) $request->input('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error fetching enrolment data',
            ], $response->status());
        }

        $payload = $response->json() ?: [];
        $total = (int) ($payload['total'] ?? count($payload['data'] ?? []));

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $payload['data'] ?? [],
        ]);
    }

    public function academicYear(Request $request)
    {
        return $this->forward($this->admissionService->getAcademicYear($request));
    }

    public function branch(Request $request)
    {
        return $this->forward($this->admissionService->getBranch($request));
    }

    public function levelByBranch(Request $request, $branchId)
    {
        return $this->forward($this->admissionService->getLevelByBranch($branchId, $request));
    }

    public function gradeByLevel(Request $request, $levelId)
    {
        return $this->forward($this->admissionService->getGradeByLevel($levelId, $request));
    }

    private function forward($response)
    {
        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type', 'application/json'));
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
