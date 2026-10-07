<?php

namespace App\Http\Controllers;

use App\Services\SopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SopController extends Controller
{
    private SopService $sopService;

    public function __construct(SopService $sopService)
    {
        $this->sopService = $sopService;
    }

    public function index()
    {
        return view('settings.company.sop.index', [
            'title' => 'Setting SOP',
            'sops' => $this->sopService->getAll(),
        ]);
    }

    public function create()
    {
        return view('settings.company.sop.index', [
            'title' => 'Create SOP',
            'sops' => $this->sopService->getAll(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'link' => ['required', 'url:http,https'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->sopService->create($validated);

        return Redirect::route('setting.company.sop.index')->with('success', 'SOP created successfully.');
    }

    public function show($id)
    {
        $sop = $this->sopService->findById($id);

        return view('settings.company.sop.index', [
            'title' => 'SOP Detail',
            'sops' => $this->sopService->getAll(),
            'sop' => $sop,
        ]);
    }

    public function edit($id)
    {
        $sop = $this->sopService->findById($id);

        return view('settings.company.sop.index', [
            'title' => 'Edit SOP',
            'sops' => $this->sopService->getAll(),
            'sop' => $sop,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'link' => ['required', 'url:http,https'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $this->sopService->update($id, $validated);

        return Redirect::route('setting.company.sop.index')->with('success', 'SOP updated successfully.');
    }

    public function destroy($id)
    {
        $this->sopService->delete($id);

        return Redirect::route('setting.company.sop.index')->with('success', 'SOP deleted successfully.');
    }
}
