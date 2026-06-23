<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete()->after('phone');
            $table->string('status')->default('pending')->after('package_id');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
            $table->timestamp('deactivated_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('deactivated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'phone', 'package_id', 'status', 'approved_at',
                'approved_by', 'deactivated_at', 'rejection_reason',
            ]);
        });
    }
};
