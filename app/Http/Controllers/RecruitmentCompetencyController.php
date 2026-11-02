<?php

namespace App\Http\Controllers;

use App\Models\EvaluationCompetency;
use Illuminate\Http\Request;

class RecruitmentCompetencyController extends Controller
{
    public function index()
    {
        $competencies = EvaluationCompetency::orderBy('sort_order', 'asc')->orderBy('name', 'asc')->get();

        return view('recruitment.settings.evaluation-competencies.index', [
            'competencies' => $competencies,
            'title'        => 'Evaluation Competencies',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:evaluation_competencies,name',
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $maxSort = (int) EvaluationCompetency::max('sort_order');

        EvaluationCompetency::create([
            'name'        => $request->input('name'),
            'description' => $request->input('description'),
            'sort_order'  => $request->input('sort_order', $maxSort + 1),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('recruitment.settings.evaluation-competencies.index')
            ->with('success', 'Evaluation competency created successfully.');
    }

    public function update(Request $request, EvaluationCompetency $competency)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:evaluation_competencies,name,' . $competency->id,
            'description' => 'nullable|string|max:1000',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $competency->update([
            'name'        => $request->input('name'),
            'description' => $request->input('description'),
            'sort_order'  => $request->input('sort_order', $competency->sort_order),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('recruitment.settings.evaluation-competencies.index')
            ->with('success', 'Evaluation competency updated successfully.');
    }

    public function toggleActive(EvaluationCompetency $competency)
    {
        $competency->update(['is_active' => !$competency->is_active]);

        return redirect()
            ->route('recruitment.settings.evaluation-competencies.index')
            ->with('success', 'Competency status updated.');
    }

    public function destroy(EvaluationCompetency $competency)
    {
        // Check if competency has scores
        if ($competency->scores()->exists()) {
            return redirect()
                ->route('recruitment.settings.evaluation-competencies.index')
                ->with('error', 'Cannot delete competency that has recorded evaluation scores. You can deactivate it instead.');
        }

        $competency->delete();

        return redirect()
            ->route('recruitment.settings.evaluation-competencies.index')
            ->with('success', 'Evaluation competency deleted.');
    }
}
