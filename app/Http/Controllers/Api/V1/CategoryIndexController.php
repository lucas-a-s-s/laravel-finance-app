<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FilterCategoriesRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryIndexController extends Controller
{
    public function __invoke(FilterCategoriesRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $categories = $request->user()
            ->categories()
            ->when($filters['type'] ?? null, function ($query, string $type): void {
                $query->where('type', $type);
            })
            ->when(array_key_exists('is_active', $filters), function ($query) use ($filters): void {
                $query->where('is_active', $filters['is_active']);
            })
            ->orderByDesc('is_active')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return CategoryResource::collection($categories);
    }
}
