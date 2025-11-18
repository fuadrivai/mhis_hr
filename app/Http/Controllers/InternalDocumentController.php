<?php

namespace App\Http\Controllers;

use App\Models\InternalDocument;
use App\Http\Requests\StoreInternalDocumentRequest;
use App\Http\Requests\UpdateInternalDocumentRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Services\InternalDocumentService;
use Illuminate\Http\Request;

class InternalDocumentController extends Controller
{

    private EmployeeService $employeeService;
    private InternalDocumentService $internalDocumentService;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(
        EmployeeService $employeeService,
        InternalDocumentService $internalDocumentService
    ) {
        $this->employeeService = $employeeService;
        $this->internalDocumentService = $internalDocumentService;
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
        $employee= $this->employeeService->getByuserId(auth()->id());
        return view('layouts.document-form',["data"=>$employee]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInternalDocumentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->internalDocumentService->post($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InternalDocument  $internalDocument
     * @return \Illuminate\Http\Response
     */
    public function show(InternalDocument $internalDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InternalDocument  $internalDocument
     * @return \Illuminate\Http\Response
     */
    public function edit(InternalDocument $internalDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInternalDocumentRequest  $request
     * @param  \App\Models\InternalDocument  $internalDocument
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInternalDocumentRequest $request, InternalDocument $internalDocument)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InternalDocument  $internalDocument
     * @return \Illuminate\Http\Response
     */
    public function destroy(InternalDocument $internalDocument)
    {
        //
    }
}
