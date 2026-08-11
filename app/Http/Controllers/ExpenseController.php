<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Expense::with(['category'])
            ->where('gym_id', $gymId);

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->filled('from')) {
            $query->whereDate('expense_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('expense_date', '<=', $request->input('to'));
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(15)->withQueryString();

        $categories = ExpenseCategory::where('gym_id', $gymId)->orderBy('name')->get();

        $summary = [
            'total' => (float) Expense::where('gym_id', $gymId)->sum('amount'),
            'month' => (float) Expense::where('gym_id', $gymId)
                ->whereBetween('expense_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount'),
        ];

        return view('expenses.index', compact('expenses', 'categories', 'summary'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('expenses.create'), 403);

        $categories = ExpenseCategory::where('gym_id', current_gym()?->id)->orderBy('name')->get();

        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('expenses.create'), 403);

        $data = $request->validate([
            'category_id' => ['required', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', 'in:cash,upi,card,neft,cheque,other'],
            'description' => ['nullable', 'string'],
        ]);

        $data['payment_method'] = $data['payment_method'] ?? null;

        Expense::create(array_merge(collect($data)->except('payment_method')->toArray(), [
            'gym_id' => current_gym()->id,
            'created_by' => auth()->id(),
        ]));

        audit_log('expense.created', 'expenses', null, 'Expense recorded ' . money($data['amount']));

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function destroy(Expense $expense)
    {
        abort_unless(auth()->user()->hasPermission('expenses.manage'), 403);

        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }

    public function export(Request $request)
    {
        $gymId = current_gym()?->id;

        $expenses = Expense::with('category')->where('gym_id', $gymId)
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->input('to')))
            ->get();

        $filename = 'expenses-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Category', 'Vendor', 'Description', 'Amount']);

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->expense_date,
                    $expense->category?->name,
                    $expense->vendor,
                    $expense->description,
                    $expense->amount,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
