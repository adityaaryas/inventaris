<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * GET /api/categories
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Category::query();
            
            // Filter berdasarkan pencarian nama
            if ($request->has('search')) {
                $query->search($request->search);
            }
            
            // Include items count
            if ($request->has('with_items_count') && $request->with_items_count == 'true') {
                $query->withCount('items');
            }
            
            // Include items
            if ($request->has('with_items') && $request->with_items == 'true') {
                $query->with('items');
            }
            
            $categories = $query->orderBy('name')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Data categories berhasil diambil',
                'data' => $categories
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/categories
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name'
            ]);

            $category = Category::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Category berhasil ditambahkan',
                'data' => $category
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * GET /api/categories/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            
            // Include items jika diminta
            if (request()->has('with_items') && request()->with_items == 'true') {
                $category->load('items');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Detail category berhasil diambil',
                'data' => $category
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/categories/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $id
            ]);

            $category->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Category berhasil diupdate',
                'data' => $category
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/categories/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            
            // Cek apakah category masih digunakan oleh items
            $itemsCount = $category->items()->count();
            if ($itemsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Category '{$category->name}' tidak dapat dihapus karena masih digunakan oleh {$itemsCount} item(s)",
                    'items_count' => $itemsCount
                ], 400);
            }
            
            $categoryName = $category->name;
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => "Category '{$categoryName}' berhasil dihapus"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories with their items
     * GET /api/categories/with-items
     */
    public function withItems(): JsonResponse
    {
        try {
            $categories = Category::with('items')->orderBy('name')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Data categories dengan items berhasil diambil',
                'data' => $categories
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data categories dengan items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories with items count
     * GET /api/categories/with-count
     */
    public function withCount(): JsonResponse
    {
        try {
            $categories = Category::withCount('items')->orderBy('name')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Data categories dengan jumlah items berhasil diambil',
                'data' => $categories
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data categories dengan count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}