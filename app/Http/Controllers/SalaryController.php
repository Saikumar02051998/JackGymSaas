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

        $defaultPeriod = now()->format('Y-m');

        $paymentDay = (int) (current_gym()?->setting('salary_payment_day', 1) ?? 1);
        $defaultPaymentDate = now()->day > $paymentDay
            ? now()->addMonth()->startOfMonth()->addDays($paymentDay - 1)
            : now()->startOfMonth()->addDays($paymentDay - 1);

        $deductions = $staff->mapWithKeys(fn ($s) => [
            $s->id => $this->salaries->leaveDeduction($s, $defaultPeriod, (float) ($s->basic_salary ?? 0) + (float) ($s->allowances ?? 0)),
        ]);

        return view('staff.salary-create', compact('staff', 'defaultPeriod', 'defaultPaymentDate', 'deductions'));
    }

    public function deductionPreview(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('salary.manage'), 403);

        $data = $request->validate([
            'staff_id' => ['required', 'exists:staff_profiles,id'],
            'period' => ['required', 'string', 'max:20'],
            'basic' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
        ]);

        $staff = StaffProfile::findOrFail($data['staff_id']);

        abort_if($staff->gym_id !== current_gym()?->id, 403);

        $gross = (float) ($data['basic'] ?? $staff->basic_salary ?? 0) + (float) ($data['allowances'] ?? $staff->allowances ?? 0);

        return response()->json($this->salaries->leaveDeduction($staff, $data['period'], $gross));
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

    public function bonus()
    {
        abort_unless(auth()->user()->hasPermission('salary.manage'), 403);

        $staff = StaffProfile::with('user')->where('gym_id', current_gym()?->id)
            ->where('status', 'active')
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->orderBy('id')
            ->get();

        return view('staff.salary-bonus', compact('staff'));
    }

    public function applyBonus(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('salary.manage'), 403);

        $data = $request->validate([
            'scope' => ['required', 'in:all,selected'],
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['integer', 'exists:staff_profiles,id'],
            'period' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $query = StaffProfile::where('gym_id', current_gym()?->id)
            ->where('status', 'active')
            ->whereHas('user', fn ($q) => $q->where('status', 'active'));

        if ($data['scope'] === 'selected') {
            $query->whereIn('id', $data['staff_ids'] ?? []);
        }

        $selected = $query->pluck('id');

        if ($selected->isEmpty()) {
            return back()->withErrors(['staff' => 'No staff members match the selected scope.']);
        }

        $count = 0;
        foreach ($selected as $staffId) {
            $staff = StaffProfile::find($staffId);
            $existing = Salary::where('staff_id', $staffId)->where('period', $data['period'])->first();

            $base = $existing
                ? [
                    'basic' => $existing->basic,
                    'allowances' => $existing->allowances,
                    'commission' => $existing->commission,
                    'deductions' => $existing->deductions,
                    'incentives' => $existing->incentives,
                    'advance' => $existing->advance,
                ]
                : [
                    'basic' => $staff->basic_salary,
                    'allowances' => $staff->allowances,
                    'commission' => $staff->commission_rate,
                    'deductions' => 0,
                    'incentives' => 0,
                    'advance' => 0,
                ];

            $this->salaries->createOrUpdate($staff, array_merge($base, [
                'period' => $data['period'],
                'bonus' => $data['amount'],
                'payment_status' => $existing?->payment_status ?? 'pending',
                'notes' => $data['notes'] ?? ($existing?->notes ?? null),
            ]), false);
            $count++;
        }

        audit_log('salary.bonus', 'salaries', null, "Bonus of {$data['amount']} applied to {$count} staff for {$data['period']}");

        return back()->with('success', "Bonus applied to {$count} staff member(s) for {$data['period']}.");
    }
}
