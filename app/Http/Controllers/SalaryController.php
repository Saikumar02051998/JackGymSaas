<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\StaffProfile;
use App\Services\SalaryService;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function __construct(protected SalaryService $salaries) {}

    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Salary::with(['staff.user', 'items'])
            ->where('gym_id', $gymId);

        if ($request->filled('period')) {
            $query->where('period', $request->input('period'));
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->input('status'));
        }

        $salaries = $query->orderByDesc('period')->orderByDesc('created_at')->paginate(15)->withQueryString();

        $periods = Salary::where('gym_id', $gymId)->distinct()->orderByDesc('period')->pluck('period');

        return view('staff.salaries', compact('salaries', 'periods'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('salary.manage'), 403);

        $staff = StaffProfile::with('user')->where('gym_id', current_gym()?->id)
            ->where('status', 'active')
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->orderBy('id')
            ->get();

        return view('staff.salary-create', compact('staff'));
    }

    public function pay(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('salary.manage'), 403);

        $data = $request->validate([
            'staff_id' => ['required', 'exists:staff_profiles,id'],
            'period' => ['required', 'string', 'max:20'],
            'basic' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'incentives' => ['nullable', 'numeric', 'min:0'],
            'advance' => ['nullable', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_status' => ['nullable', 'in:pending,paid'],
            'notes' => ['nullable', 'string'],
        ]);

        $staff = StaffProfile::findOrFail($data['staff_id']);

        $this->salaries->createOrUpdate($staff, $data);

        return redirect()->route('staff.salaries.index')->with('success', 'Salary processed.');
    }
}
