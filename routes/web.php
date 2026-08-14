<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Client\AppointmentController as ClientAppointmentController;
use App\Http\Controllers\Client\AttendanceController as ClientAttendanceController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DietController as ClientDietController;
use App\Http\Controllers\Client\InvoiceController as ClientInvoiceController;
use App\Http\Controllers\Client\MembershipController as ClientMembershipController;
use App\Http\Controllers\Client\PaymentController as ClientPaymentController;
use App\Http\Controllers\Client\ProfileController as ClientProfileController;
use App\Http\Controllers\Client\ProgressController as ClientProgressController;
use App\Http\Controllers\Client\SupportController as ClientSupportController;
use App\Http\Controllers\Client\WorkoutController as ClientWorkoutController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DietController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FollowupController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\PtSessionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffRoleController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Saas\DashboardController as SaasDashboardController;
use App\Http\Controllers\Saas\GymController as SaasGymController;
use App\Http\Controllers\Saas\PaymentController as SaasPaymentController;
use App\Http\Controllers\Saas\PlanController as SaasPlanController;
use App\Http\Controllers\Saas\SettingController as SaasSettingController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\WorkoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    if (is_saas()) {
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/register/verify', [AuthController::class, 'showVerifyEmail'])->name('register.verify');
        Route::post('/register/verify', [AuthController::class, 'verifyEmail']);
        Route::post('/register/verify/resend', [AuthController::class, 'resendVerification'])->name('register.verify.resend');
    }

    Route::get('/forgot-password', [PasswordController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/forgot-password/otp', [PasswordController::class, 'showOtp'])->name('password.otp');
    Route::post('/forgot-password/otp', [PasswordController::class, 'verifyOtp']);
    Route::post('/forgot-password/otp/resend', [PasswordController::class, 'resendOtp'])->name('password.otp.resend');
    Route::get('/reset-password', [PasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::middleware('client')->prefix('client')->name('client.')->group(function () {
        Route::get('/', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ClientProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [ClientProfileController::class, 'update'])->name('profile.update');
        Route::get('/membership', [ClientMembershipController::class, 'show'])->name('membership');
        Route::post('/membership/renew', [ClientMembershipController::class, 'renew'])->name('membership.renew');
        Route::get('/attendance', [ClientAttendanceController::class, 'index'])->name('attendance');
        Route::get('/workouts', [ClientWorkoutController::class, 'index'])->name('workouts');
        Route::get('/diets', [ClientDietController::class, 'index'])->name('diets');
        Route::get('/progress', [ClientProgressController::class, 'index'])->name('progress');
        Route::post('/progress', [ClientProgressController::class, 'store'])->name('progress.store');
        Route::delete('/progress/{progress}', [ClientProgressController::class, 'destroy'])->name('progress.destroy');
        Route::get('/appointments', [ClientAppointmentController::class, 'index'])->name('appointments');
        Route::post('/appointments', [ClientAppointmentController::class, 'store'])->name('appointments.store');
        Route::delete('/appointments/{appointment}', [ClientAppointmentController::class, 'destroy'])->name('appointments.destroy');
        Route::get('/payments', [ClientPaymentController::class, 'index'])->name('payments');
        Route::get('/payments/checkout', [ClientPaymentController::class, 'checkout'])->name('payments.checkout');
        Route::post('/payments', [ClientPaymentController::class, 'store'])->name('payments.store');
        Route::post('/payments/verify', [ClientPaymentController::class, 'verify'])->name('payments.verify');
        Route::get('/invoices', [ClientInvoiceController::class, 'index'])->name('invoices');
        Route::get('/invoices/{invoice}', [ClientInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/support', [ClientSupportController::class, 'index'])->name('support');
        Route::post('/support', [ClientSupportController::class, 'store'])->name('support.store');
        Route::get('/support/{ticket}', [ClientSupportController::class, 'show'])->name('support.show');
        Route::post('/support/{ticket}/reply', [ClientSupportController::class, 'reply'])->name('support.reply');
    });

    Route::middleware('permission:dashboard.view')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('clients')->name('clients.')->middleware('permission:clients.view')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::get('/export', [ClientController::class, 'export'])->name('export');
        Route::middleware('permission:clients.create')->group(function () {
            Route::get('/create', [ClientController::class, 'create'])->name('create');
            Route::post('/', [ClientController::class, 'store'])->name('store');
        });
        Route::middleware('permission:clients.edit')->group(function () {
            Route::get('/{client}/edit', [ClientController::class, 'edit'])->name('edit');
            Route::put('/{client}', [ClientController::class, 'update'])->name('update');
            Route::post('/{client}/toggle-status', [ClientController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{client}/health', [ClientController::class, 'updateHealth'])->name('health');
        });
        Route::middleware('permission:clients.delete')->delete('/{client}', [ClientController::class, 'destroy'])->name('destroy');
        Route::get('/{client}', [ClientController::class, 'show'])->name('show');
    });

    Route::prefix('memberships')->name('memberships.')->middleware('permission:memberships.view')->group(function () {
        Route::get('/', [MembershipController::class, 'index'])->name('index');
        Route::get('/expiring', [MembershipController::class, 'expiring'])->name('expiring');
        Route::get('/trials', [MembershipController::class, 'trials'])->name('trials');
        Route::middleware('permission:memberships.create')->group(function () {
            Route::get('/create', [MembershipController::class, 'create'])->name('create');
            Route::post('/', [MembershipController::class, 'store'])->name('store');
        });
        Route::middleware('permission:memberships.renew')->group(function () {
            Route::post('/{membership}/renew', [MembershipController::class, 'renew'])->name('renew');
            Route::post('/{membership}/cancel', [MembershipController::class, 'cancel'])->name('cancel');
        });
    });

    Route::prefix('membership-plans')->name('memberships.plans.')->middleware('permission:memberships.manage')->group(function () {
        Route::get('/', [MembershipPlanController::class, 'index'])->name('index');
        Route::get('/create', [MembershipPlanController::class, 'create'])->name('create');
        Route::post('/', [MembershipPlanController::class, 'store'])->name('store');
        Route::get('/{plan}/edit', [MembershipPlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [MembershipPlanController::class, 'update'])->name('update');
        Route::delete('/{plan}', [MembershipPlanController::class, 'destroy'])->name('destroy');
        Route::post('/{plan}/toggle', [MembershipPlanController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('attendance')->name('attendance.')->middleware('permission:attendance.view')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('check-in');
        Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('check-out');
        Route::post('/checkout-all', [AttendanceController::class, 'checkoutAll'])->name('checkout-all');
        Route::middleware('permission:attendance.staff')->group(function () {
            Route::get('/staff', [AttendanceController::class, 'staff'])->name('staff');
            Route::post('/staff/check-in', [AttendanceController::class, 'staffCheckIn'])->name('staff-check-in');
            Route::post('/staff/check-out', [AttendanceController::class, 'staffCheckOut'])->name('staff-check-out');
        });
    });

    Route::prefix('staff/leaves')->name('staff.leaves.')->middleware('permission:attendance.staff')->group(function () {
        Route::get('/', [LeaveController::class, 'index'])->name('index');
        Route::get('/create', [LeaveController::class, 'create'])->name('create');
        Route::post('/', [LeaveController::class, 'store'])->name('store');
        Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
    });

    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/my-payslips', [SalaryController::class, 'myPayslips'])->name('my-payslips');
        Route::get('/my-payslips/{salary}', [SalaryController::class, 'payslip'])->name('payslips.show');
    });

    Route::prefix('staff')->name('staff.')->middleware('permission:staff.view')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::middleware('permission:staff.create')->group(function () {
            Route::get('/create', [StaffController::class, 'create'])->name('create');
            Route::post('/', [StaffController::class, 'store'])->name('store');
        });
        Route::middleware('permission:staff.edit')->group(function () {
            Route::get('/{user}/edit', [StaffController::class, 'edit'])->name('edit');
            Route::put('/{user}', [StaffController::class, 'update'])->name('update');
            Route::post('/{user}/toggle-status', [StaffController::class, 'toggleStatus'])->name('toggle-status');
        });
        Route::middleware('permission:staff.delete')->delete('/{user}', [StaffController::class, 'destroy'])->name('destroy');

        Route::prefix('roles')->name('roles.')->middleware('permission:staff.roles')->group(function () {
            Route::get('/', [StaffRoleController::class, 'index'])->name('index');
            Route::post('/', [StaffRoleController::class, 'store'])->name('store');
            Route::get('/{role}/edit', [StaffRoleController::class, 'edit'])->name('edit');
            Route::put('/{role}', [StaffRoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [StaffRoleController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('salaries')->name('salaries.')->middleware('permission:salary.view')->group(function () {
            Route::get('/', [SalaryController::class, 'index'])->name('index');
            Route::middleware('permission:salary.manage')->group(function () {
                Route::get('/create', [SalaryController::class, 'create'])->name('create');
                Route::post('/pay', [SalaryController::class, 'pay'])->name('pay');
                Route::post('/deduction-preview', [SalaryController::class, 'deductionPreview'])->name('deduction-preview');
                Route::get('/bonus', [SalaryController::class, 'bonus'])->name('bonus');
                Route::post('/bonus', [SalaryController::class, 'applyBonus'])->name('bonus.apply');
                Route::post('/{salary}/status', [SalaryController::class, 'status'])->name('status');
            });
        });

        Route::get('/{user}', [StaffController::class, 'show'])->name('show');
    });

    Route::prefix('leads')->name('leads.')->middleware('permission:leads.view')->group(function () {
        Route::get('/', [LeadController::class, 'index'])->name('index');
        Route::get('/trials', [LeadController::class, 'trials'])->name('trials');
        Route::middleware('permission:leads.create')->group(function () {
            Route::get('/create', [LeadController::class, 'create'])->name('create');
            Route::post('/', [LeadController::class, 'store'])->name('store');
        });
        Route::middleware('permission:leads.edit')->group(function () {
            Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
            Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
            Route::post('/{lead}/status', [LeadController::class, 'updateStatus'])->name('status');
        });
        Route::middleware('permission:leads.manage')->group(function () {
            Route::post('/{lead}/assign', [LeadController::class, 'assign'])->name('assign');
            Route::post('/{lead}/convert', [LeadController::class, 'convert'])->name('convert');
        });
        Route::middleware('permission:leads.edit')->delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy');
        Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
    });

    Route::prefix('followups')->name('followups.')->middleware('permission:followups.view')->group(function () {
        Route::get('/', [FollowupController::class, 'index'])->name('index');
        Route::middleware('permission:followups.create')->group(function () {
            Route::get('/create', [FollowupController::class, 'create'])->name('create');
            Route::post('/', [FollowupController::class, 'store'])->name('store');
        });
        Route::middleware('permission:followups.manage')->group(function () {
            Route::post('/{followup}/complete', [FollowupController::class, 'complete'])->name('complete');
            Route::post('/{followup}/reschedule', [FollowupController::class, 'reschedule'])->name('reschedule');
            Route::post('/{followup}/cancel', [FollowupController::class, 'cancel'])->name('cancel');
            Route::delete('/{followup}', [FollowupController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('payments')->name('payments.')->middleware('permission:payments.view')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/export', [PaymentController::class, 'export'])->name('export');
        Route::middleware('permission:payments.create')->group(function () {
            Route::get('/create', [PaymentController::class, 'create'])->name('create');
            Route::post('/', [PaymentController::class, 'store'])->name('store');
        });
        Route::middleware('permission:payments.verify')->post('/verify', [PaymentController::class, 'verify'])->name('verify');
        Route::middleware('permission:payments.refund')->post('/{payment}/refund', [PaymentController::class, 'refund'])->name('refund');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
    });

    Route::prefix('invoices')->name('invoices.')->middleware('permission:invoices.view')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/print', [InvoiceController::class, 'print'])->name('print');
        Route::middleware('permission:invoices.manage')->group(function () {
            Route::post('/{invoice}/email', [InvoiceController::class, 'email'])->name('email');
            Route::post('/{invoice}/paid', [InvoiceController::class, 'markPaid'])->name('paid');
            Route::post('/{invoice}/void', [InvoiceController::class, 'void'])->name('void');
        });
    });

    Route::prefix('expenses')->name('expenses.')->middleware('permission:expenses.view')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/export', [ExpenseController::class, 'export'])->name('export');
        Route::middleware('permission:expenses.create')->group(function () {
            Route::get('/create', [ExpenseController::class, 'create'])->name('create');
            Route::post('/', [ExpenseController::class, 'store'])->name('store');
        });
        Route::middleware('permission:expenses.manage')->delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/clients', [ReportController::class, 'clients'])->name('clients');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('/finance', [ReportController::class, 'finance'])->name('finance');
        Route::get('/staff', [ReportController::class, 'staff'])->name('staff');
        Route::get('/leads', [ReportController::class, 'leads'])->name('leads');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });

    Route::prefix('workouts')->name('workouts.')->middleware('permission:workouts.manage')->group(function () {
        Route::get('/', [WorkoutController::class, 'index'])->name('index');
        Route::get('/create', [WorkoutController::class, 'create'])->name('create');
        Route::post('/', [WorkoutController::class, 'store'])->name('store');
        Route::get('/{workout}', [WorkoutController::class, 'show'])->name('show');
        Route::get('/{workout}/edit', [WorkoutController::class, 'edit'])->name('edit');
        Route::put('/{workout}', [WorkoutController::class, 'update'])->name('update');
        Route::delete('/{workout}', [WorkoutController::class, 'destroy'])->name('destroy');
        Route::post('/{workout}/toggle', [WorkoutController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('diets')->name('diets.')->middleware('permission:diets.manage')->group(function () {
        Route::get('/', [DietController::class, 'index'])->name('index');
        Route::get('/create', [DietController::class, 'create'])->name('create');
        Route::post('/', [DietController::class, 'store'])->name('store');
        Route::get('/{diet}', [DietController::class, 'show'])->name('show');
        Route::get('/{diet}/edit', [DietController::class, 'edit'])->name('edit');
        Route::put('/{diet}', [DietController::class, 'update'])->name('update');
        Route::delete('/{diet}', [DietController::class, 'destroy'])->name('destroy');
        Route::post('/{diet}/toggle', [DietController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('progress')->name('progress.')->middleware('permission:progress.view')->group(function () {
        Route::get('/', [ProgressController::class, 'index'])->name('index');
        Route::middleware('permission:progress.manage')->group(function () {
            Route::post('/weight', [ProgressController::class, 'logWeight'])->name('weight');
            Route::delete('/{progress}', [ProgressController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('pt-sessions')->name('pt.')->middleware('permission:pt.view')->group(function () {
        Route::get('/', [PtSessionController::class, 'index'])->name('index');
        Route::middleware('permission:pt.manage')->group(function () {
            Route::get('/create', [PtSessionController::class, 'create'])->name('create');
            Route::post('/', [PtSessionController::class, 'store'])->name('store');
            Route::post('/{ptSession}/complete', [PtSessionController::class, 'complete'])->name('complete');
            Route::post('/{ptSession}/cancel', [PtSessionController::class, 'cancel'])->name('cancel');
        });
    });

    Route::prefix('appointments')->name('appointments.')->middleware('permission:appointments.view')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::middleware('permission:appointments.manage')->group(function () {
            Route::get('/create', [AppointmentController::class, 'create'])->name('create');
            Route::post('/', [AppointmentController::class, 'store'])->name('store');
            Route::post('/{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete');
            Route::post('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
        });
    });

    Route::prefix('equipment')->name('equipment.')->middleware('permission:equipment.view')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::middleware('permission:equipment.manage')->group(function () {
            Route::post('/', [EquipmentController::class, 'store'])->name('store');
            Route::put('/{equipment}', [EquipmentController::class, 'update'])->name('update');
            Route::delete('/{equipment}', [EquipmentController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('inventory')->name('inventory.')->middleware('permission:inventory.view')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::middleware('permission:inventory.manage')->group(function () {
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::put('/{item}', [InventoryController::class, 'update'])->name('update');
            Route::post('/{item}/stock', [InventoryController::class, 'adjustStock'])->name('stock');
            Route::delete('/{item}', [InventoryController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('announcements')->name('announcements.')->middleware('permission:announcements.view')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::middleware('permission:announcements.manage')->group(function () {
            Route::post('/', [AnnouncementController::class, 'store'])->name('store');
            Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
            Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('support')->name('support.')->middleware('permission:support.view')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('/{ticket}', [SupportController::class, 'show'])->name('show');
        Route::middleware('permission:support.reply')->group(function () {
            Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('reply');
            Route::post('/{ticket}/status', [SupportController::class, 'updateStatus'])->name('status');
        });
    });

    Route::prefix('settings')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::middleware('permission:settings.manage')->group(function () {
            Route::put('/', [SettingController::class, 'update'])->name('update');
            Route::post('/payment-gateway', [SettingController::class, 'updatePaymentGateway'])->name('payment-gateway');
            Route::post('/salary-rules', [SettingController::class, 'updateSalaryRules'])->name('salary-rules');
        });
    });

    Route::prefix('audit')->name('audit.')->middleware('permission:audit.view')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
    });
});

if (is_saas()) {
    Route::middleware('auth')->prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::post('/order', [SubscriptionController::class, 'createOrder'])->name('order');
        Route::post('/verify', [SubscriptionController::class, 'verify'])->name('verify');
    });

    Route::middleware(['auth', 'saas.owner'])->prefix('saas')->name('saas.')->group(function () {
        Route::get('/', [SaasDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('gyms')->name('gyms.')->group(function () {
            Route::get('/', [SaasGymController::class, 'index'])->name('index');
            Route::get('/{gym}', [SaasGymController::class, 'show'])->name('show');
            Route::post('/{gym}/status', [SaasGymController::class, 'toggleStatus'])->name('status');
            Route::post('/{gym}/owner-password', [SaasGymController::class, 'resetOwnerPassword'])->name('owner-password');
        });

        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/', [SaasPlanController::class, 'index'])->name('index');
            Route::get('/create', [SaasPlanController::class, 'create'])->name('create');
            Route::post('/', [SaasPlanController::class, 'store'])->name('store');
            Route::get('/{plan}/edit', [SaasPlanController::class, 'edit'])->name('edit');
            Route::put('/{plan}', [SaasPlanController::class, 'update'])->name('update');
            Route::post('/{plan}/toggle', [SaasPlanController::class, 'toggle'])->name('toggle');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [SaasPaymentController::class, 'index'])->name('index');
            Route::get('/create', [SaasPaymentController::class, 'create'])->name('create');
            Route::post('/', [SaasPaymentController::class, 'store'])->name('store');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SaasSettingController::class, 'index'])->name('index');
            Route::put('/', [SaasSettingController::class, 'update'])->name('update');
        });
    });
}

Route::prefix('api')->group(function () {
    Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

    if (is_saas()) {
        Route::post('/payments/saas-webhook', [SubscriptionController::class, 'webhook'])->name('payments.saas-webhook');
    }
});
