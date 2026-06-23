<?php

use App\Http\Controllers\PostcodeController;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Members as AdminMembers;
use App\Livewire\Admin\Packages as AdminPackages;
use App\Livewire\Admin\PendingMembers;
use App\Livewire\Admin\SmsCampaigns;
use App\Livewire\MemberDashboard;
use App\Livewire\RegisterMember;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/register-member', RegisterMember::class)->name('register.member');

Route::post('/postcode/lookup', [PostcodeController::class, 'lookup'])->name('postcode.lookup');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', MemberDashboard::class)->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/members/pending', PendingMembers::class)->name('members.pending');
    Route::get('/members', AdminMembers::class)->name('members');
    Route::get('/packages', AdminPackages::class)->name('packages');
    Route::get('/sms', SmsCampaigns::class)->name('sms');
});

require __DIR__.'/settings.php';
