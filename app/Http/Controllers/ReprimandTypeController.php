<?php

namespace App\Http\Controllers;

use App\Models\ReprimandType;
use Illuminate\Http\Request;

class ReprimandTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Reprimand Type';
        $reprimandTypes = ReprimandType::all();
        return view('settings.reprimand_type.index', compact('reprimandTypes', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Typically managed via a modal in the index view, but we can have it return the view if needed
        return view('settings.reprimand_type.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'deduction_score' => 'required|integer',
            'level' => 'required|integer|min:1',
        ]);

        ReprimandType::create($request->all());

        return redirect()->route('reprimand-type.index')->with('success', 'Reprimand Type created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReprimandType  $reprimandType
     * @return \Illuminate\Http\Response
     */
    public function show(ReprimandType $reprimandType)
    {
        return view('settings.reprimand_type.index', compact('reprimandType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReprimandType  $reprimandType
     * @return \Illuminate\Http\Response
     */
    public function edit(ReprimandType $reprimandType)
    {
        // Could return a view or just handle in modal
        return response()->json($reprimandType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReprimandType  $reprimandType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReprimandType $reprimandType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'deduction_score' => 'required|integer',
            'level' => 'required|integer|min:1',
        ]);

        $reprimandType->update($request->all());

        return redirect()->route('reprimand-type.index')->with('success', 'Reprimand Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReprimandType  $reprimandType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReprimandType $reprimandType)
    {
        $reprimandType->delete();

        return redirect()->route('reprimand-type.index')->with('success', 'Reprimand Type deleted successfully.');
    }
}
