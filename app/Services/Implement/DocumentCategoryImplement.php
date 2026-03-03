<?php

namespace App\Services;

use App\Models\DocumentCategory;
use Illuminate\Database\Eloquent\Collection;

class DocumentCategoryImplement implements DocumentCategoryService
{
    /**
     * @var DocumentCategory
     */
    protected $model;

    public function __construct(DocumentCategory $model)
    {
        $this->model = $model;
    }

    public function get(): Collection
    {
        return $this->model->all();
    }

    public function show(int $id): ?DocumentCategory
    {
        return $this->model->find($id);
    }

    public function post(array $data): DocumentCategory
    {
        return $this->model->create($data);
    }

    public function put(int $id, array $data): bool
    {
        $item = $this->model->find($id);
        if (!$item) {
            return false;
        }
        return $item->update($data);
    }

    public function delete(int $id): bool
    {
        $item = $this->model->find($id);
        if (!$item) {
            return false;
        }
        return (bool) $item->delete();
    }
}
