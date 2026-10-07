<?php

namespace App\Services\Implement;

use App\Models\Sop;
use App\Services\SopService;

class SopImplement implements SopService
{
    public function getAll()
    {
        return Sop::orderBy('title')->get();
    }

    public function findById($id)
    {
        return Sop::findOrFail($id);
    }

    public function create(array $data)
    {
        return Sop::create([
            'title' => $data['title'],
            'link' => $data['link'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function update($id, array $data)
    {
        $sop = Sop::findOrFail($id);
        $sop->fill([
            'title' => $data['title'],
            'link' => $data['link'],
            'is_active' => (bool) ($data['is_active'] ?? $sop->is_active),
        ]);
        $sop->save();

        return $sop;
    }

    public function delete($id)
    {
        $sop = Sop::findOrFail($id);
        return $sop->delete();
    }
}
