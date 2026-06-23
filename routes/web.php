<?php

use App\Livewire\Admin\Campaigns as AdminCampaigns;
use App\Livewire\Admin\CreateMember;
use App\Livewire\Admin\Expenses as AdminExpenses;
use App\Livewire\Admin\ManageCampaign;
use App\Livewire\Admin\Members as AdminMembers;
use App\Livewire\Admin\Packages as AdminPackages;
use App\Livewire\Admin\Payments as AdminPayments;
use App\Livewire\Admin\PendingMembers;
use App\Livewire\Admin\SmsCampaigns;
use App\Livewire\Admin\UserProfile;
use App\Livewire\Campaigns\Index as CampaignsIndex;
use App\Livewire\Campaigns\Show as CampaignShow;
use App\Livewire\Dashboard;
use App\Livewire\RegisterMember;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/register-member', RegisterMember::class)->name('register.member');

Route::get('/campaigns', CampaignsIndex::class)->name('campaigns.index');
Route::get('/c/{campaign:slug}', CampaignShow::class)->name('campaigns.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/dashboard', '/dashboard');
        Route::get('/members/pending', PendingMembers::class)->name('members.pending');
        Route::get('/members', AdminMembers::class)->name('members');
        Route::get('/members/create', CreateMember::class)->name('members.create');
        Route::get('/packages', AdminPackages::class)->name('packages');
        Route::get('/sms', SmsCampaigns::class)->name('sms');
        Route::get('/payments', AdminPayments::class)->name('payments');
        Route::get('/expenses', AdminExpenses::class)->name('expenses');
        Route::get('/campaigns', AdminCampaigns::class)->name('campaigns');
        Route::get('/campaigns/{campaign}', ManageCampaign::class)->name('campaigns.manage');
        Route::get('/members/{user}', UserProfile::class)->name('members.profile');
    });
});

require __DIR__.'/settings.php';
