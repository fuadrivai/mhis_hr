<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SopService;
use Illuminate\Http\Request;

class SopApiController extends Controller
{
    private SopService $sopService;

    public function __construct(SopService $sopService)
    {
        $this->sopService = $sopService;
    }

    public function index()
    {
        return response()->json($this->sopService->getAll());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'link' => ['required', 'url:http,https'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $sop = $this->sopService->create($validated);

        return response()->json([
            'message' => 'SOP created successfully.',
            'data' => $sop,
        ], 201);
    }

    public function show($id)
    {
        return response()->json($this->sopService->findById($id));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'link' => ['sometimes', 'required', 'url:http,https'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $sop = $this->sopService->update($id, $validated);

        return response()->json([
            'message' => 'SOP updated successfully.',
            'data' => $sop,
        ]);
    }

    public function destroy($id)
    {
        $this->sopService->delete($id);

        return response()->json([
            'message' => 'SOP deleted successfully.',
        ]);
    }
}
