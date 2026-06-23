<?php

use App\Console\Commands\SendRenewalReminders;
use App\Console\Commands\SyncPackagesToStripe;
use Illuminate\Support\Facades\Schedule;

Schedule::command(SendRenewalReminders::class)->daily();
Schedule::command(SyncPackagesToStripe::class)->daily();
