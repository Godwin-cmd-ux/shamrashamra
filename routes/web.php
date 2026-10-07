<?php

use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\ContributionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventMemberController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PledgeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicInvitationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VendorController;
use App\Models\EventCategory;
use App\Models\InvitationTemplate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome', [
    'categories' => EventCategory::where('is_active', true)->orderBy('sort_order')->get(),
    'templates' => InvitationTemplate::platform()->get(),
]))->name('home');

Route::post('/locale/{locale}', [LocaleController::class, 'switch'])
    ->name('locale.switch');

// Guest-facing invitation pages (opaque token, no account required).
Route::get('/i/{token}', [PublicInvitationController::class, 'show'])
    ->name('public.invitation.show');

Route::post('/i/{token}/rsvp', [PublicInvitationController::class, 'rsvp'])
    ->middleware('throttle:30,1')
    ->name('public.invitation.rsvp');

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Events ------------------------------------------------------------
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::patch('/events/{event}/status', [EventController::class, 'updateStatus'])->name('events.status');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    // Members -----------------------------------------------------------
    Route::get('/events/{event}/members', [EventMemberController::class, 'index'])->name('events.members.index');
    Route::post('/events/{event}/members', [EventMemberController::class, 'store'])->name('events.members.store');
    Route::patch('/events/{event}/members/{member}', [EventMemberController::class, 'update'])->name('events.members.update');
    Route::delete('/events/{event}/members/{member}', [EventMemberController::class, 'destroy'])->name('events.members.destroy');

    // Guests ------------------------------------------------------------
    Route::get('/events/{event}/guests', [GuestController::class, 'index'])->name('events.guests.index');
    Route::get('/events/{event}/guests/create', [GuestController::class, 'create'])->name('events.guests.create');
    Route::post('/events/{event}/guests', [GuestController::class, 'store'])->name('events.guests.store');
    Route::get('/events/{event}/guests/import', [GuestController::class, 'importForm'])->name('events.guests.import');
    Route::post('/events/{event}/guests/import', [GuestController::class, 'import'])->name('events.guests.import.store');
    Route::get('/events/{event}/guests/export', [GuestController::class, 'export'])->name('events.guests.export');
    Route::get('/events/{event}/guests/{guest}/edit', [GuestController::class, 'edit'])->name('events.guests.edit');
    Route::put('/events/{event}/guests/{guest}', [GuestController::class, 'update'])->name('events.guests.update');
    Route::delete('/events/{event}/guests/{guest}', [GuestController::class, 'destroy'])->name('events.guests.destroy');

    // Invitations -------------------------------------------------------
    Route::get('/events/{event}/invitations', [InvitationController::class, 'index'])->name('events.invitations.index');
    Route::get('/events/{event}/invitations/create', [InvitationController::class, 'create'])->name('events.invitations.create');
    Route::post('/events/{event}/invitations', [InvitationController::class, 'store'])->name('events.invitations.store');
    Route::get('/events/{event}/invitations/{invitation}', [InvitationController::class, 'show'])->name('events.invitations.show');
    Route::get('/events/{event}/invitations/{invitation}/qr', [InvitationController::class, 'qr'])->name('events.invitations.qr');
    Route::post('/events/{event}/invitations/{invitation}/revoke', [InvitationController::class, 'revoke'])->name('events.invitations.revoke');
    Route::post('/events/{event}/invitations/{invitation}/replace', [InvitationController::class, 'replace'])->name('events.invitations.replace');
    Route::post('/events/{event}/invitations/{invitation}/share', [InvitationController::class, 'share'])->name('events.invitations.share');

    // Check-in ----------------------------------------------------------
    Route::get('/events/{event}/checkin', [CheckInController::class, 'index'])->name('events.checkin.index');
    Route::post('/events/{event}/checkin/verify', [CheckInController::class, 'verify'])
        ->middleware('throttle:60,1')
        ->name('events.checkin.verify');

    // Finance -----------------------------------------------------------
    Route::get('/events/{event}/finance/pledges', [PledgeController::class, 'index'])->name('events.finance.pledges');
    Route::post('/events/{event}/finance/pledges', [PledgeController::class, 'store'])->name('events.finance.pledges.store');
    Route::post('/events/{event}/finance/pledges/{pledge}/cancel', [PledgeController::class, 'cancel'])->name('events.finance.pledges.cancel');

    Route::get('/events/{event}/finance/contributions', [ContributionController::class, 'index'])->name('events.finance.contributions');
    Route::post('/events/{event}/finance/contributions', [ContributionController::class, 'store'])->name('events.finance.contributions.store');
    Route::post('/events/{event}/finance/contributions/{contribution}/reverse', [ContributionController::class, 'reverse'])->name('events.finance.contributions.reverse');

    Route::get('/events/{event}/finance/expenses', [ExpenseController::class, 'index'])->name('events.finance.expenses');
    Route::post('/events/{event}/finance/expenses', [ExpenseController::class, 'store'])->name('events.finance.expenses.store');
    Route::post('/events/{event}/finance/expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('events.finance.expenses.approve');
    Route::post('/events/{event}/finance/expenses/{expense}/reverse', [ExpenseController::class, 'reverse'])->name('events.finance.expenses.reverse');

    Route::get('/events/{event}/finance/budget', [BudgetController::class, 'index'])->name('events.finance.budget');
    Route::post('/events/{event}/finance/budget', [BudgetController::class, 'store'])->name('events.finance.budget.store');
    Route::put('/events/{event}/finance/budget/{category}', [BudgetController::class, 'update'])->name('events.finance.budget.update');
    Route::delete('/events/{event}/finance/budget/{category}', [BudgetController::class, 'destroy'])->name('events.finance.budget.destroy');

    // Vendors, reports, audit -------------------------------------------
    Route::get('/events/{event}/vendors', [VendorController::class, 'index'])->name('events.vendors.index');
    Route::post('/events/{event}/vendors', [VendorController::class, 'store'])->name('events.vendors.store');
    Route::put('/events/{event}/vendors/{vendor}', [VendorController::class, 'update'])->name('events.vendors.update');
    Route::delete('/events/{event}/vendors/{vendor}', [VendorController::class, 'destroy'])->name('events.vendors.destroy');

    Route::get('/events/{event}/reports', [ReportController::class, 'index'])->name('events.reports.index');
    Route::get('/events/{event}/reports/{type}.csv', [ReportController::class, 'export'])->name('events.reports.export');

    Route::get('/events/{event}/audit', [AuditController::class, 'index'])->name('events.audit');

    // Platform administration -------------------------------------------
    Route::get('/admin', [PlatformController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/categories', [PlatformController::class, 'storeCategory'])->name('admin.categories.store');
    Route::post('/admin/categories/{category}/toggle', [PlatformController::class, 'toggleCategory'])->name('admin.categories.toggle');
});
