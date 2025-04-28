<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->string('phone_number')->nullable()->unique();
            $table->enum('payment_status', ['paid', 'unpaid', 'pending'])->default('unpaid');
            $table->enum('active_status', ['active', 'inactive'])->default('active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['balance', 'phone_number', 'payment_status', 'active_status']);
        });
    }
};
