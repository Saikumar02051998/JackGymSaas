<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Equipment::where('gym_id', $gymId);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $equipment = $query->orderBy('name')->paginate(15)->withQueryString();

        $summary = [
            'total' => Equipment::where('gym_id', $gymId)->count(),
            'active' => Equipment::where('gym_id', $gymId)->where('status', 'active')->count(),
            'maintenance' => Equipment::where('gym_id', $gymId)->where('status', 'maintenance')->count(),
            'due' => Equipment::where('gym_id', $gymId)
                ->where('status', '!=', 'retired')
                ->whereNotNull('next_maintenance')
                ->where('next_maintenance', '<=', now()->addDays(14)->toDateString())
                ->count(),
        ];

        return view('equipment.index', compact('equipment', 'summary'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('equipment.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'warranty_until' => ['nullable', 'date'],
            'last_maintenance' => ['nullable', 'date'],
            'next_maintenance' => ['nullable', 'date'],
            'condition' => ['nullable', 'in:excellent,good,fair,poor,needs_repair'],
            'location' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,maintenance,retired'],
            'notes' => ['nullable', 'string'],
        ]);

        $equipment = Equipment::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'condition' => $data['condition'] ?? 'good',
            'status' => $data['status'] ?? 'active',
        ]));

        $this->syncExpense($equipment, $data);

        audit_log('equipment.created', 'equipment', null, "Added equipment {$data['name']}");

        return back()->with('success', 'Equipment added.');
    }

    public function update(Request $request, Equipment $equipment)
    {
        abort_unless(auth()->user()->hasPermission('equipment.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'warranty_until' => ['nullable', 'date'],
            'last_maintenance' => ['nullable', 'date'],
            'next_maintenance' => ['nullable', 'date'],
            'condition' => ['nullable', 'in:excellent,good,fair,poor,needs_repair'],
            'location' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,maintenance,retired'],
            'notes' => ['nullable', 'string'],
        ]);

        $equipment->update($data);

        $this->syncExpense($equipment, $data);

        audit_log('equipment.updated', 'equipment', $equipment->id, "Updated equipment {$equipment->name}");

        return back()->with('success', 'Equipment updated.');
    }

    public function destroy(Equipment $equipment)
    {
        abort_unless(auth()->user()->hasPermission('equipment.manage'), 403);

        $equipment->expense?->delete();

        $equipment->delete();

        return back()->with('success', 'Equipment removed.');
    }

    private function syncExpense(Equipment $equipment, array $data): void
    {
        $cost = (float) ($data['purchase_cost'] ?? 0);

        $category = ExpenseCategory::firstOrCreate(
            ['gym_id' => $equipment->gym_id, 'name' => 'Equipment'],
            ['description' => 'Equipment costs']
        );

        $payload = [
            'category_id' => $category->id,
            'amount' => $cost,
            'expense_date' => $data['purchase_date'] ?? today()->toDateString(),
            'description' => 'Equipment purchase: '.$equipment->name,
        ];

        $expense = $equipment->expense;

        if ($cost <= 0) {
            if ($expense) {
                $expense->delete();
            }

            return;
        }

        if ($expense) {
            $expense->update($payload);
        } else {
            Expense::create(array_merge($payload, [
                'gym_id' => $equipment->gym_id,
                'equipment_id' => $equipment->id,
                'created_by' => auth()->id(),
            ]));
        }
    }
}
