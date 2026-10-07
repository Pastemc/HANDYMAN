<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceCategory::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->latest()->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('categories', 'public');
                $imagePaths[] = $path;
            }
        }

        $category = ServiceCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
            'images' => $imagePaths,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function show($id)
    {
        $category = ServiceCategory::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = ServiceCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_active' => 'boolean',
            'keep_images' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'description', 'icon', 'is_active']);

        if ($request->has('name')) {
            $data['slug'] = Str::slug($request->name);
        }

        $existingImages = $category->images ?? [];
        $keepImages = $request->input('keep_images', []);
        
        $imagesToDelete = array_diff($existingImages, $keepImages);
        foreach ($imagesToDelete as $imageToDelete) {
            Storage::disk('public')->delete($imageToDelete);
        }

        $finalImages = $keepImages;

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('categories', 'public');
                $finalImages[] = $path;
            }
        }

        $data['images'] = $finalImages;

        $category->update($data);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = ServiceCategory::findOrFail($id);
        
        if ($category->images) {
            foreach ($category->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }
        
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}