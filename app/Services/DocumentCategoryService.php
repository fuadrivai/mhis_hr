<?php

namespace App\Services;

use App\Models\DocumentCategory;

interface DocumentCategoryService
{
    public function get();

    public function show(int $id): ?DocumentCategory;

    public function post(array $data): DocumentCategory;

    public function put(int $id, array $data): bool;

    public function delete(int $id): bool;
}
