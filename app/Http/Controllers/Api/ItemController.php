<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Item::with('category');
            
            // Filter berdasarkan kategori
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            
            // Filter berdasarkan pencarian nama
            if ($request->has('search')) {
                $query->search($request->search);
            }

            // Filter stock rendah
            if ($request->has('low_stock') && $request->low_stock == 'true') {
                $query->lowStock();
            }

            $items = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data items berhasil diambil',
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data items',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:items',
                'category_id' => 'required|exists:categories,id',
                'stock' => 'required|integer|min:0',
                'unit' => 'nullable|string|max:50',
                'min_stock' => 'required|integer|min:0'
            ]);

            $item = Item::create($validatedData);
            $item->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan',
                'data' => $item
            ], 201);
        }catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try{
            $item = Item::with('category')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail item berhasil diambil',
                'data' => $item
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Item tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $item = Item::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:items,name,' . $id,
                'category_id' => 'sometimes|required|exists:categories,id',
                'stock' => 'sometimes|required|integer|min:0',
                'unit' => 'nullable|string|max:50',
                'min_stock' => 'sometimes|required|integer|min:0'
            ]);

            $item->update($validatedData);
            $item->load('category');

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil diupdate',
                'data' => $item
            ]);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $item = Item::findOrFail($id);
            $itemName = $item->name;

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => "Item '{$itemName}' berhasil dihapus"
            ]);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item',
                'error' => $e->getMessage()
            ], 500 );
        }
    }

    /**
     * Get items with low stock
     * GET /api/items/low-stock
     */
    public function lowStock(): JsonResponse
    {
        try {
            $items = Item::lowStock()->with('category')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data items dengan stock rendah',
                'data' => $items,
                'count' => $items->count() 
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil items dengan stock rendah',
                'error' => $e->getMessage()
            ],500 );
        }
    }

    /**
     * Update stock item (untuk transaksi masuk/keluar)
     * PATCH /api/items/{id}/stock
     */
    public function updateStock(Request $request,string $id): JsonResponse
    {
        try{
            $item = Item::findOrFail($id);

            $validatedData = $request->validate([
                'type' => 'requiered|in:in,out',
                'quanity' => 'required|integer|min:1'
            ]);

            $currentStock = $item->stock;

            if($validatedData['type' === 'in']){
                $newStock = $currentStock + $validatedData['quantity'];
            }else{
                $newStock = $currentStock - $validatedData['quantity'];

                if ($newStock < 0 ){
                    return response()->json([
                        'success' => false,
                        'message' => 'stock tidak mencukupi',
                        'current stock' => $currentStock,
                        'requested' => $validatedData['quantity']
                    ], 400);
                }
            }

            $item->update(['stock' => $newStock]);
            $item->load('category');

            return response()->json([
                'succes' => true,
                'message' => 'Stock berhasil di update',
                'data' => $item,
                'stock_change' => [
                    'type' => $validatedData['data'],
                    'quantity' => $validatedData['quantity'],
                    'previous_stock' => $currentStock,
                    'current_stock' => $newStock
                ]
            ]);
        } catch (ValidationException $e){
            return response()->json([
                'succes' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

