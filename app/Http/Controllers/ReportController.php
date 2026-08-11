<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function clients(Request $request)
    {
        $report = $this->reports->clientReport($request->input('from'), $request->input('to'));

        return view('reports.clients', ['report' => $report]);
    }

    public function attendance(Request $request)
    {
        $report = $this->reports->attendanceReport($request->input('from'), $request->input('to'));

        return view('reports.attendance', ['report' => $report]);
    }

    public function finance(Request $request)
    {
        $revenue = $this->reports->revenueReport($request->input('from'), $request->input('to'));
        $expenses = $this->reports->expenseReport($request->input('from'), $request->input('to'));

        $net = (float) $revenue['total_revenue'] - (float) $expenses['total_expenses'];

        return view('reports.finance', compact('revenue', 'expenses', 'net'));
    }

    public function staff(Request $request)
    {
        $report = $this->reports->staffAttendanceReport($request->input('from'), $request->input('to'));

        return view('reports.staff', ['report' => $report]);
    }

    public function leads(Request $request)
    {
        $report = $this->reports->leadReport($request->input('from'), $request->input('to'));

        return view('reports.leads', ['report' => $report]);
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'clients');
        $from = $request->input('from');
        $to = $request->input('to');

        $filename = $type . '-report-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($type, $from, $to) {
            $handle = fopen('php://output', 'w');

            if ($type === 'finance') {
                $revenue = $this->reports->revenueReport($from, $to);
                $expenses = $this->reports->expenseReport($from, $to);

                fputcsv($handle, ['Period Revenue']);
                fputcsv($handle, ['Payment No', 'Client', 'Plan', 'Method', 'Amount', 'Date']);
                foreach ($revenue['payments'] as $p) {
                    fputcsv($handle, [$p->payment_no, $p->client?->display_name, $p->plan?->name, $p->payment_method, $p->final_amount, $p->payment_date]);
                }

                fputcsv($handle, []);
                fputcsv($handle, ['Period Expenses']);
                fputcsv($handle, ['Date', 'Category', 'Description', 'Amount']);
                foreach ($expenses['expenses'] as $e) {
                    fputcsv($handle, [$e->expense_date, $e->category?->name, $e->description, $e->amount]);
                }
            } else {
                $data = match ($type) {
                    'attendance' => $this->reports->attendanceReport($from, $to),
                    'leads' => $this->reports->leadReport($from, $to),
                    default => $this->reports->clientReport($from, $to),
                };

                if ($type === 'attendance') {
                    fputcsv($handle, ['Date', 'Member ID', 'Client', 'Check In', 'Check Out', 'Duration (min)']);
                    foreach ($data['records'] as $r) {
                        fputcsv($handle, [$r->attendance_date, $r->client?->member_id, $r->client?->display_name, $r->check_in, $r->check_out, $r->duration_minutes]);
                    }
                } elseif ($type === 'leads') {
                    fputcsv($handle, ['Name', 'Phone', 'Source', 'Status', 'Assigned To', 'Created']);
                    foreach ($data['leads'] as $l) {
                        fputcsv($handle, [$l->name, $l->phone, $l->source, $l->status, $l->assignedTo?->name, $l->created_at]);
                    }
                } else {
                    fputcsv($handle, ['Member ID', 'Name', 'Email', 'Phone', 'Joined', 'Status']);
                    foreach ($data['recent_clients'] as $c) {
                        fputcsv($handle, [$c->member_id, $c->user?->name, $c->user?->email, $c->phone, $c->joining_date, $c->status]);
                    }
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
