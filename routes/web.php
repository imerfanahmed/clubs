<?php

use App\Http\Controllers\PostcodeController;
use App\Livewire\Admin\Expenses as AdminExpenses;
use App\Livewire\Admin\Members as AdminMembers;
use App\Livewire\Admin\Packages as AdminPackages;
use App\Livewire\Admin\Payments as AdminPayments;
use App\Livewire\Admin\PendingMembers;
use App\Livewire\Admin\SmsCampaigns;
use App\Livewire\Dashboard;
use App\Livewire\RegisterMember;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/register-member', RegisterMember::class)->name('register.member');

Route::post('/postcode/lookup', [PostcodeController::class, 'lookup'])->name('postcode.lookup');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/dashboard', '/dashboard');
        Route::get('/members/pending', PendingMembers::class)->name('members.pending');
        Route::get('/members', AdminMembers::class)->name('members');
        Route::get('/packages', AdminPackages::class)->name('packages');
        Route::get('/sms', SmsCampaigns::class)->name('sms');
        Route::get('/payments', AdminPayments::class)->name('payments');
        Route::get('/expenses', AdminExpenses::class)->name('expenses');
    });
});

require __DIR__.'/settings.php';
