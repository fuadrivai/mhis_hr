<?php

namespace App\Http\Controllers;

use App\Models\Reprimand;
use App\Models\ReprimandType;
use App\Models\Employee;
use Illuminate\Http\Request;

class ReprimandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Reprimand';
        $reprimands = Reprimand::with(['employee.personal', 'reprimandType', 'watchers'])->get();
        $employees = Employee::with('personal')->get();
        $reprimandTypes = ReprimandType::all();
        
        return view('reprimand.index', compact('reprimands', 'employees', 'reprimandTypes', 'title'));
    }

    private function getValidationRules(Request $request, $reprimandId = null)
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'effective_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:effective_date',
            'notes' => 'nullable|string',
            'attachment_link' => 'nullable|url',
            'document_template_id' => 'nullable|integer',
            'watchers' => 'nullable|array',
            'watchers.*' => 'exists:employees,id',
            'reprimand_type_id' => [
                'required',
                'exists:reprimand_types,id',
                function ($attribute, $value, $fail) use ($request, $reprimandId) {
                    $highestActiveLevel = 0;
                    
                    $query = Reprimand::where('employee_id', $request->employee_id)
                        ->where('effective_date', '<=', today())
                        ->where('end_date', '>=', today());
                        
                    if ($reprimandId) {
                        $query->where('id', '!=', $reprimandId);
                    }
                    
                    $activeReprimands = $query->with('reprimandType')->get();
                    
                    foreach($activeReprimands as $activeRep) {
                        $lvl = $activeRep->reprimandType->level ?? 1;
                        if ($lvl > $highestActiveLevel) {
                            $highestActiveLevel = $lvl;
                        }
                    }
                    
                    if ($highestActiveLevel > 0) {
                        $newType = ReprimandType::find($value);
                        if ($newType && $newType->level <= $highestActiveLevel) {
                            $fail('The employee has an active reprimand at Level ' . $highestActiveLevel . '. You must assign a higher level.');
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->getValidationRules($request));

        $reprimand = Reprimand::create($request->except('watchers'));

        if ($request->has('watchers')) {
            $reprimand->watchers()->sync($request->watchers);
        }

        return redirect()->route('reprimand.index')->with('success', 'Reprimand created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reprimand  $reprimand
     * @return \Illuminate\Http\Response
     */
    public function show(Reprimand $reprimand)
    {
        return response()->json($reprimand->load(['employee', 'reprimandType', 'watchers']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reprimand  $reprimand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reprimand $reprimand)
    {
        $request->validate($this->getValidationRules($request, $reprimand->id));

        $reprimand->update($request->except('watchers'));

        if ($request->has('watchers')) {
            $reprimand->watchers()->sync($request->watchers);
        } else {
            $reprimand->watchers()->detach();
        }

        return redirect()->route('reprimand.index')->with('success', 'Reprimand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reprimand  $reprimand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reprimand $reprimand)
    {
        $reprimand->delete();

        return redirect()->route('reprimand.index')->with('success', 'Reprimand deleted successfully.');
    }
}
