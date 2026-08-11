<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = InventoryItem::where('gym_id', $gymId);

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'reorder_level');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('name')->paginate(15)->withQueryString();

        $categories = InventoryItem::where('gym_id', $gymId)->distinct()->pluck('category');

        $summary = [
            'total' => InventoryItem::where('gym_id', $gymId)->count(),
            'low_stock' => InventoryItem::where('gym_id', $gymId)->whereColumn('stock', '<=', 'reorder_level')->count(),
            'value' => (float) InventoryItem::where('gym_id', $gymId)
                ->selectRaw('SUM(stock * purchase_price) as value')->value('value'),
        ];

        return view('inventory.index', compact('items', 'categories', 'summary'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('inventory.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'stock' => ['required', 'integer', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:100'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        InventoryItem::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'reorder_level' => $data['reorder_level'] ?? 0,
            'unit' => $data['unit'] ?? 'pcs',
        ]));

        audit_log('inventory.created', 'inventory', null, "Added inventory item {$data['name']}");

        return back()->with('success', 'Inventory item added.');
    }

    public function update(Request $request, InventoryItem $item)
    {
        abort_unless(auth()->user()->hasPermission('inventory.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:100'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ]);

        $item->update($data);

        return back()->with('success', 'Inventory item updated.');
    }

    public function adjustStock(Request $request, InventoryItem $item)
    {
        abort_unless(auth()->user()->hasPermission('inventory.manage'), 403);

        $data = $request->validate([
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer'],
            'note' => ['nullable', 'string'],
        ]);

        $qty = $data['type'] === 'out' ? -$data['quantity'] : $data['quantity'];

        $item->update(['stock' => max(0, $item->stock + $qty)]);

        $item->transactions()->create([
            'type' => $data['type'],
            'quantity' => $data['type'] === 'adjustment' ? $data['quantity'] : $qty,
            'note' => $data['note'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Stock adjusted.');
    }

    public function destroy(InventoryItem $item)
    {
        abort_unless(auth()->user()->hasPermission('inventory.manage'), 403);

        $item->delete();

        return back()->with('success', 'Inventory item removed.');
    }
}
