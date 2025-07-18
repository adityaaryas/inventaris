<?php

namespace App\Http\Controllers;

use App\Models\StockExit;
use App\Models\Item;
use Illuminate\Http\Request;

class StockExitController extends Controller
{
    // List semua data stok keluar
    public function index()
    {
        return StockExit::with(['item', 'user'])->orderByDesc('date')->get();
    }

    // Simpan stok keluar baru & update stok item (mengurangi stok)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'user_id' => 'required|exists:users,id',
            'qty'     => 'required|integer|min:1',
            'date'    => 'required|date',
            'note'    => 'nullable|string',
        ]);

        // Cek stok cukup
        $item = Item::findOrFail($validated['item_id']);
        if ($item->stock < $validated['qty']) {
            return response()->json(['message' => 'Stok tidak cukup untuk dikeluarkan.'], 422);
        }

        // Buat stok keluar
        $stockExit = StockExit::create($validated);

        // Kurangi stok item
        $item->decrement('stock', $validated['qty']);

        return response()->json($stockExit->load(['item', 'user']), 201);
    }

    // Detail stok keluar
    public function show($id)
    {
        return StockExit::with(['item', 'user'])->findOrFail($id);
    }

    // Update stok keluar (jika perlu, misal qty berubah)
    public function update(Request $request, $id)
    {
        $stockExit = StockExit::findOrFail($id);

        $validated = $request->validate([
            'qty'  => 'sometimes|integer|min:1',
            'date' => 'sometimes|date',
            'note' => 'nullable|string',
        ]);

        // Jika qty diubah, update stok item juga
        if ($request->has('qty')) {
            $selisih = $validated['qty'] - $stockExit->qty;

            $item = $stockExit->item;
            // Pastikan stok tidak negatif
            if ($selisih > 0 && $item->stock < $selisih) {
                return response()->json(['message' => 'Stok tidak cukup untuk perubahan jumlah.'], 422);
            }
            $item->decrement('stock', $selisih);
        }

        $stockExit->update($validated);

        return response()->json($stockExit->load(['item', 'user']));
    }

    // Hapus stok keluar & kembalikan stok item
    public function destroy($id)
    {
        $stockExit = StockExit::findOrFail($id);

        // Kembalikan stok item
        $stockExit->item->increment('stock', $stockExit->qty);

        $stockExit->delete();

        return response()->json(['message' => 'Data stok keluar berhasil dihapus.']);
    }
}

