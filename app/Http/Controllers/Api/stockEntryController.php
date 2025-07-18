<?php

namespace App\Http\Controllers;

use App\Models\StockEntry;
use App\Models\Item;
use Illuminate\Http\Request;

class StockEntryController extends Controller
{
    // List semua data stok masuk
    public function index()
    {
        return StockEntry::with(['item', 'user'])->orderByDesc('date')->get();
    }

    // Simpan stok masuk baru dan update stok item
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'user_id' => 'required|exists:users,id',
            'qty'     => 'required|integer|min:1',
            'date'    => 'required|date',
            'note'    => 'nullable|string',
        ]);

        // Buat stok masuk
        $stockEntry = StockEntry::create($validated);

        // Update stok item (tambah)
        $item = Item::findOrFail($validated['item_id']);
        $item->increment('stock', $validated['qty']);

        return response()->json($stockEntry->load(['item', 'user']), 201);
    }

    // Detail stok masuk
    public function show($id)
    {
        return StockEntry::with(['item', 'user'])->findOrFail($id);
    }

    // Update stok masuk (jarang dipakai, biasanya tidak diperbolehkan)
    public function update(Request $request, $id)
    {
        $stockEntry = StockEntry::findOrFail($id);

        $validated = $request->validate([
            'qty'  => 'sometimes|integer|min:1',
            'date' => 'sometimes|date',
            'note' => 'nullable|string',
        ]);

        // Jika qty diubah, update stok item juga
        if ($request->has('qty')) {
            $selisih = $validated['qty'] - $stockEntry->qty;
            $stockEntry->item->increment('stock', $selisih);
        }

        $stockEntry->update($validated);

        return response()->json($stockEntry->load(['item', 'user']));
    }

    // Hapus stok masuk & kurangi stok item
    public function destroy($id)
    {
        $stockEntry = StockEntry::findOrFail($id);

        // Kurangi stok item
        $stockEntry->item->decrement('stock', $stockEntry->qty);

        $stockEntry->delete();

        return response()->json(['message' => 'Data stok masuk berhasil dihapus.']);
    }
}
