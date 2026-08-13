<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalaryRuleController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('salary.manage'), 403);

        $gym = current_gym();

        return view('staff.salary-rules', [
            'rules' => [
                'calendar_days' => (int) $gym->setting('salary_calendar_days', 30),
                'paid_leave_days' => (int) $gym->setting('salary_paid_leave_days', 2),
                'paid_half_days' => (int) $gym->setting('salary_paid_half_days', 4),
            ],
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('salary.manage'), 403);

        $data = $request->validate([
            'calendar_days' => ['required', 'in:28,30'],
            'paid_leave_days' => ['required', 'integer', 'min:0', 'max:31'],
            'paid_half_days' => ['required', 'integer', 'min:0', 'max:62'],
        ]);

        $gym = current_gym();

        $gym->setSetting('salary_calendar_days', (string) $data['calendar_days']);
        $gym->setSetting('salary_paid_leave_days', (string) $data['paid_leave_days']);
        $gym->setSetting('salary_paid_half_days', (string) $data['paid_half_days']);

        audit_log('salary.rules_updated', 'salaries', $gym->id, 'Salary rules updated');

        return back()->with('success', 'Salary rules saved.');
    }
}
