<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::withCount('skills')->orderBy('name')->get();
        return CategoryResource::collection($categories);
    }

    public function show(Category $category): CategoryResource
    {
        $category->load('skills.category');
        return new CategoryResource($category);
    }
}
